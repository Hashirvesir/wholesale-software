<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Models\Stock;
use App\Models\OpeningStock;
use App\Models\ChartOfAccount;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\TracksActivity;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProductController extends Controller implements HasMiddleware
{
    use TracksActivity;
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view products', only: ['index', 'show']),
            new Middleware('permission:manage products', except: ['index', 'show']),
        ];
    }
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'unit', 'stocks']);

        // Search by Name or SKU
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filter by Category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by Brand
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // Filter by Warehouse
        if ($request->filled('warehouse_id')) {
            $warehouseId = $request->warehouse_id;
            $query->whereHas('stocks', function($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            });
        }

        $products = $query->latest()->paginate(10)->withQueryString();
        
        $categories = Category::all();
        $brands = Brand::all();
        $warehouses = \App\Models\Warehouse::all();
        
        return view('products.index', compact('products', 'categories', 'brands', 'warehouses'));
    }

    public function show(Product $product)
    {
        $product->load(['category', 'brand', 'unit', 'stocks.warehouse', 'openingStocks.warehouse']);
        $purchaseItems = \App\Models\PurchaseItem::where('product_id', $product->id)
            ->with(['purchase' => fn($q) => $q->with('party', 'warehouse')])
            ->orderByDesc('id')
            ->get();
        return view('products.show', compact('product', 'purchaseItems'));
    }

    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all();
        $units = Unit::all();
        $warehouses = Warehouse::all();
        return view('products.create', compact('categories', 'brands', 'units', 'warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:products,sku',
            'barcode' => 'nullable|string|max:255|unique:products,barcode',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'unit_id' => 'required|exists:units,id',
            'sale_price' => 'required|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'retail_price' => 'nullable|numeric|min:0',
            'dealer_price' => 'nullable|numeric|min:0',
            'alert_quantity' => 'required|numeric|min:0',
            'status' => 'required|boolean',
            'has_opening_stock' => 'nullable|boolean',
            'opening_stock_qty' => 'nullable|numeric|min:0',
            'opening_stock_warehouse_id' => 'nullable|exists:warehouses,id',
            'opening_stock_rate' => 'nullable|numeric|min:0',
            'opening_stock_date' => 'nullable|date',
            'opening_stock_note' => 'nullable|string|max:500',
        ]);

        $product = DB::transaction(function () use ($request) {
            $productData = $request->only([
                'name', 'sku', 'barcode', 'category_id', 'brand_id', 'unit_id',
                'sale_price', 'wholesale_price', 'retail_price', 'dealer_price',
                'alert_quantity', 'status'
            ]);

            $product = Product::create($productData);

            $hasOpening = $request->boolean('has_opening_stock') || ($request->filled('opening_stock_qty') && (float) $request->opening_stock_qty > 0);
            $qty = (float) $request->input('opening_stock_qty', 0);

            if ($hasOpening && $qty > 0) {
                $warehouseId = $request->input('opening_stock_warehouse_id') ?: (Warehouse::first()->id ?? 1);
                $rate = (float) $request->input('opening_stock_rate', 0);
                if ($rate <= 0) {
                    $rate = (float) $request->input('wholesale_price', 0);
                }
                if ($rate <= 0) {
                    $rate = (float) $request->input('sale_price', 0);
                }

                $date = $request->input('opening_stock_date', now()->toDateString());
                $note = $request->input('opening_stock_note') ?: "Initial Opening Stock on product creation";
                $total = round($qty * $rate, 2);

                // 1. Create OpeningStock record
                $openingStock = OpeningStock::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouseId,
                    'qty' => $qty,
                    'rate' => $rate,
                    'cargo_amount' => 0,
                    'total' => $total,
                    'date' => $date,
                    'note' => $note,
                ]);

                // 2. Create or update Stock record
                $stock = Stock::where('product_id', $product->id)
                              ->where('warehouse_id', $warehouseId)
                              ->first();

                if ($stock) {
                    $currentQty = $stock->qty;
                    $currentAvgCost = $stock->avg_cost;
                    $newQty = $currentQty + $qty;
                    $newAvgCost = $newQty > 0 ? (($currentQty * $currentAvgCost) + ($qty * $rate)) / $newQty : $rate;

                    $stock->update([
                        'qty' => $newQty,
                        'avg_cost' => round($newAvgCost, 2)
                    ]);
                } else {
                    Stock::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouseId,
                        'qty' => $qty,
                        'avg_cost' => $rate
                    ]);
                }

                // 3. Accounting Ledger Entries (Double-Entry: Debit Inventory, Credit Opening Balance Equity)
                if ($total > 0) {
                    $inventoryAcc = ChartOfAccount::where('name', 'Inventory')->first();
                    $equityAcc = ChartOfAccount::firstOrCreate(
                        ['name' => 'Opening Balance Equity'],
                        ['type' => 'Equity', 'status' => true]
                    );

                    if ($inventoryAcc) {
                        LedgerEntry::create([
                            'account_id' => $inventoryAcc->id,
                            'party_id' => null,
                            'warehouse_id' => $warehouseId,
                            'debit' => $total,
                            'credit' => 0,
                            'date' => $date,
                            'reference_type' => 'opening_stock',
                            'reference_id' => $openingStock->id,
                            'note' => "Opening Stock for {$product->name}",
                        ]);
                    }

                    LedgerEntry::create([
                        'account_id' => $equityAcc->id,
                        'party_id' => null,
                        'warehouse_id' => $warehouseId,
                        'debit' => 0,
                        'credit' => $total,
                        'date' => $date,
                        'reference_type' => 'opening_stock',
                        'reference_id' => $openingStock->id,
                        'note' => "Opening Stock for {$product->name}",
                    ]);
                }
            }

            return $product;
        });

        $this->logActivity('Created Product', 'Products', $product->id, null, $product->toArray());

        $hasStockMsg = $request->filled('opening_stock_qty') && (float) $request->opening_stock_qty > 0 ? ' with initial opening stock.' : '.';
        return redirect()->route('products.index')->with('success', 'Product created successfully' . $hasStockMsg);
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $brands = Brand::all();
        $units = Unit::all();
        return view('products.edit', compact('product', 'categories', 'brands', 'units'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|max:255|unique:products,barcode,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'unit_id' => 'required|exists:units,id',
            'sale_price' => 'required|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'retail_price' => 'nullable|numeric|min:0',
            'dealer_price' => 'nullable|numeric|min:0',
            'alert_quantity' => 'required|numeric|min:0',
            'status' => 'required|boolean',
        ]);

        $oldData = $product->toArray();
        $product->update($request->all());

        $this->logActivity('Updated Product', 'Products', $product->id, $oldData, $product->toArray());

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->purchaseItems()->exists() || $product->saleItems()->exists()) {
            return redirect()->route('products.index')->with('error', 'Cannot delete product with existing transactions.');
        }
        $oldData = $product->toArray();
        $product->delete();
        $this->logActivity('Deleted Product', 'Products', $product->id, $oldData, null);
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function printLabels(Request $request)
    {
        $products = Product::all(['id', 'name', 'sku', 'sale_price', 'barcode']);
        return view('products.print_labels', compact('products'));
    }

    public function getBarcode(Product $product)
    {
        return response($product->getBarcodeSVG());
    }

    public function exportPdf(Request $request)
    {
        $query = Product::with(['category', 'brand', 'unit', 'stocks']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        $products = $query->latest()->get();
        $settings = \App\Models\Setting::first();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('products.export_pdf', compact('products', 'settings'));
        $pdf->setPaper('a4', 'landscape');
        return $pdf->download('products-list-' . date('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $query = Product::with(['category', 'brand', 'unit', 'stocks']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        $products = $query->latest()->get();

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'SKU', 'Barcode', 'Category', 'Brand', 'Unit', 'Sale Price', 'Current Stock', 'Alert Qty', 'Status']);
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->name,
                    $product->sku,
                    $product->barcode ?? $product->sku,
                    $product->category->name,
                    $product->brand->name,
                    $product->unit->short_name,
                    $product->sale_price,
                    $product->getCurrentStock(),
                    $product->alert_quantity,
                    $product->status ? 'Active' : 'Inactive',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products-list-' . date('Y-m-d') . '.csv"',
        ]);
    }
}
