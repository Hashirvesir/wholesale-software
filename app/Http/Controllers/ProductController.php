<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use Illuminate\Http\Request;
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
        $product->load(['category', 'brand', 'unit', 'stocks.warehouse']);
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
        return view('products.create', compact('categories', 'brands', 'units'));
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
            'alert_quantity' => 'required|numeric|min:0',
            'status' => 'required|boolean',
        ]);

        $product = Product::create($request->all());

        $this->logActivity('Created Product', 'Products', $product->id, null, $product->toArray());

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
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
