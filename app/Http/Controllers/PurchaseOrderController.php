<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Party;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Stock;
use App\Models\ChartOfAccount;
use App\Models\LedgerEntry;
use App\Models\Setting;
use App\Traits\TracksActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PurchaseOrderController extends Controller implements HasMiddleware
{
    use TracksActivity;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view purchases', only: ['index', 'show']),
            new Middleware('permission:create purchases', only: ['create', 'store', 'receiveGoods']),
            new Middleware('permission:edit purchases', only: ['edit', 'update']),
            new Middleware('permission:delete purchases', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['party', 'warehouse', 'items.product']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('party_id')) {
            $query->where('party_id', $request->party_id);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhereHas('party', function ($qp) use ($search) {
                      $qp->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }

        $purchaseOrders = $query->latest('order_date')->paginate(15)->withQueryString();

        // Metrics
        $totalOrdersCount = PurchaseOrder::count();
        $pendingCount = PurchaseOrder::where('status', 'pending')->count();
        $partialCount = PurchaseOrder::where('status', 'partial')->count();
        $completedCount = PurchaseOrder::where('status', 'completed')->count();
        $pendingValue = PurchaseOrder::whereIn('status', ['pending', 'partial'])->sum('total_amount');

        $vendors = Party::where('type', 'vendor')->where('status', true)->get();
        $warehouses = Warehouse::where('status', true)->get();

        return view('purchase_orders.index', compact(
            'purchaseOrders',
            'totalOrdersCount',
            'pendingCount',
            'partialCount',
            'completedCount',
            'pendingValue',
            'vendors',
            'warehouses'
        ));
    }

    public function create()
    {
        $vendors = Party::where('type', 'vendor')->where('status', true)->get()->map(function ($v) {
            $v->current_balance = (float)$v->getCurrentBalance();
            return $v;
        });
        $warehouses = Warehouse::where('status', true)->get();
        $products = Product::where('status', true)->get()->map(function ($p) {
            $p->current_stock = (float)$p->getCurrentStock();
            $p->warehouse_stocks = $p->stocks->pluck('qty', 'warehouse_id');
            return $p;
        });
        $settings = Setting::first();

        // Auto-generate next PO number
        $nextId = (PurchaseOrder::max('id') ?? 0) + 1;
        $suggestedPoNumber = 'PO-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        return view('purchase_orders.create', compact('vendors', 'warehouses', 'products', 'settings', 'suggestedPoNumber'));
    }

    public function store(Request $request)
    {
        // Normalize price field if price_pkr is passed
        if ($request->has('items') && is_array($request->items)) {
            $items = $request->items;
            foreach ($items as $k => $item) {
                if (!isset($item['price']) && isset($item['price_pkr'])) {
                    $items[$k]['price'] = $item['price_pkr'];
                }
            }
            $request->merge(['items' => $items]);
        }

        $validated = $request->validate([
            'po_number' => 'required|string|max:50|unique:purchase_orders,po_number',
            'party_id' => 'required|exists:parties,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'currency' => 'required|in:PKR,RMB',
            'exchange_rate' => 'required|numeric|min:0.01',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $po = DB::transaction(function () use ($validated, $request) {
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $unitPrice = (float)($item['price'] ?? $item['price_pkr'] ?? 0);
                $totalAmount += round($item['qty'] * $unitPrice, 2);
            }

            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $validated['po_number'],
                'party_id' => $validated['party_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'status' => 'pending',
                'currency' => $validated['currency'],
                'exchange_rate' => $validated['exchange_rate'],
                'total_amount' => $totalAmount,
                'note' => $validated['note'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $unitPrice = (float)($item['price'] ?? $item['price_pkr'] ?? 0);
                $lineTotal = round($item['qty'] * $unitPrice, 2);
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'ordered_qty' => $item['qty'],
                    'received_qty' => 0,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                ]);
            }

            $this->logActivity('Created Purchase Order', 'Purchase Orders', $purchaseOrder->id, null, $purchaseOrder->toArray());

            return $purchaseOrder;
        });

        return redirect()->route('purchase-orders.show', $po)->with('success', 'Purchase Order ' . $po->po_number . ' created successfully!');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['party', 'warehouse', 'items.product', 'purchases.items.product', 'creator']);
        $warehouses = Warehouse::where('status', true)->get();

        return view('purchase_orders.show', compact('purchaseOrder', 'warehouses'));
    }

    public function receiveGoods(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === 'completed') {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('warning', 'This Purchase Order is already fully received (Completed).');
        }

        if ($purchaseOrder->status === 'cancelled') {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Cannot receive goods for a cancelled purchase order.');
        }

        $request->validate([
            'receiving_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.po_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.receive_qty' => 'required|numeric|min:0',
        ]);

        // Filter items that have receive_qty > 0
        $receivingItems = array_filter($request->items, fn ($i) => (float)($i['receive_qty'] ?? 0) > 0);

        if (empty($receivingItems)) {
            return redirect()->back()->with('error', 'Please enter receiving quantity for at least one item.');
        }

        try {
            DB::transaction(function () use ($purchaseOrder, $receivingItems, $request) {
                $totalReceivedAmount = 0;
                $purchaseItemsData = [];

                foreach ($receivingItems as $itemInput) {
                    $poItem = PurchaseOrderItem::where('id', $itemInput['po_item_id'])
                        ->where('purchase_order_id', $purchaseOrder->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $receiveQty = (float) $itemInput['receive_qty'];
                    $remainingQty = (float) $poItem->getRemainingQty();

                    if ($remainingQty <= 0) {
                        continue;
                    }

                    if ($receiveQty > $remainingQty + 0.001) {
                        throw new \Exception("Receiving qty ({$receiveQty}) cannot exceed remaining ordered qty ({$remainingQty}) for " . ($poItem->product->name ?? 'item') . ".");
                    }

                    $lineTotal = round($receiveQty * $poItem->unit_price, 2);
                    $totalReceivedAmount += $lineTotal;

                    $purchaseItemsData[] = [
                        'po_item' => $poItem,
                        'product_id' => $poItem->product_id,
                        'qty' => $receiveQty,
                        'unit_price' => $poItem->unit_price,
                        'line_total' => $lineTotal,
                    ];
                }

                if (empty($purchaseItemsData)) {
                    $purchaseOrder->updateReceivingStatus();
                    return;
                }

                // 1. Create real Purchase (GRN)
                $purchase = Purchase::create([
                    'party_id' => $purchaseOrder->party_id,
                    'warehouse_id' => $request->warehouse_id,
                    'purchase_order_id' => $purchaseOrder->id,
                    'date' => $request->receiving_date,
                    'currency' => $purchaseOrder->currency,
                    'exchange_rate' => $purchaseOrder->exchange_rate,
                    'total_amount' => $totalReceivedAmount,
                    'discount_amount' => 0,
                    'paid_amount' => 0,
                    'note' => 'Received against ' . $purchaseOrder->po_number . ($request->note ? ' — ' . $request->note : ''),
                ]);

                // 2. Insert PurchaseItems, Update Stock and Weighted Average Cost
                foreach ($purchaseItemsData as $itemData) {
                    $pricePkr = ($purchaseOrder->currency === 'RMB')
                        ? round($itemData['unit_price'] * $purchaseOrder->exchange_rate, 2)
                        : $itemData['unit_price'];

                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $itemData['product_id'],
                        'qty' => $itemData['qty'],
                        'price_rmb' => ($purchaseOrder->currency === 'RMB') ? $itemData['unit_price'] : 0,
                        'price_pkr' => $pricePkr,
                        'total' => $itemData['line_total'],
                        'cargo_amount' => 0,
                    ]);

                    // Stock & Avg Cost calculation
                    $stock = Stock::where('product_id', $itemData['product_id'])
                        ->where('warehouse_id', $request->warehouse_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$stock) {
                        $stock = new Stock([
                            'product_id' => $itemData['product_id'],
                            'warehouse_id' => $request->warehouse_id,
                            'qty' => 0,
                            'avg_cost' => 0,
                        ]);
                    }

                    $currentQty = (float) ($stock->qty ?? 0);
                    $currentAvgCost = (float) ($stock->avg_cost ?? 0);
                    $addedQty = $itemData['qty'];
                    $addedRate = $itemData['unit_price'];

                    $newQty = $currentQty + $addedQty;
                    if ($newQty > 0) {
                        $newAvgCost = (($currentQty * $currentAvgCost) + ($addedQty * $addedRate)) / $newQty;
                    } else {
                        $newAvgCost = $addedRate;
                    }

                    $stock->qty = $newQty;
                    $stock->avg_cost = round($newAvgCost, 2);
                    $stock->save();

                    // Increment received_qty on PO Item
                    $itemData['po_item']->increment('received_qty', $addedQty);
                }

                // 3. Post Double-Entry Ledger Entries
                $inventoryAcc = ChartOfAccount::where('name', 'Inventory')->first();
                $payableAcc   = ChartOfAccount::where('name', 'Accounts Payable')->first();

                if (!$inventoryAcc) {
                    $inventoryAcc = ChartOfAccount::create([
                        'name' => 'Inventory',
                        'type' => 'Asset',
                        'opening_balance' => 0,
                        'balance_type' => 'debit',
                        'status' => true,
                    ]);
                }

                if (!$payableAcc) {
                    $payableAcc = ChartOfAccount::create([
                        'name' => 'Accounts Payable',
                        'type' => 'Liability',
                        'opening_balance' => 0,
                        'balance_type' => 'credit',
                        'status' => true,
                    ]);
                }

                // Debit Inventory
                LedgerEntry::create([
                    'account_id' => $inventoryAcc->id,
                    'party_id' => null,
                    'warehouse_id' => $request->warehouse_id,
                    'debit' => $totalReceivedAmount,
                    'credit' => 0,
                    'date' => $request->receiving_date,
                    'reference_type' => 'purchase',
                    'reference_id' => $purchase->id,
                    'note' => 'Goods Receipt from PO ' . $purchaseOrder->po_number,
                ]);

                // Credit Accounts Payable (Vendor)
                LedgerEntry::create([
                    'account_id' => $payableAcc->id,
                    'party_id' => $purchaseOrder->party_id,
                    'warehouse_id' => $request->warehouse_id,
                    'debit' => 0,
                    'credit' => $totalReceivedAmount,
                    'date' => $request->receiving_date,
                    'reference_type' => 'purchase',
                    'reference_id' => $purchase->id,
                    'note' => 'Goods Received against PO ' . $purchaseOrder->po_number,
                ]);

                // 4. Update PO overall status
                $purchaseOrder->refresh();
                $purchaseOrder->updateReceivingStatus();

                $this->logActivity('Received Goods (GRN)', 'Purchase Orders', $purchaseOrder->id, null, [
                    'purchase_id' => $purchase->id,
                    'received_amount' => $totalReceivedAmount,
                    'new_status' => $purchaseOrder->status,
                ]);
            });

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'Goods received successfully! Stock and accounting ledger have been updated.');
        } catch (\Exception $e) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->getTotalReceivedQty() > 0) {
            return redirect()->back()->with('error', 'Cannot delete Purchase Order because goods have already been received against it.');
        }

        $oldData = $purchaseOrder->toArray();
        $purchaseOrder->delete();

        $this->logActivity('Deleted Purchase Order', 'Purchase Orders', $purchaseOrder->id, $oldData, null);

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase Order deleted successfully.');
    }
}
