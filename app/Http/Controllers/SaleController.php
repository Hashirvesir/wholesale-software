<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Party;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Stock;
use App\Models\ChartOfAccount;
use App\Models\LedgerEntry;
use App\Models\PaymentSplit;
use App\Traits\TracksActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SaleController extends Controller implements HasMiddleware
{
    use TracksActivity;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view sales', only: ['index', 'show']),
            new Middleware('permission:create sales', only: ['create', 'store', 'quickAdd']),
            new Middleware('permission:edit sales', only: ['edit', 'update']),
            new Middleware('permission:delete sales', only: ['destroy']),
            new Middleware('permission:return sales', only: ['returnInvoice']),
        ];
    }

    public function index(Request $request)
    {
        $query = Sale::with(['party', 'warehouse', 'items']);

        // Search by Customer Name or Sale ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('party', function($qp) use ($search) {
                      $qp->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Date Filter
        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        // Warehouse Filter
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $sales = $query->latest()->paginate(10)->withQueryString();
        $warehouses = Warehouse::all();

        return view('sales.index', compact('sales', 'warehouses'));
    }

    public function create()
    {
        $customers = Party::where('type', 'customer')->get()->map(function($customer) {
            $customer->current_balance = $customer->getCurrentBalance();
            return $customer;
        });
        $warehouses = Warehouse::all();
        $products = Product::with('stocks')->get()->map(function($product) {
            $product->current_stock = $product->getCurrentStock();
            $product->warehouse_stocks = $product->stocks->pluck('qty', 'warehouse_id');
            $product->warehouse_costs = $product->stocks->pluck('avg_cost', 'warehouse_id');
            return $product;
        });
        
        $paymentAccounts = ChartOfAccount::where('type', 'Asset')
            ->whereNotIn('name', ['Inventory', 'Accounts Receivable'])
            ->where('status', true)
            ->get();

        $settings = \App\Models\Setting::first();

        return view('sales.create', compact('customers', 'warehouses', 'products', 'paymentAccounts', 'settings'));
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
        ]);

        // Custom validation for unique Product + Rate combinations
        $combinations = [];
        foreach ($request->items as $item) {
            $key = $item['product_id'] . '-' . $item['price'];
            if (in_array($key, $combinations)) {
                return back()->withInput()->withErrors(['error' => 'The same product cannot be added twice with the same price/rate.']);
            }
            $combinations[] = $key;
        }

        // Any payment row with an amount must have an account selected; empty rows (no amount) are ignored
        foreach ($request->payments ?? [] as $payment) {
            if ((float) ($payment['amount'] ?? 0) > 0 && empty($payment['account_id'])) {
                return back()->withInput()->withErrors(['error' => 'Please select an account for every payment amount entered.']);
            }
        }

        // Walk-In Customer: the sale must always be paid in full — no credit/balance due allowed.
        // (Warehouse just defaults to the Shop in the UI; the user can still change it, so it's
        // not forced here.) Enforced server-side too (not just in the form's JS) so it can't be
        // bypassed.
        $settings = \App\Models\Setting::first();
        if ($settings && $settings->walk_in_customer_id && $request->party_id == $settings->walk_in_customer_id) {
            $subtotal = collect($request->items)->sum(fn ($item) => round($item['qty'] * $item['price'], 2));
            $total = max(0, $subtotal - ($request->discount_amount ?? 0));
            $totalPaid = collect($request->payments ?? [])->sum(fn ($payment) => (float) ($payment['amount'] ?? 0));

            if ($totalPaid + 0.01 < $total) {
                return back()->withInput()->withErrors(['error' => 'Walk-In Customer sales must be paid in full — no credit allowed.']);
            }
        }

        $saleTxn = function () use ($request) {
            $payments = collect($request->payments ?? [])
                ->filter(fn ($payment) => (float) ($payment['amount'] ?? 0) > 0)
                ->values()
                ->all();
            // Calculate gross total
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += round($item['qty'] * $item['price'], 2);
            }

            $discount = $request->discount_amount ?? 0;
            $total = max(0, $subtotal - $discount);

            // Create Sale
            $party = Party::findOrFail($request->party_id);
            $currentBalance = $party->getCurrentBalance();
            $totalPaid = collect($payments)->sum('amount');

            // If customer has advance (credit balance), automatically use it
            $advanceUsed = 0;
            if ($currentBalance < 0) {
                $advanceAvailable = abs($currentBalance);
                $advanceUsed = min($advanceAvailable, $total);
            }

            // Total paid for the INVOICE is cash paid + advance used
            // But it shouldn't exceed the total amount of the sale
            $invoicePaidAmount = min($total, $totalPaid + $advanceUsed);

            $sale = Sale::create([
                'party_id' => $request->party_id,
                'warehouse_id' => $request->warehouse_id,
                'account_id' => $payments[0]['account_id'] ?? null,
                'date' => $request->date,
                'total_amount' => $total,
                'discount_amount' => $discount,
                'paid_amount' => $invoicePaidAmount,
                'note' => $request->note . ($advanceUsed > 0 ? " (Covered by Advance: " . $party->currency . " " . number_format($advanceUsed, 2) . ")" : ""),
            ]);

            // Create Sale Items and Reduce Stock
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

                // Reduce Stock Quantity (avg_cost remains unchanged)
                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $request->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if ($stock) {
                    $totalCogs += round($item['qty'] * $stock->avg_cost, 2);
                    $stock->qty -= $item['qty'];
                    $stock->save();
                } else {
                    // If no stock record exists, create one with negative qty (backorder scenario)
                    Stock::create([
                        'product_id' => $item['product_id'],
                        'warehouse_id' => $request->warehouse_id,
                        'qty' => -$item['qty'],
                        'avg_cost' => 0,
                    ]);
                }
            }

            // --- DOUBLE ENTRY ACCOUNTING ---
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
                'note' => $sale->note ?? "Sale to {$sale->party->name}",
            ]);

            LedgerEntry::create([
                'account_id' => $salesAccount->id,
                'party_id' => null, // Internal account, no party association for ledger balance
                'warehouse_id' => $sale->warehouse_id,
                'debit' => 0,
                'credit' => $sale->total_amount,
                'date' => $sale->date,
                'reference_type' => 'sale',
                'reference_id' => $sale->id,
                'note' => $sale->note ?? "Sale to {$sale->party->name}",
            ]);

            // 2. COGS Entry: Debit COGS, Credit Inventory
            if ($totalCogs > 0) {
                LedgerEntry::create([
                    'account_id' => $cogsAccount->id,
                    'party_id' => null, // Internal account, no party association for ledger balance
                    'warehouse_id' => $sale->warehouse_id,
                    'debit' => $totalCogs,
                    'credit' => 0,
                    'date' => $sale->date,
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'note' => "COGS for Sale #{$sale->id}",
                ]);

                LedgerEntry::create([
                    'account_id' => $inventoryAccount->id,
                    'party_id' => null, // Internal account, no party association for ledger balance
                    'warehouse_id' => $sale->warehouse_id,
                    'debit' => 0,
                    'credit' => $totalCogs,
                    'date' => $sale->date,
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'note' => "COGS for Sale #{$sale->id}",
                ]);
            }

            // 3. Payment Entry (if CASH paid at time of sale), one debit/credit pair per split account
            // Note: Advance used is NOT recorded as a new payment ledger entry
            // because the Sale Debit already offsets the Credit balance.
            foreach ($payments as $payment) {
                $amount = round($payment['amount'], 2);
                if ($amount <= 0) {
                    continue;
                }

                // Debit Selected Account (Cash/Bank)
                LedgerEntry::create([
                    'account_id' => $payment['account_id'],
                    'party_id' => null,
                    'warehouse_id' => $sale->warehouse_id,
                    'debit' => $amount,
                    'credit' => 0,
                    'date' => $sale->date,
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'note' => "Payment received at time of Sale #{$sale->id}",
                ]);

                // Credit Customer (Accounts Receivable)
                LedgerEntry::create([
                    'account_id' => $accountsReceivable->id,
                    'party_id' => $sale->party_id,
                    'warehouse_id' => $sale->warehouse_id,
                    'debit' => 0,
                    'credit' => $amount,
                    'date' => $sale->date,
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'note' => "Payment received at time of Sale #{$sale->id}",
                ]);

                PaymentSplit::create([
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'account_id' => $payment['account_id'],
                    'amount' => $amount,
                ]);
            }

            return $sale;
        };

        try {
            $sale = DB::transaction($saleTxn);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Sale creation failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withInput()->withErrors(['error' => 'Something went wrong while saving the sale. No changes were made — please try again.']);
        }

        $this->logActivity('Created Sale', 'Sales', $sale->id, null, $sale->toArray());

        return redirect()->route('sales.show', $sale)->with('success', 'Sale created successfully!');
    }

    public function show(Sale $sale)
    {
        $sale->load(['party', 'warehouse', 'items.product', 'paymentSplits.account']);
        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        $customers = Party::where('type', 'customer')->get()->map(function($customer) use ($sale) {
            $balance = $customer->getCurrentBalance();
            if ($customer->id == $sale->party_id) {
                // Subtract current sale's impact to show actual "Previous" balance
                $saleImpact = $sale->total_amount - $sale->paid_amount;
                $customer->current_balance = $balance - $saleImpact;
            } else {
                $customer->current_balance = $balance;
            }
            return $customer;
        });
        $warehouses = Warehouse::all();
        $products = Product::with('stocks')->get()->map(function($product) {
            $product->current_stock = $product->getCurrentStock();
            $product->warehouse_stocks = $product->stocks->pluck('qty', 'warehouse_id');
            $product->warehouse_costs = $product->stocks->pluck('avg_cost', 'warehouse_id');
            return $product;
        });
        $sale->load(['items.product']);

        $mappedItems = $sale->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'search' => $item->product->name . ' (' . $item->product->sku . ')',
                'open' => false,
                'qty' => (float)$item->qty,
                'price' => (float)$item->price,
                'error' => false,
                'highlightedIndex' => -1,
            ];
        });
        
        $paymentAccounts = ChartOfAccount::where('type', 'Asset')
            ->whereNotIn('name', ['Inventory', 'Accounts Receivable'])
            ->where('status', true)
            ->get();

        $existingSplits = $sale->paymentSplits;
        if ($existingSplits->isEmpty() && $sale->paid_amount > 0 && $sale->account_id) {
            // Legacy record paid before split payments existed - seed one row from the old fields
            $mappedPayments = [['account_id' => $sale->account_id, 'amount' => (float) $sale->paid_amount]];
        } else {
            $mappedPayments = $existingSplits->map(fn ($split) => [
                'account_id' => $split->account_id,
                'amount' => (float) $split->amount,
            ])->values()->all();
        }

        $settings = \App\Models\Setting::first();

        return view('sales.edit', compact('sale', 'customers', 'warehouses', 'products', 'mappedItems', 'paymentAccounts', 'mappedPayments', 'settings'));
    }

    public function update(Request $request, Sale $sale)
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
        ]);

        // Walk-In Customer: same full-payment-only rule as store() (warehouse stays changeable).
        $settings = \App\Models\Setting::first();
        if ($settings && $settings->walk_in_customer_id && $request->party_id == $settings->walk_in_customer_id) {
            $subtotal = collect($request->items)->sum(fn ($item) => round($item['qty'] * $item['price'], 2));
            $total = max(0, $subtotal - ($request->discount_amount ?? 0));
            $totalPaid = collect($request->payments ?? [])->sum(fn ($payment) => (float) ($payment['amount'] ?? 0));

            if ($totalPaid + 0.01 < $total) {
                return back()->withInput()->withErrors(['error' => 'Walk-In Customer sales must be paid in full — no credit allowed.']);
            }
        }

        // Custom validation for unique Product + Rate combinations
        $combinations = [];
        foreach ($request->items as $item) {
            $key = $item['product_id'] . '-' . $item['price'];
            if (in_array($key, $combinations)) {
                return back()->withInput()->withErrors(['error' => 'The same product cannot be added twice with the same price/rate.']);
            }
            $combinations[] = $key;
        }

        // Any payment row with an amount must have an account selected; empty rows (no amount) are ignored
        foreach ($request->payments ?? [] as $payment) {
            if ((float) ($payment['amount'] ?? 0) > 0 && empty($payment['account_id'])) {
                return back()->withInput()->withErrors(['error' => 'Please select an account for every payment amount entered.']);
            }
        }

        $oldData = $sale->load('items')->toArray();

        $updateTxn = function () use ($request, $sale) {
            $payments = collect($request->payments ?? [])
                ->filter(fn ($payment) => (float) ($payment['amount'] ?? 0) > 0)
                ->values()
                ->all();
            // Reverse old stock quantities
            foreach ($sale->items as $oldItem) {
                $stock = Stock::where('product_id', $oldItem->product_id)
                    ->where('warehouse_id', $sale->warehouse_id)
                    ->lockForUpdate()
                    ->first();
                
                if ($stock) {
                    $stock->qty += $oldItem->qty; // Add back the old quantity
                    $stock->save();
                }
            }

            // Delete old items, ledger entries and payment splits
            $sale->items()->delete();
            LedgerEntry::where('reference_type', 'sale')->where('reference_id', $sale->id)->delete();
            PaymentSplit::where('reference_type', 'sale')->where('reference_id', $sale->id)->delete();

            // Calculate new total
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += round($item['qty'] * $item['price'], 2);
            }

            $discount = $request->discount_amount ?? 0;
            $total = max(0, $subtotal - $discount);

            // Update Sale
            $sale->update([
                'party_id' => $request->party_id,
                'warehouse_id' => $request->warehouse_id,
                'account_id' => $payments[0]['account_id'] ?? null,
                'date' => $request->date,
                'total_amount' => $total,
                'discount_amount' => $discount,
                'paid_amount' => collect($payments)->sum('amount'),
                'note' => $request->note,
            ]);

            // Create new items and reduce stock
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

                // Reduce Stock
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

            // --- DOUBLE ENTRY ACCOUNTING ---
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
                'note' => $sale->note ?? "Updated Sale to {$sale->party->name}",
            ]);

            LedgerEntry::create([
                'account_id' => $salesAccount->id,
                'party_id' => null, // Internal account, no party association for ledger balance
                'warehouse_id' => $sale->warehouse_id,
                'debit' => 0,
                'credit' => $sale->total_amount,
                'date' => $sale->date,
                'reference_type' => 'sale',
                'reference_id' => $sale->id,
                'note' => $sale->note ?? "Updated Sale to {$sale->party->name}",
            ]);

            // 2. COGS Entry: Debit COGS, Credit Inventory
            if ($totalCogs > 0) {
                LedgerEntry::create([
                    'account_id' => $cogsAccount->id,
                    'party_id' => null, // Internal account, no party association for ledger balance
                    'warehouse_id' => $sale->warehouse_id,
                    'debit' => $totalCogs,
                    'credit' => 0,
                    'date' => $sale->date,
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'note' => "Updated COGS for Sale #{$sale->id}",
                ]);

                LedgerEntry::create([
                    'account_id' => $inventoryAccount->id,
                    'party_id' => null, // Internal account, no party association for ledger balance
                    'warehouse_id' => $sale->warehouse_id,
                    'debit' => 0,
                    'credit' => $totalCogs,
                    'date' => $sale->date,
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'note' => "Updated COGS for Sale #{$sale->id}",
                ]);
            }

            // 3. Payment Entry (if paid at time of sale), one debit/credit pair per split account
            foreach ($payments as $payment) {
                $amount = round($payment['amount'], 2);
                if ($amount <= 0) {
                    continue;
                }

                // Debit Selected Account (Cash/Bank)
                LedgerEntry::create([
                    'account_id' => $payment['account_id'],
                    'party_id' => null,
                    'warehouse_id' => $sale->warehouse_id,
                    'debit' => $amount,
                    'credit' => 0,
                    'date' => $sale->date,
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'note' => "Updated Payment received at time of Sale #{$sale->id}",
                ]);

                // Credit Customer (Accounts Receivable)
                LedgerEntry::create([
                    'account_id' => $accountsReceivable->id,
                    'party_id' => $sale->party_id,
                    'warehouse_id' => $sale->warehouse_id,
                    'debit' => 0,
                    'credit' => $amount,
                    'date' => $sale->date,
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'note' => "Updated Payment received at time of Sale #{$sale->id}",
                ]);

                PaymentSplit::create([
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'account_id' => $payment['account_id'],
                    'amount' => $amount,
                ]);
            }
        };

        try {
            DB::transaction($updateTxn);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Sale update failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withInput()->withErrors(['error' => 'Something went wrong while updating the sale. No changes were made — please try again.']);
        }

        $this->logActivity('Updated Sale', 'Sales', $sale->id, $oldData, $sale->load('items')->toArray());

        return redirect()->route('sales.show', $sale)->with('success', 'Sale updated successfully!');
    }

    public function destroy(Sale $sale)
    {
        DB::transaction(function () use ($sale) {
            // Reverse stock quantities
            foreach ($sale->items as $item) {
                $stock = Stock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $sale->warehouse_id)
                    ->first();
                
                if ($stock) {
                    $stock->qty += $item->qty;
                    $stock->save();
                }
            }

            // Delete ledger entries and payment splits
            LedgerEntry::where('reference_type', 'sale')->where('reference_id', $sale->id)->delete();
            PaymentSplit::where('reference_type', 'sale')->where('reference_id', $sale->id)->delete();

            $sale->delete();
        });

        $this->logActivity('Deleted Sale', 'Sales', $sale->id, $sale->toArray(), null);

        return redirect()->route('sales.index')->with('success', 'Sale deleted successfully!');
    }
    public function downloadPdf(Sale $sale)
    {
        $sale->load(['party', 'warehouse', 'items.product.unit', 'paymentSplits.account']);
        $settings = \App\Models\Setting::first();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('sales.pdf', compact('sale', 'settings'));
        return $pdf->stream('Invoice-' . $sale->id . '.pdf');
    }

    // Same as downloadPdf(), but reachable without login via a signed URL (see routes/web.php)
    // so it can be shared directly with a customer, e.g. over WhatsApp.
    public function publicPdf(Sale $sale)
    {
        return $this->downloadPdf($sale);
    }

    public function thermalPrint(Sale $sale)
    {
        $sale->load(['party', 'warehouse', 'items.product.unit', 'paymentSplits.account']);
        $settings = \App\Models\Setting::first();
        $currentBalance = $sale->party->getCurrentBalance();

        return view('sales.thermal', compact('sale', 'settings', 'currentBalance'));
    }
}
