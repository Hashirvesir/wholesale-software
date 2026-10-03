<?php

namespace App\Http\Controllers;

use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\Sale;
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

class SalesReturnController extends Controller implements HasMiddleware
{
    use TracksActivity;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view sales', only: ['index', 'show']),
            new Middleware('permission:return sales', only: ['create', 'store', 'createFromSale']),
            new Middleware('permission:delete sales', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = SalesReturn::with(['party', 'warehouse', 'sale']);

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

        $salesReturns = $query->latest()->paginate(10)->withQueryString();
        $warehouses = Warehouse::all();
        
        return view('sales_returns.index', compact('salesReturns', 'warehouses'));
    }

    public function create()
    {
        $sales = Sale::with('party')->latest()->get();
        $warehouses = Warehouse::where('status', true)->get();
        
        return view('sales_returns.create', compact('sales', 'warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $salesReturn = DB::transaction(function () use ($request) {
            $sale = Sale::findOrFail($request->sale_id);
            
            // Calculate total
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += round($item['qty'] * $item['price'], 2);
            }

            // Create Sales Return
            $salesReturn = SalesReturn::create([
                'sale_id' => $request->sale_id,
                'party_id' => $sale->party_id,
                'warehouse_id' => $request->warehouse_id,
                'date' => $request->date,
                'total_amount' => $totalAmount,
                'note' => $request->note,
            ]);

            // Create Sales Return Items & Update Stock
            $totalCogs = 0;
            foreach ($request->items as $item) {
                SalesReturnItem::create([
                    'sales_return_id' => $salesReturn->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'total' => round($item['qty'] * $item['price'], 2),
                ]);

                // Add stock back
            $stock = Stock::where('product_id', $item['product_id'])
                ->where('warehouse_id', $request->warehouse_id)
                ->lockForUpdate()
                ->first();

                if ($stock) {
                    $itemCogs = round($item['qty'] * $stock->avg_cost, 2);
                    $totalCogs += $itemCogs;

                    $stock->qty += $item['qty'];
                    $stock->save();
                } else {
                    Stock::create([
                        'product_id' => $item['product_id'],
                        'warehouse_id' => $request->warehouse_id,
                        'qty' => $item['qty'],
                        'avg_cost' => 0,
                    ]);
                }
            }

            // Accounting Entries
            $salesReturnAccount = ChartOfAccount::where('name', 'Sales Return')->first();
            if (!$salesReturnAccount) {
                $salesReturnAccount = ChartOfAccount::create([
                    'name' => 'Sales Return',
                    'type' => 'Income', // Contra-Revenue
                    'status' => true,
                ]);
            }

            $accountsReceivable = ChartOfAccount::where('name', 'Accounts Receivable')->first();
            $inventoryAccount = ChartOfAccount::where('name', 'Inventory')->first();
            $cogsAccount = ChartOfAccount::where('name', 'COGS')->first();

            // 1. Revenue Reversal: Debit Sales Return (effectively reducing revenue), Credit Customer
            LedgerEntry::create([
                'account_id' => $salesReturnAccount->id,
                'party_id' => null,
                'warehouse_id' => $request->warehouse_id,
                'debit' => $totalAmount,
                'credit' => 0,
                'date' => $request->date,
                'reference_type' => 'sales_return',
                'reference_id' => $salesReturn->id,
                'note' => 'Sales Return - Revenue Reversal',
            ]);

            LedgerEntry::create([
                'account_id' => $accountsReceivable->id,
                'party_id' => $sale->party_id,
                'warehouse_id' => $request->warehouse_id,
                'debit' => 0,
                'credit' => $totalAmount,
                'date' => $request->date,
                'reference_type' => 'sales_return',
                'reference_id' => $salesReturn->id,
                'note' => 'Sales Return - Credit to Customer',
            ]);

            // 2. COGS Reversal: Debit Inventory (restoring asset), Credit COGS (reducing expense)
            if ($totalCogs > 0) {
                LedgerEntry::create([
                    'account_id' => $inventoryAccount->id,
                    'party_id' => null,
                    'warehouse_id' => $request->warehouse_id,
                    'debit' => $totalCogs,
                    'credit' => 0,
                    'date' => $request->date,
                    'reference_type' => 'sales_return',
                    'reference_id' => $salesReturn->id,
                    'note' => 'Sales Return - Inventory Restored',
                ]);

                LedgerEntry::create([
                    'account_id' => $cogsAccount->id,
                    'party_id' => null,
                    'warehouse_id' => $request->warehouse_id,
                    'debit' => 0,
                    'credit' => $totalCogs,
                    'date' => $request->date,
                    'reference_type' => 'sales_return',
                    'reference_id' => $salesReturn->id,
                    'note' => 'Sales Return - COGS Reversed',
                ]);
            }
            return $salesReturn;
        });

        $this->logActivity('Created Sales Return', 'Sales Returns', $salesReturn->id, null, $salesReturn->load('items')->toArray());

        return redirect()->route('sales-returns.index')->with('success', 'Sales Return created successfully.');
    }

    public function show(SalesReturn $salesReturn)
    {
        $salesReturn->load(['party', 'warehouse', 'sale', 'items.product.unit']);
        return view('sales_returns.show', compact('salesReturn'));
    }

    public function destroy(SalesReturn $salesReturn)
    {
        DB::transaction(function () use ($salesReturn) {
            // Reverse stock
        foreach ($salesReturn->items as $item) {
            $stock = Stock::where('product_id', $item->product_id)
                ->where('warehouse_id', $salesReturn->warehouse_id)
                ->lockForUpdate()
                ->first();
                
                if ($stock) {
                    $stock->qty -= $item->qty;
                    $stock->save();
                }
            }

            // Delete ledger entries
            LedgerEntry::where('reference_type', 'sales_return')
                ->where('reference_id', $salesReturn->id)
                ->delete();

            // Delete sales return
            $salesReturn->delete();
        });

        $this->logActivity('Deleted Sales Return', 'Sales Returns', $salesReturn->id, $salesReturn->toArray(), null);

        return redirect()->route('sales-returns.index')->with('success', 'Sales Return deleted successfully.');
    }
    public function createFromSale(Sale $sale)
    {
        $sale->load(['items.product', 'party', 'warehouse']);
        
        $mappedItems = $sale->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'search' => $item->product->name . ' (' . $item->product->sku . ')',
                'qty' => 0, // Default return quantity is 0
                'price' => $item->price,
                'error' => false,
                'highlightedIndex' => -1,
                'open' => false,
                'original_qty' => $item->qty // Keep track of original
            ];
        });

        $products = Product::where('status', true)->get();

        return view('sales_returns.create_from_sale', compact('sale', 'mappedItems', 'products'));
    }

    public function storeFromSale(Request $request, Sale $sale)
    {
        $request->validate([
            'items' => 'array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0',
        ]);

        $salesReturn = DB::transaction(function () use ($request, $sale) {
            $originalItems = $sale->items()->get()->keyBy('product_id');
            $submittedItems = collect($request->items);
            
            $returnItems = [];
            $totalReturnAmount = 0;

            // 1. Identify Returned Items
            foreach ($originalItems as $productId => $originalItem) {
                $submittedItem = $submittedItems->firstWhere('product_id', $productId);
                
                $originalQty = $originalItem->qty;
                // Here, submitted 'qty' is the RETURN QUANTITY
                $returnQty = $submittedItem ? (float)$submittedItem['qty'] : 0; 
                
                if ($returnQty > $originalQty) {
                    throw new \Exception("Cannot return more than original quantity for product: {$originalItem->product_id}");
                }

                $newQty = $originalQty - $returnQty; // This is what remains in the sale

                if ($returnQty > 0) {
                    $returnItems[] = [
                        'product_id' => $productId,
                        'qty' => $returnQty,
                        'new_qty_for_sale' => $newQty,
                        'price' => $originalItem->price,
                        'total' => $returnQty * $originalItem->price
                    ];
                    $totalReturnAmount += ($returnQty * $originalItem->price);
                }
            }

            // If nothing returned, do nothing
            if (count($returnItems) == 0) {
                 return null; 
            }

            // 2. Create Sales Return Record
            $salesReturn = SalesReturn::create([
                'sale_id' => $sale->id,
                'party_id' => $sale->party_id,
                'warehouse_id' => $sale->warehouse_id,
                'date' => now(),
                'total_amount' => $totalReturnAmount,
                'note' => 'Auto-generated from Sale modification',
            ]);

            // 3. Create Return Items and Restore Stock
            $totalCogs = 0;
            foreach ($returnItems as $item) {
                SalesReturnItem::create([
                    'sales_return_id' => $salesReturn->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'total' => $item['total']
                ]);

                // Restore Stock (Customer returned item, so stock increases)
            $stock = Stock::where('product_id', $item['product_id'])
                ->where('warehouse_id', $sale->warehouse_id)
                ->lockForUpdate()
                ->first();
                
                if ($stock) {
                    $itemCogs = round($item['qty'] * $stock->avg_cost, 2);
                    $totalCogs += $itemCogs;

                    $stock->qty += $item['qty'];
                    $stock->save();
                } else {
                    Stock::create([
                         'product_id' => $item['product_id'],
                         'warehouse_id' => $sale->warehouse_id,
                         'qty' => $item['qty'],
                         'avg_cost' => 0 
                    ]);
                }
            }

            // 4. Update Original Sale Items
            // We only need to update the items that changed.
            foreach ($returnItems as $item) {
                if ($item['new_qty_for_sale'] > 0) {
                    // Update existing item
                     $sale->items()->where('product_id', $item['product_id'])->update([
                        'qty' => $item['new_qty_for_sale'],
                        'total' => $item['new_qty_for_sale'] * $item['price']
                    ]);
                } else {
                    // Fully returned, delete item
                    $sale->items()->where('product_id', $item['product_id'])->delete();
                }
            }
            
            // Recalculate Sale Total
            $newTotalAmount = $sale->items()->sum('total');
            $sale->update([
                'total_amount' => $newTotalAmount,
            ]);

            // 5. Accounting Entries for Return
            $salesReturnAccount = ChartOfAccount::firstOrCreate(
                ['name' => 'Sales Return'],
                ['type' => 'Income', 'status' => true]
            );

            $accountsReceivable = ChartOfAccount::where('name', 'Accounts Receivable')->first();
            $inventoryAccount = ChartOfAccount::where('name', 'Inventory')->first();
            $cogsAccount = ChartOfAccount::where('name', 'COGS')->first();

            // 1. Revenue Reversal: Debit Sales Return, Credit Customer
            LedgerEntry::create([
                'account_id' => $salesReturnAccount->id,
                'party_id' => null,
                'warehouse_id' => $sale->warehouse_id,
                'debit' => $totalReturnAmount,
                'credit' => 0,
                'date' => now(),
                'reference_type' => 'sales_return',
                'reference_id' => $salesReturn->id,
                'note' => 'Sales Return for Invoice #' . $sale->id,
            ]);

            LedgerEntry::create([
                'account_id' => $accountsReceivable->id,
                'party_id' => $sale->party_id,
                'warehouse_id' => $sale->warehouse_id,
                'debit' => 0,
                'credit' => $totalReturnAmount,
                'date' => now(),
                'reference_type' => 'sales_return',
                'reference_id' => $salesReturn->id,
                'note' => 'Sales Return Credit for Invoice #' . $sale->id,
            ]);

            // 2. COGS Reversal: Debit Inventory, Credit COGS
            if ($totalCogs > 0) {
                LedgerEntry::create([
                    'account_id' => $inventoryAccount->id,
                    'warehouse_id' => $sale->warehouse_id,
                    'debit' => $totalCogs,
                    'credit' => 0,
                    'date' => now(),
                    'reference_type' => 'sales_return',
                    'reference_id' => $salesReturn->id,
                    'note' => 'Sales Return - Inventory Restored for Invoice #' . $sale->id,
                ]);

                LedgerEntry::create([
                    'account_id' => $cogsAccount->id,
                    'warehouse_id' => $sale->warehouse_id,
                    'debit' => 0,
                    'credit' => $totalCogs,
                    'date' => now(),
                    'reference_type' => 'sales_return',
                    'reference_id' => $salesReturn->id,
                    'note' => 'Sales Return - COGS Reversed for Invoice #' . $sale->id,
                ]);
            }
            return $salesReturn;
        });

        if (!$salesReturn) {
            return redirect()->route('sales.index')->with('info', 'No items were selected for return.');
        }

        $this->logActivity('Created Sales Return (From Sale)', 'Sales Returns', $salesReturn->id, null, $salesReturn->load('items')->toArray());

        return redirect()->route('sales.index')->with('success', 'Sales Return processed and Original Sale updated.');
    }
}
