<?php

namespace App\Http\Controllers;

use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Purchase;
use App\Models\Party;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Stock;
use App\Models\ChartOfAccount;
use App\Models\LedgerEntry;
use App\Traits\TracksActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PurchaseReturnController extends Controller implements HasMiddleware
{
    use TracksActivity;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view purchases', only: ['index', 'show']),
            new Middleware('permission:return purchases', only: ['create', 'store', 'createFromPurchase']),
            new Middleware('permission:delete purchases', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = PurchaseReturn::with(['party', 'warehouse', 'purchase']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('party', function($qp) use ($search) {
                      $qp->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        $purchaseReturns = $query->latest()->paginate(10)->withQueryString();
        $warehouses = Warehouse::all();
        
        return view('purchase_returns.index', compact('purchaseReturns', 'warehouses'));
    }

    public function create()
    {
        $purchases = Purchase::with('party')->latest()->get();
        $warehouses = Warehouse::where('status', true)->get();
        
        return view('purchase_returns.create', compact('purchases', 'warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.cost' => 'required|numeric|min:0',
        ]);

        $purchaseReturn = DB::transaction(function () use ($request) {
            $purchase = Purchase::findOrFail($request->purchase_id);
            
            // Calculate total
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += round($item['qty'] * $item['cost'], 2);
            }

            // Create Purchase Return
            $purchaseReturn = PurchaseReturn::create([
                'purchase_id' => $request->purchase_id,
                'party_id' => $purchase->party_id,
                'warehouse_id' => $request->warehouse_id,
                'date' => $request->date,
                'total_amount' => $totalAmount,
                'note' => $request->note,
            ]);

            // Reduce stock and Update WAC
            foreach ($request->items as $item) {
                PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'cost' => $item['cost'],
                    'total' => round($item['qty'] * $item['cost'], 2),
                ]);

                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $request->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if ($stock) {
                    $currentQty = $stock->qty;
                    $currentAvgCost = $stock->avg_cost;
                    $returnQty = $item['qty'];
                    $returnCost = $item['cost'];

                    $newQty = $currentQty - $returnQty;
                    
                    if ($newQty > 0) {
                        // Recalculate WAC: (Total Value - Returned Value) / Remaining Qty
                        $newAvgCost = (($currentQty * $currentAvgCost) - ($returnQty * $returnCost)) / $newQty;
                        $stock->qty = $newQty;
                        $stock->avg_cost = max(0, round($newAvgCost, 2));
                    } else {
                        $stock->qty = 0;
                        $stock->avg_cost = 0;
                    }
                    $stock->save();
                }
            }

            // Accounting Entries
            $purchaseReturnAccount = ChartOfAccount::where('name', 'Purchase Return')->first();
            if (!$purchaseReturnAccount) {
                $purchaseReturnAccount = ChartOfAccount::create([
                    'name' => 'Purchase Return',
                    'type' => 'Income',
                    'status' => true,
                ]);
            }

            $accountsPayable = ChartOfAccount::where('name', 'Accounts Payable')->first();

            // Credit Purchase Return (Income)
            LedgerEntry::create([
                'account_id' => $purchaseReturnAccount->id,
                'party_id' => null,
                'warehouse_id' => $request->warehouse_id,
                'debit' => 0,
                'credit' => $totalAmount,
                'date' => $request->date,
                'reference_type' => 'purchase_return',
                'reference_id' => $purchaseReturn->id,
                'note' => 'Purchase Return',
            ]);

            // Debit Accounts Payable (reduce vendor debt)
            LedgerEntry::create([
                'account_id' => $accountsPayable->id,
                'party_id' => $purchase->party_id,
                'warehouse_id' => $request->warehouse_id,
                'debit' => $totalAmount,
                'credit' => 0,
                'date' => $request->date,
                'reference_type' => 'purchase_return',
                'reference_id' => $purchaseReturn->id,
                'note' => 'Purchase Return to Vendor',
            ]);
            return $purchaseReturn;
        });

        $this->logActivity('Created Purchase Return', 'Purchase Returns', $purchaseReturn->id, null, $purchaseReturn->load('items')->toArray());

        return redirect()->route('purchase-returns.index')->with('success', 'Purchase Return created successfully.');
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load(['party', 'warehouse', 'purchase', 'items.product.unit']);
        return view('purchase_returns.show', compact('purchaseReturn'));
    }

    public function destroy(PurchaseReturn $purchaseReturn)
    {
        DB::transaction(function () use ($purchaseReturn) {
            // Reverse stock
            foreach ($purchaseReturn->items as $item) {
                $stock = Stock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $purchaseReturn->warehouse_id)
                    ->lockForUpdate()
                    ->first();
                
                if ($stock) {
                    $stock->qty += $item->qty;
                    $stock->save();
                }
            }

            // Delete ledger entries
            LedgerEntry::where('reference_type', 'purchase_return')
                ->where('reference_id', $purchaseReturn->id)
                ->delete();

            // Delete purchase return
            $purchaseReturn->delete();
        });

        $this->logActivity('Deleted Purchase Return', 'Purchase Returns', $purchaseReturn->id, $purchaseReturn->toArray(), null);

        return redirect()->route('purchase-returns.index')->with('success', 'Purchase Return deleted successfully.');
    }
    public function createFromPurchase(Purchase $purchase)
    {
        $purchase->load(['items.product', 'party', 'warehouse']);
        
        $mappedItems = $purchase->items->map(function ($item) {
            // Landed cost (goods + per-item cargo share), not just price_pkr — otherwise a
            // return understates the value actually removed from Inventory and inflates the
            // remaining stock's average cost.
            $landedCost = $item->price_pkr + ($item->qty > 0 ? $item->cargo_amount / $item->qty : 0);
            return [
                'product_id' => $item->product_id,
                'search' => $item->product->name . ' (' . $item->product->sku . ')',
                'qty' => 0, // Default return quantity
                'cost' => round($landedCost, 2),
                'error' => false,
                'highlightedIndex' => -1,
                'open' => false,
                'original_qty' => $item->qty // Keep track of original
            ];
        });

        return view('purchase_returns.create_from_purchase', compact('purchase', 'mappedItems'));
    }

    public function storeFromPurchase(Request $request, Purchase $purchase)
    {
        $request->validate([
            'items' => 'array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0',
        ]);

        $purchaseReturn = null; // Initialize purchaseReturn outside the transaction
        DB::transaction(function () use ($request, $purchase, &$purchaseReturn) {
            $originalItems = $purchase->items()->get()->keyBy('product_id');
            $submittedItems = collect($request->items);
            
            $returnItems = [];
            $totalReturnAmount = 0;

            // 1. Identify Returned Items
            foreach ($originalItems as $productId => $originalItem) {
                $submittedItem = $submittedItems->firstWhere('product_id', $productId);
                
                $originalQty = $originalItem->qty;
                $returnQty = $submittedItem ? (float)$submittedItem['qty'] : 0; // Input is Return Qty

                if ($returnQty > $originalQty) {
                    throw new \Exception("Cannot return more than original quantity for product: {$originalItem->product_id}");
                }

                $newQty = $originalQty - $returnQty;

                if ($returnQty > 0) {
                    // Landed cost (goods + per-item cargo share) — see createFromPurchase().
                    $landedCost = round($originalItem->price_pkr + ($originalItem->qty > 0 ? $originalItem->cargo_amount / $originalItem->qty : 0), 2);
                    $returnItems[] = [
                        'product_id' => $productId,
                        'qty' => $returnQty,
                        'new_qty_for_purchase' => $newQty,
                        'cost' => $landedCost,
                        'total' => round($returnQty * $landedCost, 2)
                    ];
                    $totalReturnAmount += round($returnQty * $landedCost, 2);
                }
            }

            // If nothing changed, do nothing
            if (count($returnItems) == 0) {
                 return; 
            }

            // 2. Create Purchase Return Record
            $purchaseReturn = PurchaseReturn::create([
                'purchase_id' => $purchase->id,
                'party_id' => $purchase->party_id,
                'warehouse_id' => $purchase->warehouse_id,
                'date' => now(),
                'total_amount' => $totalReturnAmount,
                'note' => 'Auto-generated from Purchase modification',
            ]);

            // 3. Create Return Items and Reduce Stock (Update WAC)
        foreach ($returnItems as $item) {
            PurchaseReturnItem::create([
                'purchase_return_id' => $purchaseReturn->id,
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
                'cost' => $item['cost'],
                'total' => $item['total']
            ]);

            // Reduce Stock (We are returning to vendor, so stock goes out)
            $stock = Stock::where('product_id', $item['product_id'])
                ->where('warehouse_id', $purchase->warehouse_id)
                ->lockForUpdate()
                ->first();
            
            if ($stock) {
                $currentQty = $stock->qty;
                $currentAvgCost = $stock->avg_cost;
                $returnQty = $item['qty'];
                $returnCost = $item['cost'];

                $newQty = $currentQty - $returnQty;
                
                if ($newQty > 0) {
                    $newAvgCost = (($currentQty * $currentAvgCost) - ($returnQty * $returnCost)) / $newQty;
                    $stock->qty = $newQty;
                    $stock->avg_cost = max(0, round($newAvgCost, 2));
                } else {
                    $stock->qty = 0;
                    $stock->avg_cost = 0;
                }
                $stock->save();
            } 
        }

            // 4. Update Original Purchase Items
            foreach ($returnItems as $item) {
                if ($item['new_qty_for_purchase'] > 0) {
                     $purchase->items()->where('product_id', $item['product_id'])->update([
                        'qty' => $item['new_qty_for_purchase'],
                        'total' => $item['new_qty_for_purchase'] * $item['cost'] // Re-calc total for purchase item
                    ]);
                } else {
                    $purchase->items()->where('product_id', $item['product_id'])->delete();
                }
            }
            
            // Recalculate Purchase Total
            $newTotalAmount = $purchase->items()->sum('total');
            $purchase->update([
                'total_amount' => $newTotalAmount,
            ]);

            // 5. Accounting Entries for Return
             $purchaseReturnAccount = ChartOfAccount::firstOrCreate(
                ['name' => 'Purchase Return'],
                ['type' => 'Income', 'status' => true]
            );

            $accountsPayable = ChartOfAccount::where('name', 'Accounts Payable')->first();

            // Credit Purchase Return (Income)
            LedgerEntry::create([
                'account_id' => $purchaseReturnAccount->id,
                'party_id' => null,
                'warehouse_id' => $purchase->warehouse_id,
                'debit' => 0,
                'credit' => $totalReturnAmount,
                'date' => now(),
                'reference_type' => 'purchase_return',
                'reference_id' => $purchaseReturn->id,
                'note' => 'Purchase Return for Invoice #' . $purchase->id,
            ]);

            // Debit Vendor (Accounts Payable - Reduce Liability)
            LedgerEntry::create([
                'account_id' => $accountsPayable->id,
                'party_id' => $purchase->party_id,
                'warehouse_id' => $purchase->warehouse_id,
                'debit' => $totalReturnAmount,
                'credit' => 0,
                'date' => now(),
                'reference_type' => 'purchase_return',
                'reference_id' => $purchaseReturn->id,
                'note' => 'Purchase Return Debit for Invoice #' . $purchase->id,
            ]);

        });

        $this->logActivity('Created Purchase Return (From Purchase)', 'Purchase Returns', $purchaseReturn->id, null, $purchaseReturn->load('items')->toArray());

        return redirect()->route('purchases.index')->with('success', 'Purchase Return processed and Original Purchase updated.');
    }
}
