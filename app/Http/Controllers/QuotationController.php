<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Party;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Setting;
use App\Traits\TracksActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class QuotationController extends Controller implements HasMiddleware
{
    use TracksActivity;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view sales', only: ['index', 'show', 'downloadPdf']),
            new Middleware('permission:create sales', only: ['create', 'store', 'convertToSale', 'updateStatus']),
            new Middleware('permission:edit sales', only: ['edit', 'update']),
            new Middleware('permission:delete sales', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = Quotation::with(['party', 'warehouse', 'items.product', 'convertedSale']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('party', fn ($p) => $p->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('party_id')) {
            $query->where('party_id', $request->party_id);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('from_date')) {
            $query->where('date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('date', '<=', $request->to_date);
        }

        $quotations = $query->latest()->paginate(15)->withQueryString();
        $customers = Party::where('type', 'customer')->get();
        $warehouses = Warehouse::all();
        $settings = Setting::first();

        return view('quotations.index', compact('quotations', 'customers', 'warehouses', 'settings'));
    }

    public function create()
    {
        $customers = Party::where('type', 'customer')->get()->map(function ($customer) {
            $customer->current_balance = $customer->getCurrentBalance();
            $customer->price_tier = $customer->price_tier ?? 'wholesale';
            return $customer;
        });

        $warehouses = Warehouse::all();

        $products = Product::with('stocks')->where('status', 1)->get()->map(function ($product) {
            $product->current_stock = $product->getCurrentStock();
            $product->wholesale_price = (float)($product->wholesale_price ?? $product->sale_price);
            $product->retail_price = (float)($product->retail_price ?? $product->sale_price);
            $product->dealer_price = (float)($product->dealer_price ?? $product->sale_price);
            $product->warehouse_stocks = $product->stocks->pluck('qty', 'warehouse_id');
            return $product;
        });

        $settings = Setting::first();

        return view('quotations.create', compact('customers', 'warehouses', 'products', 'settings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'party_id' => 'required|exists:parties,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        $quotation = DB::transaction(function () use ($request) {
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += round($item['qty'] * $item['price'], 2);
            }

            $discount = (float)($request->discount_amount ?? 0);
            $total = max(0, $subtotal - $discount);

            $quotation = Quotation::create([
                'party_id' => $request->party_id,
                'warehouse_id' => $request->warehouse_id,
                'date' => $request->date,
                'valid_until' => $request->valid_until,
                'total_amount' => $total,
                'discount_amount' => $discount,
                'status' => 'draft',
                'note' => $request->note,
            ]);

            foreach ($request->items as $item) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'total' => round($item['qty'] * $item['price'], 2),
                ]);
            }

            return $quotation;
        });

        $this->logActivity('Created Quotation', 'Quotations', $quotation->id, null, $quotation->toArray());

        return redirect()->route('quotations.show', $quotation)->with('success', 'Quotation / Estimate #' . $quotation->id . ' created successfully.');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['party', 'warehouse', 'items.product.unit', 'convertedSale']);
        $settings = Setting::first();
        $currentBalance = $quotation->party->getCurrentBalance();

        // Build WhatsApp share URL
        $whatsappUrl = '';
        if ($quotation->party && !empty($quotation->party->phone)) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $quotation->party->phone);
            if (str_starts_with($cleanPhone, '0')) {
                $cleanPhone = '92' . substr($cleanPhone, 1);
            }
            $signedUrl = URL::signedRoute('quotations.public-pdf', ['quotation' => $quotation->id]);
            $msg = "Dear {$quotation->party->name}, please find your Quotation / Estimate #{$quotation->id} for amount " . ($settings->currency_symbol ?? 'Rs') . " " . number_format($quotation->total_amount, 2) . ".\nView / Download: {$signedUrl}";
            $whatsappUrl = "https://wa.me/{$cleanPhone}?text=" . urlencode($msg);
        }

        return view('quotations.show', compact('quotation', 'settings', 'currentBalance', 'whatsappUrl'));
    }

    public function edit(Quotation $quotation)
    {
        if ($quotation->isConverted()) {
            return redirect()->route('quotations.show', $quotation)->with('error', 'Converted quotations cannot be modified.');
        }

        $customers = Party::where('type', 'customer')->get()->map(function ($customer) {
            $customer->current_balance = $customer->getCurrentBalance();
            $customer->price_tier = $customer->price_tier ?? 'wholesale';
            return $customer;
        });

        $warehouses = Warehouse::all();

        $products = Product::with('stocks')->where('status', 1)->get()->map(function ($product) {
            $product->current_stock = $product->getCurrentStock();
            $product->wholesale_price = (float)($product->wholesale_price ?? $product->sale_price);
            $product->retail_price = (float)($product->retail_price ?? $product->sale_price);
            $product->dealer_price = (float)($product->dealer_price ?? $product->sale_price);
            $product->warehouse_stocks = $product->stocks->pluck('qty', 'warehouse_id');
            return $product;
        });

        $quotation->load('items.product');
        $settings = Setting::first();

        return view('quotations.edit', compact('quotation', 'customers', 'warehouses', 'products', 'settings'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        if ($quotation->isConverted()) {
            return redirect()->route('quotations.show', $quotation)->with('error', 'Converted quotations cannot be modified.');
        }

        $request->validate([
            'party_id' => 'required|exists:parties,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        $oldData = $quotation->load('items')->toArray();

        DB::transaction(function () use ($request, $quotation) {
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += round($item['qty'] * $item['price'], 2);
            }

            $discount = (float)($request->discount_amount ?? 0);
            $total = max(0, $subtotal - $discount);

            $quotation->update([
                'party_id' => $request->party_id,
                'warehouse_id' => $request->warehouse_id,
                'date' => $request->date,
                'valid_until' => $request->valid_until,
                'total_amount' => $total,
                'discount_amount' => $discount,
                'note' => $request->note,
            ]);

            $quotation->items()->delete();

            foreach ($request->items as $item) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'total' => round($item['qty'] * $item['price'], 2),
                ]);
            }
        });

        $this->logActivity('Updated Quotation', 'Quotations', $quotation->id, $oldData, $quotation->load('items')->toArray());

        return redirect()->route('quotations.show', $quotation)->with('success', 'Quotation #' . $quotation->id . ' updated successfully.');
    }

    public function destroy(Quotation $quotation)
    {
        if ($quotation->isConverted()) {
            return redirect()->route('quotations.show', $quotation)->with('error', 'Converted quotations cannot be deleted.');
        }

        $oldData = $quotation->load('items')->toArray();
        $id = $quotation->id;
        $quotation->delete();

        $this->logActivity('Deleted Quotation', 'Quotations', $id, $oldData, null);

        return redirect()->route('quotations.index')->with('success', 'Quotation #' . $id . ' deleted successfully.');
    }

    public function updateStatus(Request $request, Quotation $quotation)
    {
        $request->validate([
            'status' => 'required|in:draft,sent,accepted,cancelled',
        ]);

        $quotation->update(['status' => $request->status]);

        return back()->with('success', 'Quotation status updated to ' . ucfirst($request->status));
    }

    public function convertToSale(Quotation $quotation)
    {
        if ($quotation->isConverted()) {
            return redirect()->route('sales.show', $quotation->converted_sale_id)->with('info', 'This quotation is already converted to Sale #' . $quotation->converted_sale_id);
        }

        // Redirect to sales.create with quotation ID pre-filled
        return redirect()->route('sales.create', ['from_quotation' => $quotation->id]);
    }

    public function downloadPdf(Quotation $quotation)
    {
        $quotation->load(['party', 'warehouse', 'items.product.unit']);
        $settings = Setting::first();

        $pdf = Pdf::loadView('quotations.pdf', compact('quotation', 'settings'));
        return $pdf->stream('Quotation-' . $quotation->id . '.pdf');
    }

    public function publicPdf(Quotation $quotation)
    {
        return $this->downloadPdf($quotation);
    }
}
