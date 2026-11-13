<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Party;
use App\Models\ChartOfAccount;
use App\Models\LedgerEntry;
use App\Models\PaymentAllocation;
use App\Models\RepairOrder;
use App\Models\Sale;
use App\Models\Purchase;
use App\Traits\TracksActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PaymentController extends Controller implements HasMiddleware
{
    use TracksActivity;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view payments', only: ['index', 'show', 'downloadPdf', 'thermalPrint', 'partyPendingInvoices']),
            new Middleware('permission:create payments', only: ['create', 'store']),
            new Middleware('permission:delete payments', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = Payment::with(['party', 'account', 'warehouse']);

        // Search by Party Name or Payment ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('party', function($qp) use ($search) {
                      $qp->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by Type
        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        // Filter by Warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Date Filter
        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        $payments = $query->latest()->paginate(10)->withQueryString();
        $warehouses = \App\Models\Warehouse::all();
        return view('payments.index', compact('payments', 'warehouses'));
    }

    public function partyPendingInvoices(Request $request)
    {
        $partyId = $request->query('party_id');
        $paymentType = $request->query('payment_type', 'received');

        if (!$partyId) {
            return response()->json(['invoices' => []]);
        }

        $invoices = [];

        if ($paymentType === 'received') {
            // Customer: Unpaid Repair Orders & Unpaid Sales
            $repairs = RepairOrder::where('party_id', $partyId)
                ->where('balance_amount', '>', 0)
                ->orderBy('received_date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            foreach ($repairs as $repair) {
                $invoices[] = [
                    'type' => 'repair',
                    'type_label' => 'Mobile Repair',
                    'id' => $repair->id,
                    'reference_no' => $repair->order_no,
                    'date' => $repair->received_date ? $repair->received_date->format('d M, Y') : '',
                    'sort_date' => $repair->received_date ? $repair->received_date->toIso8601String() : '',
                    'total_amount' => (float)$repair->total_amount,
                    'paid_amount' => (float)$repair->advance_amount,
                    'balance_amount' => (float)$repair->balance_amount,
                ];
            }

            $sales = Sale::where('party_id', $partyId)
                ->whereRaw('paid_amount < (total_amount - discount_amount)')
                ->orderBy('date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            foreach ($sales as $sale) {
                $net = (float)$sale->total_amount - (float)$sale->discount_amount;
                $due = max(0, $net - (float)$sale->paid_amount);
                $invoices[] = [
                    'type' => 'sale',
                    'type_label' => 'Sale Invoice',
                    'id' => $sale->id,
                    'reference_no' => 'INV-' . str_pad($sale->id, 5, '0', STR_PAD_LEFT),
                    'date' => $sale->date ? \Carbon\Carbon::parse($sale->date)->format('d M, Y') : '',
                    'sort_date' => $sale->date ? \Carbon\Carbon::parse($sale->date)->toIso8601String() : '',
                    'total_amount' => $net,
                    'paid_amount' => (float)$sale->paid_amount,
                    'balance_amount' => $due,
                ];
            }
        } else {
            // Supplier: Unpaid Purchases
            $purchases = Purchase::where('party_id', $partyId)
                ->whereRaw('paid_amount < (total_amount - discount_amount)')
                ->orderBy('date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            foreach ($purchases as $purchase) {
                $net = (float)$purchase->total_amount - (float)$purchase->discount_amount;
                $due = max(0, $net - (float)$purchase->paid_amount);
                $invoices[] = [
                    'type' => 'purchase',
                    'type_label' => 'Purchase Bill',
                    'id' => $purchase->id,
                    'reference_no' => 'BILL-' . str_pad($purchase->id, 5, '0', STR_PAD_LEFT),
                    'date' => $purchase->date ? \Carbon\Carbon::parse($purchase->date)->format('d M, Y') : '',
                    'sort_date' => $purchase->date ? \Carbon\Carbon::parse($purchase->date)->toIso8601String() : '',
                    'total_amount' => $net,
                    'paid_amount' => (float)$purchase->paid_amount,
                    'balance_amount' => $due,
                ];
            }
        }

        // Sort ascending by date (FIFO order)
        usort($invoices, function($a, $b) {
            return strcmp($a['sort_date'], $b['sort_date']);
        });

        return response()->json(['invoices' => $invoices]);
    }

    public function create()
    {
        $parties = Party::where('status', true)->get()->map(function ($party) {
            return [
                'id' => $party->id,
                'name' => $party->name,
                'type' => $party->type,
                'balance' => $party->getCurrentBalance(),
            ];
        });

        // Get Cash and Bank accounts (Assets excluding Inventory and Receivables)
        $accounts = ChartOfAccount::where('type', 'Asset')
            ->whereNotIn('name', ['Inventory', 'Accounts Receivable'])
            ->where('status', true)
            ->get();
        $warehouses = \App\Models\Warehouse::where('status', true)->get();
        $settings = \App\Models\Setting::first();
        $salesmen = \App\Models\User::all();
        
        return view('payments.create', compact('parties', 'accounts', 'warehouses', 'settings', 'salesmen'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'party_id' => 'required|exists:parties,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'from_account_id' => 'required|exists:chart_of_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_type' => 'required|in:received,paid',
            // Block typos like 2026-11-04 (for 11-04) or year 0005: a future-dated entry is
            // counted in the party's current balance but not in the ledger report up to today.
            'date' => 'required|date|after_or_equal:2020-01-01|before_or_equal:today',
            'note' => 'nullable|string',
        ], [
            'date.before_or_equal' => 'Payment date cannot be in the future.',
            'date.after_or_equal' => 'Please enter a valid payment date.',
        ]);

        $payment = DB::transaction(function () use ($request) {
            $settings = \App\Models\Setting::first();
            $warehouseId = $request->warehouse_id ?: ($settings->shop_warehouse_id ?? \App\Models\Warehouse::first()->id ?? 1);

            $paymentData = $request->all();
            $paymentData['warehouse_id'] = $warehouseId;
            $paymentData['salesman_id'] = $request->salesman_id ?? auth()->id();
            $payment = Payment::create($paymentData);

            $party = Party::findOrFail($payment->party_id);

            $receivableAcc = ChartOfAccount::where('name', 'Accounts Receivable')->first();
            $payableAcc = ChartOfAccount::where('name', 'Accounts Payable')->first();

            // Determine which account to hit based on Party Type
            // Customers -> Accounts Receivable
            // Vendors/Cargo -> Accounts Payable
            $partyAccount = ($party->type === 'customer') ? $receivableAcc : $payableAcc;

            if ($payment->payment_type === 'received') {
                // Received Payment: Debit Cash/Bank, Credit Party Account
                
                // Debit Cash/Bank
                LedgerEntry::create([
                    'account_id' => $payment->from_account_id,
                    'party_id' => null,
                    'warehouse_id' => $warehouseId,
                    'debit' => $payment->amount,
                    'credit' => 0,
                    'date' => $payment->date,
                    'reference_type' => 'payment',
                    'reference_id' => $payment->id,
                    'note' => $payment->note ?? 'Payment Received',
                ]);

                // Credit Party Account
                LedgerEntry::create([
                    'account_id' => $partyAccount->id,
                    'party_id' => $payment->party_id,
                    'warehouse_id' => $warehouseId,
                    'debit' => 0,
                    'credit' => $payment->amount,
                    'date' => $payment->date,
                    'reference_type' => 'payment',
                    'reference_id' => $payment->id,
                    'note' => $payment->note ?? 'Payment Received from Party',
                ]);
            } else {
                // Paid Payment: Debit Party Account, Credit Cash/Bank
                
                // Debit Party Account
                LedgerEntry::create([
                    'account_id' => $partyAccount->id,
                    'party_id' => $payment->party_id,
                    'warehouse_id' => $warehouseId,
                    'debit' => $payment->amount,
                    'credit' => 0,
                    'date' => $payment->date,
                    'reference_type' => 'payment',
                    'reference_id' => $payment->id,
                    'note' => $payment->note ?? 'Payment Paid to Party',
                ]);

                // Credit Cash/Bank
                LedgerEntry::create([
                    'account_id' => $payment->from_account_id,
                    'party_id' => null,
                    'warehouse_id' => $warehouseId,
                    'debit' => 0,
                    'credit' => $payment->amount,
                    'date' => $payment->date,
                    'reference_type' => 'payment',
                    'reference_id' => $payment->id,
                    'note' => $payment->note ?? 'Payment Paid',
                ]);
            }

            // Allocate payment to unpaid invoices / repair orders
            $unallocated = (float) $payment->amount;
            $manualAllocations = $request->input('allocations', []);

            if (!empty($manualAllocations) && is_array($manualAllocations)) {
                foreach ($manualAllocations as $type => $records) {
                    if (!is_array($records)) continue;
                    foreach ($records as $targetId => $allocAmt) {
                        $allocAmt = (float) $allocAmt;
                        if ($allocAmt <= 0 || $unallocated <= 0) continue;
                        $amountToApply = min($allocAmt, $unallocated);

                        if ($type === 'repair') {
                            $repair = RepairOrder::where('id', $targetId)->where('party_id', $payment->party_id)->first();
                            if ($repair && (float)$repair->balance_amount > 0) {
                                $actual = min($amountToApply, (float)$repair->balance_amount);
                                $repair->advance_amount += $actual;
                                $repair->balance_amount = max(0, (float)$repair->total_amount - (float)$repair->advance_amount);
                                $repair->save();

                                PaymentAllocation::create([
                                    'payment_id' => $payment->id,
                                    'allocatable_type' => RepairOrder::class,
                                    'allocatable_id' => $repair->id,
                                    'amount' => $actual,
                                ]);
                                $unallocated -= $actual;
                            }
                        } elseif ($type === 'sale') {
                            $sale = Sale::where('id', $targetId)->where('party_id', $payment->party_id)->first();
                            if ($sale) {
                                $due = max(0, ((float)$sale->total_amount - (float)$sale->discount_amount) - (float)$sale->paid_amount);
                                if ($due > 0) {
                                    $actual = min($amountToApply, $due);
                                    $sale->paid_amount += $actual;
                                    $sale->save();

                                    PaymentAllocation::create([
                                        'payment_id' => $payment->id,
                                        'allocatable_type' => Sale::class,
                                        'allocatable_id' => $sale->id,
                                        'amount' => $actual,
                                    ]);
                                    $unallocated -= $actual;
                                }
                            }
                        } elseif ($type === 'purchase') {
                            $purchase = Purchase::where('id', $targetId)->where('party_id', $payment->party_id)->first();
                            if ($purchase) {
                                $due = max(0, ((float)$purchase->total_amount - (float)$purchase->discount_amount) - (float)$purchase->paid_amount);
                                if ($due > 0) {
                                    $actual = min($amountToApply, $due);
                                    $purchase->paid_amount += $actual;
                                    $purchase->save();

                                    PaymentAllocation::create([
                                        'payment_id' => $payment->id,
                                        'allocatable_type' => Purchase::class,
                                        'allocatable_id' => $purchase->id,
                                        'amount' => $actual,
                                    ]);
                                    $unallocated -= $actual;
                                }
                            }
                        }
                    }
                }
            }

            // Auto-settle remainder using FIFO if any unallocated amount remains
            if ($unallocated > 0) {
                if ($payment->payment_type === 'received') {
                    $pendingRepairs = RepairOrder::where('party_id', $payment->party_id)
                        ->where('balance_amount', '>', 0)
                        ->orderBy('received_date', 'asc')
                        ->orderBy('id', 'asc')
                        ->get();

                    $pendingSales = Sale::where('party_id', $payment->party_id)
                        ->whereRaw('paid_amount < (total_amount - discount_amount)')
                        ->orderBy('date', 'asc')
                        ->orderBy('id', 'asc')
                        ->get();

                    $combined = collect();
                    foreach ($pendingRepairs as $r) {
                        $combined->push([
                            'type' => 'repair',
                            'sort_date' => $r->received_date ? $r->received_date->toDateTimeString() : $r->created_at->toDateTimeString(),
                            'due' => (float)$r->balance_amount,
                            'model' => $r,
                        ]);
                    }
                    foreach ($pendingSales as $s) {
                        $due = max(0, ((float)$s->total_amount - (float)$s->discount_amount) - (float)$s->paid_amount);
                        $combined->push([
                            'type' => 'sale',
                            'sort_date' => $s->date ? $s->date . ' 00:00:00' : $s->created_at->toDateTimeString(),
                            'due' => $due,
                            'model' => $s,
                        ]);
                    }

                    $sorted = $combined->sortBy('sort_date');

                    foreach ($sorted as $item) {
                        if ($unallocated <= 0) break;
                        $alloc = min($unallocated, $item['due']);
                        if ($alloc <= 0) continue;

                        if ($item['type'] === 'repair') {
                            $repair = $item['model'];
                            $repair->advance_amount += $alloc;
                            $repair->balance_amount = max(0, (float)$repair->total_amount - (float)$repair->advance_amount);
                            $repair->save();

                            PaymentAllocation::create([
                                'payment_id' => $payment->id,
                                'allocatable_type' => RepairOrder::class,
                                'allocatable_id' => $repair->id,
                                'amount' => $alloc,
                            ]);
                        } elseif ($item['type'] === 'sale') {
                            $sale = $item['model'];
                            $sale->paid_amount += $alloc;
                            $sale->save();

                            PaymentAllocation::create([
                                'payment_id' => $payment->id,
                                'allocatable_type' => Sale::class,
                                'allocatable_id' => $sale->id,
                                'amount' => $alloc,
                            ]);
                        }

                        $unallocated -= $alloc;
                    }
                } elseif ($payment->payment_type === 'paid') {
                    $pendingPurchases = Purchase::where('party_id', $payment->party_id)
                        ->whereRaw('paid_amount < (total_amount - discount_amount)')
                        ->orderBy('date', 'asc')
                        ->orderBy('id', 'asc')
                        ->get();

                    foreach ($pendingPurchases as $purchase) {
                        if ($unallocated <= 0) break;
                        $due = max(0, ((float)$purchase->total_amount - (float)$purchase->discount_amount) - (float)$purchase->paid_amount);
                        if ($due <= 0) continue;

                        $alloc = min($unallocated, $due);
                        $purchase->paid_amount += $alloc;
                        $purchase->save();

                        PaymentAllocation::create([
                            'payment_id' => $payment->id,
                            'allocatable_type' => Purchase::class,
                            'allocatable_id' => $purchase->id,
                            'amount' => $alloc,
                        ]);

                        $unallocated -= $alloc;
                    }
                }
            }

            return $payment;
        });

        $this->logActivity($request->payment_type == 'received' ? 'Received Payment' : 'Made Payment', 'Payments', $payment->id, null, $payment->toArray());

        return redirect()->route('payments.show', $payment)->with('success', 'Payment recorded successfully.');
    }

    public function show(Payment $payment)
    {
        $payment->load(['party', 'account', 'warehouse', 'allocations.allocatable']);
        $settings = \App\Models\Setting::first();
        return view('payments.show', compact('payment', 'settings'));
    }

    public function destroy(Payment $payment)
    {
        DB::transaction(function () use ($payment) {
            // Revert allocations
            $allocations = PaymentAllocation::where('payment_id', $payment->id)->get();
            foreach ($allocations as $allocation) {
                if ($allocation->allocatable_type === RepairOrder::class) {
                    $repair = RepairOrder::find($allocation->allocatable_id);
                    if ($repair) {
                        $repair->advance_amount = max(0, (float)$repair->advance_amount - (float)$allocation->amount);
                        $repair->balance_amount = max(0, (float)$repair->total_amount - (float)$repair->advance_amount);
                        $repair->save();
                    }
                } elseif ($allocation->allocatable_type === Sale::class) {
                    $sale = Sale::find($allocation->allocatable_id);
                    if ($sale) {
                        $sale->paid_amount = max(0, (float)$sale->paid_amount - (float)$allocation->amount);
                        $sale->save();
                    }
                } elseif ($allocation->allocatable_type === Purchase::class) {
                    $purchase = Purchase::find($allocation->allocatable_id);
                    if ($purchase) {
                        $purchase->paid_amount = max(0, (float)$purchase->paid_amount - (float)$allocation->amount);
                        $purchase->save();
                    }
                }
            }
            PaymentAllocation::where('payment_id', $payment->id)->delete();

            LedgerEntry::where('reference_type', 'payment')->where('reference_id', $payment->id)->delete();
            $payment->delete();
        });

        $this->logActivity('Deleted Payment', 'Payments', $payment->id, $payment->toArray(), null);

        return redirect()->route('payments.index')->with('success', 'Payment deleted successfully.');
    }

    public function downloadPdf(Payment $payment)
    {
        $payment->load(['party', 'account', 'warehouse']);
        $settings = \App\Models\Setting::first();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('payments.pdf', compact('payment', 'settings'));
        return $pdf->download('payment-receipt-' . $payment->id . '.pdf');
    }

    public function publicPdf(Payment $payment)
    {
        return $this->downloadPdf($payment);
    }

    public function thermalPrint(Payment $payment)
    {
        $payment->load(['party', 'account', 'warehouse']);
        $settings = \App\Models\Setting::first();
        
        $currentBalance = $payment->party->getCurrentBalance();
        // Previous balance = Current balance minus the impact of THIS specifically recorded payment
        // For 'received' (receipt), party was credited (credit > 0), so previous balance was higher (more debit/receivable).
        // For 'paid' (payment), party was debited (debit > 0), so previous balance was lower (more credit/payable).
        $impact = ($payment->payment_type === 'received') ? -$payment->amount : $payment->amount;
        $previousBalance = $currentBalance - $impact;

        return view('payments.thermal', compact('payment', 'settings', 'previousBalance', 'currentBalance'));
    }
}
