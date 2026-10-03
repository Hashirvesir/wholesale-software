<?php

namespace App\Http\Controllers;

use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Stock;
use App\Traits\TracksActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class StockAdjustmentController extends Controller implements HasMiddleware
{
    use TracksActivity;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view stock adjustments', only: ['index', 'show']),
            new Middleware('permission:create stock adjustments', only: ['create', 'store']),
            new Middleware('permission:delete stock adjustments', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = StockAdjustment::with(['warehouse', 'items', 'user']);

        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $adjustments = $query->latest()->paginate(10)->withQueryString();
        $warehouses = Warehouse::all();

        return view('stock_adjustments.index', compact('adjustments', 'warehouses'));
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        $productsData = Product::with(['stocks', 'category'])->get()->map(function($product) {
            $product->warehouse_stocks = $product->stocks->pluck('qty', 'warehouse_id');
            $product->category_name = $product->category ? $product->category->name : '';
            return $product;
        });

        return view('stock_adjustments.create', compact('warehouses', 'productsData'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.type' => 'required|in:addition,subtraction',
            'note' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $adjustment = StockAdjustment::create([
                'date' => $request->date,
                'warehouse_id' => $request->warehouse_id,
                'note' => $request->note,
                'user_id' => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                StockAdjustmentItem::create([
                    'stock_adjustment_id' => $adjustment->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'type' => $item['type'],
                ]);

                $stock = Stock::where('product_id', $item['product_id'])
                ->where('warehouse_id', $request->warehouse_id)
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                $stock = Stock::create([
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $request->warehouse_id,
                    'qty' => 0,
                    'avg_cost' => 0
                ]);
            }

                if ($item['type'] === 'addition') {
                    $stock->increment('qty', $item['qty']);
                } else {
                    // Even if it goes negative, we allow it but it's good to have it logged
                    $stock->decrement('qty', $item['qty']);
                }
            }

            DB::commit();
        $this->logActivity('Stock Adjustment', 'Stock Adjustments', $adjustment->id, null, $adjustment->load('items')->toArray());
        return redirect()->route('stock-adjustments.index')->with('success', 'Stock adjustment recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to record adjustment: ' . $e->getMessage()]);
        }
    }

    public function show(StockAdjustment $stockAdjustment)
    {
        $stockAdjustment->load(['warehouse', 'items.product', 'user']);
        return view('stock_adjustments.show', compact('stockAdjustment'));
    }

    public function destroy(StockAdjustment $stockAdjustment)
    {
        try {
            DB::beginTransaction();

            foreach ($stockAdjustment->items as $item) {
            $stock = Stock::where('product_id', $item->product_id)
                ->where('warehouse_id', $stockAdjustment->warehouse_id)
                ->lockForUpdate()
                ->first();

                if ($stock) {
                    if ($item->type === 'addition') {
                        $stock->decrement('qty', $item->qty);
                    } else {
                        $stock->increment('qty', $item->qty);
                    }
                }
            }

            $stockAdjustment->delete();

        DB::commit();
        $this->logActivity('Deleted Stock Adjustment', 'Stock Adjustments', $stockAdjustment->id, $stockAdjustment->toArray(), null);
        return redirect()->route('stock-adjustments.index')->with('success', 'Adjustment deleted and stock effect reversed.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Could not delete adjustment: ' . $e->getMessage()]);
        }
    }
}
