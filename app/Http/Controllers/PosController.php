<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Party;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Category;
use App\Models\Brand;
use App\Models\ChartOfAccount;
use App\Models\LedgerEntry;
use App\Models\PaymentSplit;
use App\Models\Setting;
use App\Traits\TracksActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PosController extends Controller implements HasMiddleware
{
    use TracksActivity;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view sales', only: ['index']),
            new Middleware('permission:create sales', only: ['store', 'quickAddCustomer']),
        ];
    }

    public function index()
    {
        $customers = Party::where('type', 'customer')->get()->map(function ($customer) {
            $customer->current_balance = (float)$customer->getCurrentBalance();
            $customer->credit_limit = (float)($customer->credit_limit ?? 0);
            $customer->credit_days = (int)($customer->credit_days ?? 0);
            $customer->has_overdue = $customer->hasOverdueInvoices();
            return $customer;
        });

        $warehouses = Warehouse::all();
        $categories = Category::all();
        $brands = Brand::all();

        $products = Product::with(['stocks', 'category', 'unit', 'brand'])
            ->where('status', 1)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku ?? '',
                    'barcode' => $product->barcode ?? '',
                    'category_id' => $product->category_id,
                    'category_name' => $product->category->name ?? '',
                    'brand_id' => $product->brand_id,
                    'brand_name' => $product->brand->name ?? '',
                    'unit_name' => $product->unit->name ?? 'pc',
                    'sale_price' => (float)$product->sale_price,
                    'current_stock' => (float)$product->getCurrentStock(),
                    'warehouse_stocks' => $product->stocks->pluck('qty', 'warehouse_id')->map(fn ($q) => (float)$q),
                    'warehouse_costs' => $product->stocks->pluck('avg_cost', 'warehouse_id')->map(fn ($c) => (float)$c),
                ];
            });

        $paymentAccounts = ChartOfAccount::where('type', 'Asset')
            ->whereNotIn('name', ['Inventory', 'Accounts Receivable'])
            ->where('status', true)
            ->get();

        $settings = Setting::first();

        return view('pos.index', compact('customers', 'warehouses', 'categories', 'brands', 'products', 'paymentAccounts', 'settings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'party_id' => 'required|exists:parties,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'payments' => 'nullable|array',
            'payments.*.account_id' => 'nullable|exists:chart_of_accounts,id',
            'payments.*.amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        // Duplicate Product + Rate check
        $combinations = [];
        foreach ($request->items as $item) {
            $key = $item['product_id'] . '-' . $item['price'];
            if (in_array($key, $combinations)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The same product cannot be added twice with the same price/rate.',
                ], 422);
            }
            $combinations[] = $key;
        }

        // Validate payment row accounts
        foreach ($request->payments ?? [] as $payment) {
            if ((float)($payment['amount'] ?? 0) > 0 && empty($payment['account_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select an account for every payment amount entered.',
                ], 422);
            }
        }

        // Walk-In Customer full payment verification
        $settings = Setting::first();
        if ($settings && $settings->walk_in_customer_id && $request->party_id == $settings->walk_in_customer_id) {
            $subtotal = collect($request->items)->sum(fn ($item) => round($item['qty'] * $item['price'], 2));
            $total = max(0, $subtotal - ($request->discount_amount ?? 0));
            $totalPaid = collect($request->payments ?? [])->sum(fn ($payment) => (float)($payment['amount'] ?? 0));

            if ($totalPaid + 0.01 < $total) {
                return response()->json([
                    'success' => false,
                    'message' => 'Walk-In Customer sales must be paid in full — no credit allowed.',
                ], 422);
            }
        }

        try {
            $sale = DB::transaction(function () use ($request) {
                $payments = collect($request->payments ?? [])
                    ->filter(fn ($payment) => (float)($payment['amount'] ?? 0) > 0)
                    ->values()
                    ->all();

                $subtotal = 0;
                foreach ($request->items as $item) {
                    $subtotal += round($item['qty'] * $item['price'], 2);
                }

                $discount = (float)($request->discount_amount ?? 0);
                $total = max(0, $subtotal - $discount);

                $party = Party::findOrFail($request->party_id);
                $currentBalance = $party->getCurrentBalance();
                $totalPaid = collect($payments)->sum('amount');

                // If customer has advance balance (credit), automatically use it
                $advanceUsed = 0;
                if ($currentBalance < 0) {
                    $advanceAvailable = abs($currentBalance);
                    $advanceUsed = min($advanceAvailable, $total);
                }

                $invoicePaidAmount = min($total, $totalPaid + $advanceUsed);

                $sale = Sale::create([
                    'party_id' => $request->party_id,
                    'warehouse_id' => $request->warehouse_id,
                    'account_id' => $payments[0]['account_id'] ?? null,
                    'date' => $request->date,
                    'total_amount' => $total,
                    'discount_amount' => $discount,
                    'paid_amount' => $invoicePaidAmount,
                    'note' => $request->note . ($advanceUsed > 0 ? " (Covered by Advance: " . ($settings->currency_symbol ?? 'Rs') . " " . number_format($advanceUsed, 2) . ")" : ""),
                ]);

                // Create Sale Items and reduce stock
                $totalCogs = 0;
                foreach ($request->items as $item) {
                    $itemTotal = round($item['qty'] * $item['price'], 2);

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'qty' => $item['qty'],
                        'price' => $item['price'],
                        'total' => $itemTotal,
                    ]);

                    $stock = Stock::where('product_id', $item['product_id'])
                        ->where('warehouse_id', $request->warehouse_id)
                        ->lockForUpdate()
                        ->first();

                    if ($stock) {
                        $totalCogs += round($item['qty'] * $stock->avg_cost, 2);
                        $stock->qty -= $item['qty'];
                        $stock->save();
                    } else {
                        Stock::create([
                            'product_id' => $item['product_id'],
                            'warehouse_id' => $request->warehouse_id,
                            'qty' => -$item['qty'],
                            'avg_cost' => 0,
                        ]);
                    }
                }

                // Double Entry Accounting
                $accountsReceivable = ChartOfAccount::where('name', 'Accounts Receivable')->first();
                $salesAccount = ChartOfAccount::where('name', 'Sales')->first();
                $cogsAccount = ChartOfAccount::where('name', 'COGS')->first();
                $inventoryAccount = ChartOfAccount::where('name', 'Inventory')->first();

                // 1. Sale Entry: Debit Customer, Credit Sales
                LedgerEntry::create([
                    'account_id' => $accountsReceivable->id,
                    'party_id' => $sale->party_id,
                    'warehouse_id' => $sale->warehouse_id,
                    'debit' => $sale->total_amount,
                    'credit' => 0,
                    'date' => $sale->date,
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'note' => $sale->note ?? "POS Sale to {$sale->party->name}",
                ]);

                LedgerEntry::create([
                    'account_id' => $salesAccount->id,
                    'party_id' => null,
                    'warehouse_id' => $sale->warehouse_id,
                    'debit' => 0,
                    'credit' => $sale->total_amount,
                    'date' => $sale->date,
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'note' => $sale->note ?? "POS Sale to {$sale->party->name}",
                ]);

                // 2. COGS Entry: Debit COGS, Credit Inventory
                if ($totalCogs > 0) {
                    LedgerEntry::create([
                        'account_id' => $cogsAccount->id,
                        'party_id' => null,
                        'warehouse_id' => $sale->warehouse_id,
                        'debit' => $totalCogs,
                        'credit' => 0,
                        'date' => $sale->date,
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'note' => "COGS for POS Sale #{$sale->id}",
                    ]);

                    LedgerEntry::create([
                        'account_id' => $inventoryAccount->id,
                        'party_id' => null,
                        'warehouse_id' => $sale->warehouse_id,
                        'debit' => 0,
                        'credit' => $totalCogs,
                        'date' => $sale->date,
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'note' => "COGS for POS Sale #{$sale->id}",
                    ]);
                }

                // 3. Payments
                foreach ($payments as $payment) {
                    $amount = round($payment['amount'], 2);
                    if ($amount <= 0) {
                        continue;
                    }

                    LedgerEntry::create([
                        'account_id' => $payment['account_id'],
                        'party_id' => null,
                        'warehouse_id' => $sale->warehouse_id,
                        'debit' => $amount,
                        'credit' => 0,
                        'date' => $sale->date,
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'note' => "Payment received at POS Sale #{$sale->id}",
                    ]);

                    LedgerEntry::create([
                        'account_id' => $accountsReceivable->id,
                        'party_id' => $sale->party_id,
                        'warehouse_id' => $sale->warehouse_id,
                        'debit' => 0,
                        'credit' => $amount,
                        'date' => $sale->date,
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'note' => "Payment received at POS Sale #{$sale->id}",
                    ]);

                    PaymentSplit::create([
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'account_id' => $payment['account_id'],
                        'amount' => $amount,
                    ]);
                }

                return $sale;
            });

            $this->logActivity('Created POS Sale', 'Sales', $sale->id, null, $sale->toArray());

            // Generate WhatsApp URL if customer has phone
            $whatsappUrl = '';
            if ($sale->party && !empty($sale->party->phone)) {
                $cleanPhone = preg_replace('/[^0-9]/', '', $sale->party->phone);
                if (str_starts_with($cleanPhone, '0')) {
                    $cleanPhone = '92' . substr($cleanPhone, 1);
                }
                $signedUrl = URL::signedRoute('sales.public-pdf', ['sale' => $sale->id]);
                $msg = "Dear {$sale->party->name}, your invoice #{$sale->id} for amount " . ($settings->currency_symbol ?? 'Rs') . " " . number_format($sale->total_amount, 2) . " has been created.\nInvoice: {$signedUrl}";
                $whatsappUrl = "https://wa.me/{$cleanPhone}?text=" . urlencode($msg);
            }

            return response()->json([
                'success' => true,
                'message' => "Sale #{$sale->id} saved successfully!",
                'sale' => [
                    'id' => $sale->id,
                    'party_id' => $sale->party_id,
                    'party_name' => $sale->party->name,
                    'total_amount' => (float)$sale->total_amount,
                    'paid_amount' => (float)$sale->paid_amount,
                    'discount_amount' => (float)$sale->discount_amount,
                    'balance_due' => (float)max(0, $sale->total_amount - $sale->paid_amount),
                    'date' => $sale->date,
                    'items_count' => count($request->items),
                ],
                'thermal_url' => route('sales.thermal', $sale->id),
                'pdf_url' => route('sales.pdf', $sale->id),
                'public_pdf_url' => URL::signedRoute('sales.public-pdf', ['sale' => $sale->id]),
                'whatsapp_url' => $whatsappUrl,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('POS Sale creation failed: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while processing the sale: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function quickAddCustomer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'opening_balance' => 'nullable|numeric',
            'balance_type' => 'nullable|in:debit,credit',
        ]);

        $party = Party::create([
            'name' => $validated['name'],
            'type' => 'customer',
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'opening_balance' => (float)($validated['opening_balance'] ?? 0),
            'balance_type' => $validated['balance_type'] ?? 'debit',
            'status' => true,
        ]);

        $this->logActivity('Created Party from POS', 'Parties', $party->id, null, $party->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully!',
            'customer' => [
                'id' => $party->id,
                'name' => $party->name,
                'phone' => $party->phone ?? '',
                'address' => $party->address ?? '',
                'current_balance' => (float)$party->getCurrentBalance(),
                'credit_limit' => (float)($party->credit_limit ?? 0),
                'credit_days' => (int)($party->credit_days ?? 0),
                'has_overdue' => false,
            ],
        ]);
    }
}
