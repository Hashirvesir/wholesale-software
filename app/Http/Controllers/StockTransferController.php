<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Stock;
use App\Traits\TracksActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class StockTransferController extends Controller implements HasMiddleware
{
    use TracksActivity;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view stock transfers', only: ['index', 'show']),
            new Middleware('permission:create stock transfers', only: ['create', 'store', 'edit', 'update']),
            new Middleware('permission:delete stock transfers', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'items']);

        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }
        if ($request->filled('from_warehouse_id')) {
            $query->where('from_warehouse_id', $request->from_warehouse_id);
        }
        if ($request->filled('to_warehouse_id')) {
            $query->where('to_warehouse_id', $request->to_warehouse_id);
        }

        $transfers = $query->latest()->paginate(10)->withQueryString();
        $warehouses = Warehouse::all();

        return view('stock_transfers.index', compact('transfers', 'warehouses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $warehouses = Warehouse::all();
        $products = Product::all(); // We handle stocks via AJAX or pass all with warehouse stocks
        
        // Passing products with warehouse stocks for easier access in Alpine.js
        $productsData = Product::with(['stocks', 'category'])->get()->map(function($product) {
            $product->warehouse_stocks = $product->stocks->pluck('qty', 'warehouse_id');
            $product->category_name = $product->category ? $product->category->name : '';
            return $product;
        });

        return view('stock_transfers.create', compact('warehouses', 'productsData'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'from_warehouse_id' => 'required|exists:warehouses,id|different:to_warehouse_id',
            'to_warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'note' => 'nullable|string',
        ], [
            'from_warehouse_id.different' => 'From and To warehouses must be different.',
            'items.required' => 'Please add at least one product to transfer.',
        ]);

        try {
            DB::beginTransaction();

            // Check if sufficient stock exists in from_warehouse
            foreach ($request->items as $item) {
                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $request->from_warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if (!$stock || $stock->qty < $item['qty']) {
                    $product = Product::find($item['product_id']);
                    throw new \Exception("Insufficient stock for product: {$product->name} in the source warehouse.");
                }
            }

            $transfer = StockTransfer::create([
                'date' => $request->date,
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'note' => $request->note,
                'user_id' => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                ]);

                // Update From Warehouse Stock
                $fromStock = Stock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $request->from_warehouse_id)
                    ->lockForUpdate()
                    ->first();
                $fromStock->decrement('qty', $item['qty']);

                // Update To Warehouse Stock
                $toStock = Stock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $request->to_warehouse_id)
                    ->lockForUpdate()
                    ->first();
                
                if (!$toStock) {
                    $toStock = Stock::create([
                        'product_id' => $item['product_id'],
                        'warehouse_id' => $request->to_warehouse_id,
                        'qty' => 0,
                        'avg_cost' => $fromStock->avg_cost
                    ]);
                }
                
                $toStock->increment('qty', $item['qty']);
            }

            DB::commit();
            $this->logActivity('Stock Transfer', 'Stock Transfers', $transfer->id, null, $transfer->load('items')->toArray());
            return redirect()->route('stock-transfers.index')->with('success', 'Stock transferred successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StockTransfer $stockTransfer)
    {
        $stockTransfer->load(['fromWarehouse', 'toWarehouse', 'items.product', 'user']);
        return view('stock_transfers.show', compact('stockTransfer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StockTransfer $stockTransfer)
    {
        // Edit might be complex for stock transfers due to reversals.
        // Usually best to delete and recreate or keep read-only.
        // I will implement a basic edit view.
        $warehouses = Warehouse::all();
        $productsData = Product::with(['stocks', 'category'])->get()->map(function($product) {
            $product->warehouse_stocks = $product->stocks->pluck('qty', 'warehouse_id');
            $product->category_name = $product->category ? $product->category->name : '';
            return $product;
        });

        $stockTransfer->load('items.product');
        
        $mappedItems = $stockTransfer->items->map(function($item) {
            return [
                'product_id' => $item->product_id,
                'qty' => (float)$item->qty,
                'search' => $item->product->name . ' (' . $item->product->sku . ')',
                'open' => false,
                'error' => false,
                'highlightedIndex' => -1
            ];
        });

        return view('stock_transfers.edit', compact('stockTransfer', 'warehouses', 'productsData', 'mappedItems'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StockTransfer $stockTransfer)
    {
        $request->validate([
            'date' => 'required|date',
            'from_warehouse_id' => 'required|exists:warehouses,id|different:to_warehouse_id',
            'to_warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'note' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // 1. Reverse old transfer
            foreach ($stockTransfer->items as $oldItem) {
                // Return to source
                Stock::where('product_id', $oldItem->product_id)
                    ->where('warehouse_id', $stockTransfer->from_warehouse_id)
                    ->increment('qty', $oldItem->qty);
                
                // Remove from destination
                Stock::where('product_id', $oldItem->product_id)
                    ->where('warehouse_id', $stockTransfer->to_warehouse_id)
                    ->decrement('qty', $oldItem->qty);
            }

            // 2. Validate new quantities against current (reversed) stock
            foreach ($request->items as $item) {
                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $request->from_warehouse_id)
                    ->first();

                if (!$stock || $stock->qty < $item['qty']) {
                    $product = Product::find($item['product_id']);
                    throw new \Exception("Insufficient stock for product: {$product->name} in the source warehouse after reversal.");
                }
            }

            // 3. Update transfer record
            $stockTransfer->update([
                'date' => $request->date,
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'note' => $request->note,
            ]);

            // 4. Delete old items and create new ones
            $stockTransfer->items()->delete();

            foreach ($request->items as $item) {
                StockTransferItem::create([
                    'stock_transfer_id' => $stockTransfer->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                ]);

                // Apply new transfer
                Stock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $request->from_warehouse_id)
                    ->decrement('qty', $item['qty']);

                $toStock = Stock::firstOrCreate(
                    ['product_id' => $item['product_id'], 'warehouse_id' => $request->to_warehouse_id],
                    ['qty' => 0, 'avg_cost' => 0]
                );
                $toStock->increment('qty', $item['qty']);
            }

            DB::commit();
            return redirect()->route('stock-transfers.index')->with('success', 'Stock transfer updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockTransfer $stockTransfer)
    {
        try {
            DB::beginTransaction();

            foreach ($stockTransfer->items as $item) {
                // Return to source
                Stock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $stockTransfer->from_warehouse_id)
                    ->lockForUpdate()
                    ->increment('qty', $item->qty);
                
                // Remove from destination
                Stock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $stockTransfer->to_warehouse_id)
                    ->lockForUpdate()
                    ->decrement('qty', $item->qty);
            }

            $stockTransfer->delete();

            DB::commit();
            $this->logActivity('Deleted Stock Transfer', 'Stock Transfers', $stockTransfer->id, $stockTransfer->toArray(), null);
            return redirect()->route('stock-transfers.index')->with('success', 'Stock transfer cancelled and storage reversed.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Could not delete transfer: ' . $e->getMessage()]);
        }
    }
}
