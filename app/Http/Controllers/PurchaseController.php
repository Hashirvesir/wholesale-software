<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Party;
use App\Models\Warehouse;
use App\Models\Stock;
use App\Models\ChartOfAccount;
use App\Models\LedgerEntry;
use App\Models\PaymentSplit;
use App\Models\CargoCharge;
use App\Traits\TracksActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PurchaseController extends Controller implements HasMiddleware
{
    use TracksActivity;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view purchases', only: ['index', 'show']),
            new Middleware('permission:create purchases', only: ['create', 'store', 'quickAdd']),
            new Middleware('permission:edit purchases', only: ['edit', 'update']),
            new Middleware('permission:delete purchases', only: ['destroy']),
            new Middleware('permission:return purchases', only: ['returnInvoice']),
        ];
    }

    public function index(Request $request)
    {
        $query = Purchase::with(['party', 'warehouse']);

        // Search by Vendor Name or Purchase ID
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

        $purchases = $query->latest()->paginate(10)->withQueryString();
        $warehouses = Warehouse::where('status', true)->get();

        return view('purchases.index', compact('purchases', 'warehouses'));
    }

    public function create()
    {
        $vendors = Party::where('type', 'vendor')->where('status', true)->get()->map(function($v) {
            $v->current_balance = $v->getCurrentBalance();
            return $v;
        });
        $cargoParties = Party::where('type', 'cargo')->where('status', true)->get();
        $warehouses = Warehouse::where('status', true)->get();
        $products = Product::where('status', true)->get()->map(function($p) {
            $p->current_stock = $p->getCurrentStock();
            $p->warehouse_stocks = $p->stocks->pluck('qty', 'warehouse_id');
            return $p;
        });
        $paymentAccounts = ChartOfAccount::where('type', 'Asset')
            ->whereNotIn('name', ['Inventory', 'Accounts Receivable'])
            ->where('status', true)
            ->get();
        $settings = \App\Models\Setting::first();
        return view('purchases.create', compact('vendors', 'warehouses', 'products', 'cargoParties', 'paymentAccounts', 'settings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'party_id' => 'required|exists:parties,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'date' => 'required|date',
            'currency' => 'required|in:PKR,RMB',
            'exchange_rate' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.price_rmb' => 'required_if:currency,RMB|numeric|min:0',
            'items.*.price_pkr' => 'required|numeric|min:0.01',
            'payments' => 'nullable|array',
            'payments.*.account_id' => 'nullable|exists:chart_of_accounts,id',
            'payments.*.amount' => 'nullable|numeric|min:0',
            // Cargo Fields
            'cargo_party_id' => 'nullable|exists:parties,id',
            'total_weight' => 'nullable|numeric|min:0',
            'rate_per_kg' => 'nullable|numeric|min:0',
            'items.*.cargo_amount' => 'nullable|numeric|min:0',
        ]);

        // Require cargo party when per-item cargo is entered
        $totalItemCargoPre = collect($request->items)->sum(fn($i) => (float)($i['cargo_amount'] ?? 0));
        if ($totalItemCargoPre > 0 && !$request->filled('cargo_party_id')) {
            return back()->withInput()->withErrors(['cargo_party_id' => 'Cargo Party is required when per-item cargo amounts are entered.']);
        }

        // Custom validation for unique Product + Rate combinations
        $combinations = [];
        foreach ($request->items as $item) {
            $key = $item['product_id'] . '-' . $item['price_pkr'];
            if (in_array($key, $combinations)) {
                return back()->withInput()->withErrors(['error' => 'The same product cannot be added twice with the same price.']);
            }
            $combinations[] = $key;
        }

        // Any payment row with an amount must have an account selected; empty rows (no amount) are ignored
        foreach ($request->payments ?? [] as $payment) {
            if ((float) ($payment['amount'] ?? 0) > 0 && empty($payment['account_id'])) {
                return back()->withInput()->withErrors(['error' => 'Please select an account for every payment amount entered.']);
            }
        }

        $purchaseTxn = function () use ($request) {
            $payments = collect($request->payments ?? [])
                ->filter(fn ($payment) => (float) ($payment['amount'] ?? 0) > 0)
                ->values()
                ->all();
            $totalPaid = round(collect($payments)->sum('amount'), 2);

            $purchase = Purchase::create([
                'party_id' => $request->party_id,
                'warehouse_id' => $request->warehouse_id,
                'account_id' => $payments[0]['account_id'] ?? null,
                'date' => $request->date,
                'currency' => $request->currency,
                'exchange_rate' => $request->exchange_rate,
                'note' => $request->note,
                'total_amount' => 0,
            ]);

            // In transport mode cargo is accounted via CargoCharge; item total = goods only
            $isTransportMode = (float)($request->total_weight ?? 0) > 0 && (float)($request->rate_per_kg ?? 0) > 0;

            $totalPurchaseAmount = 0;
            $totalItemCargo = 0;

            foreach ($request->items as $item) {
                $cargoAmount = round($item['cargo_amount'] ?? 0, 2);
                $itemTotal = $isTransportMode
                    ? round($item['qty'] * $item['price_pkr'], 2)
                    : round($item['qty'] * $item['price_pkr'] + $cargoAmount, 2);
                $totalPurchaseAmount += $itemTotal;
                $totalItemCargo += $cargoAmount;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price_rmb' => $request->currency == 'RMB' ? $item['price_rmb'] : 0,
                    'price_pkr' => $item['price_pkr'],
                    'total' => $itemTotal,
                    'cargo_amount' => $cargoAmount,
                ]);

                // Update Stock
                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $request->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if (!$stock) {
                    $stock = new Stock([
                        'product_id' => $item['product_id'],
                        'warehouse_id' => $request->warehouse_id
                    ]);
                }

                $currentQty = $stock->qty ?? 0;
                $currentAvgCost = $stock->avg_cost ?? 0;
                $addedQty = $item['qty'];
                $addedRate = $item['price_pkr'];

                $newQty = $currentQty + $addedQty;
                if ($newQty > 0) {
                    $newAvgCost = (($currentQty * $currentAvgCost) + ($addedQty * $addedRate) + $cargoAmount) / $newQty;
                } else {
                    $newAvgCost = $addedRate + ($cargoAmount / max(1, $addedQty));
                }

                $stock->qty = $newQty;
                $stock->avg_cost = round($newAvgCost, 2);
                $stock->save();
            }

            $purchase->update([
                'total_amount' => max(0, $totalPurchaseAmount - ($request->discount_amount ?? 0)),
                'discount_amount' => $request->discount_amount ?? 0,
                'paid_amount' => $totalPaid,
            ]);

            $cargoExpenseAcc = ChartOfAccount::where('name', 'Cargo Expense')->first();
            $payableAcc     = ChartOfAccount::where('name', 'Accounts Payable')->first();

            // Per-item cargo assigned to a cargo party (weight/rate disabled in UI)
            $itemCargoToParty = $request->cargo_party_id && $totalItemCargo > 0
                && !($request->total_weight > 0 && $request->rate_per_kg > 0);

            if ($itemCargoToParty) {
                CargoCharge::create([
                    'purchase_id'    => $purchase->id,
                    'cargo_party_id' => $request->cargo_party_id,
                    'total_weight'   => 0,
                    'rate_per_kg'    => 0,
                    'total_amount'   => $totalItemCargo,
                ]);
                // No separate "Cargo Expense" debit here: this cargo amount is already part of
                // total_amount (and therefore already debited to Inventory below) since it's
                // capitalized into the item's landed cost / avg_cost. Only the payable to the
                // cargo party needs recording — the Inventory debit is what balances it.
                LedgerEntry::create([
                    'account_id'     => $payableAcc->id,
                    'party_id'       => $request->cargo_party_id,
                    'warehouse_id'   => $purchase->warehouse_id,
                    'debit'          => 0,
                    'credit'         => $totalItemCargo,
                    'date'           => $purchase->date,
                    'reference_type' => 'cargo_charge',
                    'reference_id'   => $purchase->id,
                    'note'           => "Item Cargo for Purchase #{$purchase->id}",
                ]);
            }

            // Transport cargo (weight × rate)
            if ($request->cargo_party_id && $request->total_weight > 0 && $request->rate_per_kg > 0) {
                $cargoTotal = round($request->total_weight * $request->rate_per_kg, 2);
                CargoCharge::create([
                    'purchase_id'    => $purchase->id,
                    'cargo_party_id' => $request->cargo_party_id,
                    'total_weight'   => $request->total_weight,
                    'rate_per_kg'    => $request->rate_per_kg,
                    'total_amount'   => $cargoTotal,
                ]);
                LedgerEntry::create([
                    'account_id'     => $cargoExpenseAcc->id,
                    'warehouse_id'   => $purchase->warehouse_id,
                    'debit'          => $cargoTotal,
                    'credit'         => 0,
                    'date'           => $purchase->date,
                    'reference_type' => 'cargo_charge',
                    'reference_id'   => $purchase->id,
                    'note'           => "Transport Cargo for Purchase #{$purchase->id}",
                ]);
                LedgerEntry::create([
                    'account_id'     => $payableAcc->id,
                    'party_id'       => $request->cargo_party_id,
                    'warehouse_id'   => $purchase->warehouse_id,
                    'debit'          => 0,
                    'credit'         => $cargoTotal,
                    'date'           => $purchase->date,
                    'reference_type' => 'cargo_charge',
                    'reference_id'   => $purchase->id,
                    'note'           => "Transport Cargo for Purchase #{$purchase->id}",
                ]);
            }

            // Double Entry Accounting for Purchase
            $inventoryAcc = ChartOfAccount::where('name', 'Inventory')->first();
            $purchaseNote = ($purchase->note ?? "Purchase from {$purchase->party->name}") . ($purchase->discount_amount > 0 ? " (Net after Discount: " . number_format($purchase->discount_amount, 2) . ")" : "");

            // Debit Inventory (goods + per-item cargo = total_amount)
            LedgerEntry::create([
                'account_id'     => $inventoryAcc->id,
                'party_id'       => null,
                'warehouse_id'   => $purchase->warehouse_id,
                'debit'          => $purchase->total_amount,
                'credit'         => 0,
                'date'           => $purchase->date,
                'reference_type' => 'purchase',
                'reference_id'   => $purchase->id,
                'note'           => $purchaseNote,
            ]);

            // Credit Vendor (goods only when cargo party takes item cargo)
            $vendorCredit = $purchase->total_amount - ($itemCargoToParty ? $totalItemCargo : 0);
            LedgerEntry::create([
                'account_id'     => $payableAcc->id,
                'party_id'       => $purchase->party_id,
                'warehouse_id'   => $purchase->warehouse_id,
                'debit'          => 0,
                'credit'         => $vendorCredit,
                'date'           => $purchase->date,
                'reference_type' => 'purchase',
                'reference_id'   => $purchase->id,
                'note'           => $purchaseNote,
            ]);

            // Payment Entry (if paid at time of purchase)
            if ($totalPaid > 0) {
                // How much cargo party is owed (transport mode or manual item cargo)
                $cargoPartyOwed = $isTransportMode
                    ? round($request->total_weight * $request->rate_per_kg, 2)
                    : ($itemCargoToParty ? $totalItemCargo : 0);

                $remaining = $totalPaid;

                // Pay vendor first (their portion of the payable)
                $vendorPayment = min($remaining, $vendorCredit);
                if ($vendorPayment > 0) {
                    LedgerEntry::create([
                        'account_id' => $payableAcc->id,
                        'party_id'   => $purchase->party_id,
                        'warehouse_id' => $purchase->warehouse_id,
                        'debit'      => $vendorPayment,
                        'credit'     => 0,
                        'date'       => $purchase->date,
                        'reference_type' => 'purchase',
                        'reference_id'   => $purchase->id,
                        'note'       => "Payment made at time of Purchase #{$purchase->id}",
                    ]);
                    $remaining -= $vendorPayment;
                }

                // Pay cargo party with remaining amount (if any)
                if ($remaining > 0 && $cargoPartyOwed > 0 && $request->cargo_party_id) {
                    $cargoPayment = min($remaining, $cargoPartyOwed);
                    LedgerEntry::create([
                        'account_id' => $payableAcc->id,
                        'party_id'   => $request->cargo_party_id,
                        'warehouse_id' => $purchase->warehouse_id,
                        'debit'      => $cargoPayment,
                        'credit'     => 0,
                        'date'       => $purchase->date,
                        'reference_type' => 'purchase',
                        'reference_id'   => $purchase->id,
                        'note'       => "Cargo payment at time of Purchase #{$purchase->id}",
                    ]);
                }

                // Credit Cash/Bank, one entry per payment split account
                foreach ($payments as $payment) {
                    $amount = round($payment['amount'], 2);
                    if ($amount <= 0) {
                        continue;
                    }

                    LedgerEntry::create([
                        'account_id' => $payment['account_id'],
                        'party_id'   => null,
                        'warehouse_id' => $purchase->warehouse_id,
                        'debit'      => 0,
                        'credit'     => $amount,
                        'date'       => $purchase->date,
                        'reference_type' => 'purchase',
                        'reference_id'   => $purchase->id,
                        'note'       => "Payment from Cash/Bank at time of Purchase #{$purchase->id}",
                    ]);

                    PaymentSplit::create([
                        'reference_type' => 'purchase',
                        'reference_id' => $purchase->id,
                        'account_id' => $payment['account_id'],
                        'amount' => $amount,
                    ]);
                }
            }

            return $purchase;
        };

        try {
            $purchase = DB::transaction($purchaseTxn);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Purchase creation failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withInput()->withErrors(['error' => 'Something went wrong while saving the purchase. No changes were made — please try again.']);
        }

        $this->logActivity('Recorded Purchase', 'Purchases', $purchase->id, null, $purchase->load('items')->toArray());

        return redirect()->route('purchases.show', $purchase)->with('success', 'Purchase recorded successfully and stock updated.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['party', 'warehouse', 'items.product', 'cargoCharge.cargoParty', 'paymentSplits.account']);
        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $purchase->load(['party', 'items.product', 'cargoCharge']);
        $vendors = Party::where('type', 'vendor')->where('status', true)->get()->map(function($v) {
            $v->current_balance = $v->getCurrentBalance();
            return $v;
        });
        $cargoParties = Party::where('type', 'cargo')->where('status', true)->get();
        $warehouses = Warehouse::where('status', true)->get();
        $products = Product::where('status', true)->get()->map(function($p) {
            $p->current_stock = $p->getCurrentStock();
            $p->warehouse_stocks = $p->stocks->pluck('qty', 'warehouse_id');
            return $p;
        });
        
        $mappedItems = $purchase->items->map(function($item) {
            return [
                'product_id' => $item->product_id,
                'search' => $item->product->name . ' (' . $item->product->sku . ')',
                'open' => false,
                'qty' => (float)$item->qty,
                'price_rmb' => (float)$item->price_rmb,
                'price_pkr' => (float)$item->price_pkr,
                'cargo_amount' => (float)($item->cargo_amount ?? 0),
                'error' => false,
                'highlightedIndex' => -1
            ];
        });

        $paymentAccounts = ChartOfAccount::where('type', 'Asset')
            ->whereNotIn('name', ['Inventory', 'Accounts Receivable'])
            ->where('status', true)
            ->get();

        $existingSplits = $purchase->paymentSplits;
        if ($existingSplits->isEmpty() && $purchase->paid_amount > 0 && $purchase->account_id) {
            // Legacy record paid before split payments existed - seed one row from the old fields
            $mappedPayments = [['account_id' => $purchase->account_id, 'amount' => (float) $purchase->paid_amount]];
        } else {
            $mappedPayments = $existingSplits->map(fn ($split) => [
                'account_id' => $split->account_id,
                'amount' => (float) $split->amount,
            ])->values()->all();
        }

        return view('purchases.edit', compact('purchase', 'vendors', 'warehouses', 'products', 'mappedItems', 'cargoParties', 'paymentAccounts', 'mappedPayments'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $request->validate([
            'party_id' => 'required|exists:parties,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'date' => 'required|date',
            'currency' => 'required|in:PKR,RMB',
            'exchange_rate' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.price_rmb' => 'required_if:currency,RMB|numeric|min:0',
            'items.*.price_pkr' => 'required|numeric|min:0.01',
            'payments' => 'nullable|array',
            'payments.*.account_id' => 'nullable|exists:chart_of_accounts,id',
            'payments.*.amount' => 'nullable|numeric|min:0',
            // Cargo Fields
            'cargo_party_id' => 'nullable|exists:parties,id',
            'total_weight' => 'nullable|numeric|min:0',
            'rate_per_kg' => 'nullable|numeric|min:0',
            'items.*.cargo_amount' => 'nullable|numeric|min:0',
        ]);

        // Require cargo party when per-item cargo is entered
        $totalItemCargoPre = collect($request->items)->sum(fn($i) => (float)($i['cargo_amount'] ?? 0));
        if ($totalItemCargoPre > 0 && !$request->filled('cargo_party_id')) {
            return back()->withInput()->withErrors(['cargo_party_id' => 'Cargo Party is required when per-item cargo amounts are entered.']);
        }

        // Custom validation for unique Product + Rate combinations
        $combinations = [];
        foreach ($request->items as $item) {
            $key = $item['product_id'] . '-' . $item['price_pkr'];
            if (in_array($key, $combinations)) {
                return back()->withInput()->withErrors(['error' => 'The same product cannot be added twice with the same price.']);
            }
            $combinations[] = $key;
        }

        // Any payment row with an amount must have an account selected; empty rows (no amount) are ignored
        foreach ($request->payments ?? [] as $payment) {
            if ((float) ($payment['amount'] ?? 0) > 0 && empty($payment['account_id'])) {
                return back()->withInput()->withErrors(['error' => 'Please select an account for every payment amount entered.']);
            }
        }

        $oldData = $purchase->load(['items', 'cargoCharge'])->toArray();

        $updatePurchaseTxn = function () use ($request, $purchase) {
            $payments = collect($request->payments ?? [])
                ->filter(fn ($payment) => (float) ($payment['amount'] ?? 0) > 0)
                ->values()
                ->all();
            $totalPaid = round(collect($payments)->sum('amount'), 2);

            // 1. Reverse old stock and its impact on Weighted Average Cost
            foreach ($purchase->items as $oldItem) {
                $stock = Stock::where('product_id', $oldItem->product_id)
                              ->where('warehouse_id', $purchase->warehouse_id)
                              ->lockForUpdate()
                              ->first();
                if ($stock) {
                    $oldQty = $stock->qty;
                    $oldAvgCost = $stock->avg_cost;
                    $reversedQty = $oldItem->qty;
                    $reversedPrice = $oldItem->price_pkr;
                    $reversedCargo = $oldItem->cargo_amount ?? 0;

                    $newQty = $oldQty - $reversedQty;

                    if ($newQty > 0) {
                        $newAvgCost = (($oldQty * $oldAvgCost) - ($reversedQty * $reversedPrice) - $reversedCargo) / $newQty;
                        $stock->qty = $newQty;
                        $stock->avg_cost = max(0, $newAvgCost);
                    } else {
                        $stock->qty = 0;
                        $stock->avg_cost = 0;
                    }
                    $stock->save();
                }
            }

            // 2. Delete old items, ledger entries (Both Purchase and Cargo) and payment splits
            $purchase->items()->delete();
            $purchase->cargoCharge()->delete();
            LedgerEntry::whereIn('reference_type', ['purchase', 'cargo_charge'])->where('reference_id', $purchase->id)->delete();
            PaymentSplit::where('reference_type', 'purchase')->where('reference_id', $purchase->id)->delete();

            // 3. Update purchase header
            $purchase->update([
                'party_id' => $request->party_id,
                'warehouse_id' => $request->warehouse_id,
                'account_id' => $payments[0]['account_id'] ?? null,
                'date' => $request->date,
                'currency' => $request->currency,
                'exchange_rate' => $request->exchange_rate,
                'note' => $request->note,
            ]);

            // In transport mode cargo is accounted via CargoCharge; item total = goods only
            $isTransportMode = (float)($request->total_weight ?? 0) > 0 && (float)($request->rate_per_kg ?? 0) > 0;

            $totalPurchaseAmount = 0;
            $totalItemCargo = 0;

            // 4. Create new items and apply new stock
            foreach ($request->items as $item) {
                $cargoAmount = round($item['cargo_amount'] ?? 0, 2);
                $itemTotal = $isTransportMode
                    ? round($item['qty'] * $item['price_pkr'], 2)
                    : round($item['qty'] * $item['price_pkr'] + $cargoAmount, 2);
                $totalPurchaseAmount += $itemTotal;
                $totalItemCargo += $cargoAmount;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price_rmb' => $request->currency == 'RMB' ? $item['price_rmb'] : 0,
                    'price_pkr' => $item['price_pkr'],
                    'total' => $itemTotal,
                    'cargo_amount' => $cargoAmount,
                ]);

                // Update Stock
                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $request->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if (!$stock) {
                    $stock = new Stock([
                        'product_id' => $item['product_id'],
                        'warehouse_id' => $request->warehouse_id
                    ]);
                }

                $currentQty = $stock->qty ?? 0;
                $currentAvgCost = $stock->avg_cost ?? 0;
                $addedQty = $item['qty'];
                $addedRate = $item['price_pkr'];

                $newQty = $currentQty + $addedQty;
                if ($newQty > 0) {
                    $newAvgCost = (($currentQty * $currentAvgCost) + ($addedQty * $addedRate) + $cargoAmount) / $newQty;
                } else {
                    $newAvgCost = $addedRate + ($cargoAmount / max(1, $addedQty));
                }

                $stock->qty = $newQty;
                $stock->avg_cost = round($newAvgCost, 2);
                $stock->save();
            }

            $purchase->update([
                'party_id' => $request->party_id,
                'warehouse_id' => $request->warehouse_id,
                'date' => $request->date,
                'currency' => $request->currency,
                'exchange_rate' => $request->exchange_rate,
                'total_amount' => max(0, $totalPurchaseAmount - ($request->discount_amount ?? 0)),
                'discount_amount' => $request->discount_amount ?? 0,
                'paid_amount' => $totalPaid,
                'note' => $request->note,
            ]);

            $cargoExpenseAcc = ChartOfAccount::where('name', 'Cargo Expense')->first();
            $payableAcc     = ChartOfAccount::where('name', 'Accounts Payable')->first();

            // Per-item cargo assigned to cargo party (weight/rate disabled in UI)
            $itemCargoToParty = $request->cargo_party_id && $totalItemCargo > 0
                && !($request->total_weight > 0 && $request->rate_per_kg > 0);

            if ($itemCargoToParty) {
                CargoCharge::create([
                    'purchase_id'    => $purchase->id,
                    'cargo_party_id' => $request->cargo_party_id,
                    'total_weight'   => 0,
                    'rate_per_kg'    => 0,
                    'total_amount'   => $totalItemCargo,
                ]);
                // No separate "Cargo Expense" debit here: this cargo amount is already part of
                // total_amount (and therefore already debited to Inventory below) since it's
                // capitalized into the item's landed cost / avg_cost. Only the payable to the
                // cargo party needs recording — the Inventory debit is what balances it.
                LedgerEntry::create([
                    'account_id'     => $payableAcc->id,
                    'party_id'       => $request->cargo_party_id,
                    'warehouse_id'   => $purchase->warehouse_id,
                    'debit'          => 0,
                    'credit'         => $totalItemCargo,
                    'date'           => $purchase->date,
                    'reference_type' => 'cargo_charge',
                    'reference_id'   => $purchase->id,
                    'note'           => "Item Cargo for Purchase #{$purchase->id}",
                ]);
            }

            // Transport cargo (weight × rate)
            if ($request->cargo_party_id && $request->total_weight > 0 && $request->rate_per_kg > 0) {
                $cargoTotal = round($request->total_weight * $request->rate_per_kg, 2);
                CargoCharge::create([
                    'purchase_id'    => $purchase->id,
                    'cargo_party_id' => $request->cargo_party_id,
                    'total_weight'   => $request->total_weight,
                    'rate_per_kg'    => $request->rate_per_kg,
                    'total_amount'   => $cargoTotal,
                ]);
                LedgerEntry::create([
                    'account_id'     => $cargoExpenseAcc->id,
                    'warehouse_id'   => $purchase->warehouse_id,
                    'debit'          => $cargoTotal,
                    'credit'         => 0,
                    'date'           => $purchase->date,
                    'reference_type' => 'cargo_charge',
                    'reference_id'   => $purchase->id,
                    'note'           => "Transport Cargo for Purchase #{$purchase->id}",
                ]);
                LedgerEntry::create([
                    'account_id'     => $payableAcc->id,
                    'party_id'       => $request->cargo_party_id,
                    'warehouse_id'   => $purchase->warehouse_id,
                    'debit'          => 0,
                    'credit'         => $cargoTotal,
                    'date'           => $purchase->date,
                    'reference_type' => 'cargo_charge',
                    'reference_id'   => $purchase->id,
                    'note'           => "Transport Cargo for Purchase #{$purchase->id}",
                ]);
            }

            // 5. Re-create double entry accounting
            $inventoryAcc = ChartOfAccount::where('name', 'Inventory')->first();
            $purchaseNote = ($purchase->note ?? "Updated Purchase from {$purchase->party->name}") . ($purchase->discount_amount > 0 ? " (Net after Discount: " . number_format($purchase->discount_amount, 2) . ")" : "");

            // Debit Inventory (goods + per-item cargo = total_amount)
            LedgerEntry::create([
                'account_id'     => $inventoryAcc->id,
                'party_id'       => null,
                'warehouse_id'   => $purchase->warehouse_id,
                'debit'          => $purchase->total_amount,
                'credit'         => 0,
                'date'           => $purchase->date,
                'reference_type' => 'purchase',
                'reference_id'   => $purchase->id,
                'note'           => $purchaseNote,
            ]);

            // Credit Vendor (goods only when cargo party takes item cargo)
            $vendorCredit = $purchase->total_amount - ($itemCargoToParty ? $totalItemCargo : 0);
            LedgerEntry::create([
                'account_id'     => $payableAcc->id,
                'party_id'       => $purchase->party_id,
                'warehouse_id'   => $purchase->warehouse_id,
                'debit'          => 0,
                'credit'         => $vendorCredit,
                'date'           => $purchase->date,
                'reference_type' => 'purchase',
                'reference_id'   => $purchase->id,
                'note'           => $purchaseNote,
            ]);

            // 5. Re-create Payment Entry (if paid)
            if ($totalPaid > 0) {
                // How much cargo party is owed
                $cargoPartyOwed = $isTransportMode
                    ? round($request->total_weight * $request->rate_per_kg, 2)
                    : ($itemCargoToParty ? $totalItemCargo : 0);

                $remaining = $totalPaid;

                // Pay vendor first
                $vendorPayment = min($remaining, $vendorCredit);
                if ($vendorPayment > 0) {
                    LedgerEntry::create([
                        'account_id' => $payableAcc->id,
                        'party_id'   => $purchase->party_id,
                        'warehouse_id' => $purchase->warehouse_id,
                        'debit'      => $vendorPayment,
                        'credit'     => 0,
                        'date'       => $purchase->date,
                        'reference_type' => 'purchase',
                        'reference_id'   => $purchase->id,
                        'note'       => "Updated Payment made at time of Purchase #{$purchase->id}",
                    ]);
                    $remaining -= $vendorPayment;
                }

                // Pay cargo party with remaining amount (if any)
                if ($remaining > 0 && $cargoPartyOwed > 0 && $request->cargo_party_id) {
                    $cargoPayment = min($remaining, $cargoPartyOwed);
                    LedgerEntry::create([
                        'account_id' => $payableAcc->id,
                        'party_id'   => $request->cargo_party_id,
                        'warehouse_id' => $purchase->warehouse_id,
                        'debit'      => $cargoPayment,
                        'credit'     => 0,
                        'date'       => $purchase->date,
                        'reference_type' => 'purchase',
                        'reference_id'   => $purchase->id,
                        'note'       => "Updated Cargo payment at time of Purchase #{$purchase->id}",
                    ]);
                }

                // Credit Cash/Bank, one entry per payment split account
                foreach ($payments as $payment) {
                    $amount = round($payment['amount'], 2);
                    if ($amount <= 0) {
                        continue;
                    }

                    LedgerEntry::create([
                        'account_id' => $payment['account_id'],
                        'party_id'   => null,
                        'warehouse_id' => $purchase->warehouse_id,
                        'debit'      => 0,
                        'credit'     => $amount,
                        'date'       => $purchase->date,
                        'reference_type' => 'purchase',
                        'reference_id'   => $purchase->id,
                        'note'       => "Updated Payment from Cash/Bank at time of Purchase #{$purchase->id}",
                    ]);

                    PaymentSplit::create([
                        'reference_type' => 'purchase',
                        'reference_id' => $purchase->id,
                        'account_id' => $payment['account_id'],
                        'amount' => $amount,
                    ]);
                }
            }
        };

        try {
            DB::transaction($updatePurchaseTxn);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Purchase update failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withInput()->withErrors(['error' => 'Something went wrong while updating the purchase. No changes were made — please try again.']);
        }

        $this->logActivity('Updated Purchase', 'Purchases', $purchase->id, $oldData, $purchase->load('items')->toArray());

        return redirect()->route('purchases.show', $purchase)->with('success', 'Purchase updated successfully.');
    }

    public function destroy(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            // Reverse stock updates
            foreach ($purchase->items as $item) {
                $stock = Stock::where('product_id', $item->product_id)
                              ->where('warehouse_id', $purchase->warehouse_id)
                              ->lockForUpdate()
                              ->first();

                if ($stock) {
                    $oldQty = $stock->qty;
                    $oldAvgCost = $stock->avg_cost;
                    $reversedQty = $item->qty;
                    $reversedPrice = $item->price_pkr;
                    $reversedCargo = $item->cargo_amount ?? 0;

                    $newQty = $oldQty - $reversedQty;

                    if ($newQty <= 0) {
                        $stock->qty = 0;
                        $stock->avg_cost = 0;
                    } else {
                        $newAvgCost = (($oldQty * $oldAvgCost) - ($reversedQty * $reversedPrice) - $reversedCargo) / $newQty;
                        $stock->qty = $newQty;
                        $stock->avg_cost = max(0, $newAvgCost);
                    }
                    $stock->save();
                }
            }
            // Delete ledger entries (Both Purchase and Cargo) and payment splits
            LedgerEntry::whereIn('reference_type', ['purchase', 'cargo_charge'])->where('reference_id', $purchase->id)->delete();
            PaymentSplit::where('reference_type', 'purchase')->where('reference_id', $purchase->id)->delete();

            $purchase->delete();
        });

        $this->logActivity('Deleted Purchase', 'Purchases', $purchase->id, $purchase->toArray(), null);

        return redirect()->route('purchases.index')->with('success', 'Purchase deleted and stock adjusted.');
    }
    public function downloadPdf(Purchase $purchase)
    {
        $purchase->load(['party', 'warehouse', 'items.product.unit', 'paymentSplits.account']);
        $settings = \App\Models\Setting::first();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('purchases.pdf', compact('purchase', 'settings'));
        return $pdf->stream('Purchase-' . $purchase->id . '.pdf');
    }

    // Same as downloadPdf(), but reachable without login via a signed URL (see routes/web.php)
    // so it can be shared directly with a vendor, e.g. over WhatsApp.
    public function publicPdf(Purchase $purchase)
    {
        return $this->downloadPdf($purchase);
    }
}
