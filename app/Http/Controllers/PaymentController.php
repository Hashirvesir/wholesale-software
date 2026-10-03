<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Party;
use App\Models\ChartOfAccount;
use App\Models\LedgerEntry;
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
            new Middleware('permission:view payments', only: ['index', 'show', 'downloadPdf', 'thermalPrint']),
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
        
        return view('payments.create', compact('parties', 'accounts', 'warehouses'));
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
            $payment = Payment::create($request->all());
            $party = Party::findOrFail($payment->party_id);
            $warehouseId = $request->warehouse_id ?? \App\Models\Warehouse::first()->id ?? 1; // Default to first warehouse if not selected

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
            return $payment;
        });

        $this->logActivity($request->payment_type == 'received' ? 'Received Payment' : 'Made Payment', 'Payments', $payment->id, null, $payment->toArray());

        return redirect()->route('payments.show', $payment)->with('success', 'Payment recorded successfully.');
    }

    public function show(Payment $payment)
    {
        $payment->load(['party', 'account']);
        $settings = \App\Models\Setting::first();
        return view('payments.show', compact('payment', 'settings'));
    }

    public function destroy(Payment $payment)
    {
        DB::transaction(function () use ($payment) {
            LedgerEntry::where('reference_type', 'payment')->where('reference_id', $payment->id)->delete();
            $payment->delete();
        });

        $this->logActivity('Deleted Payment', 'Payments', $payment->id, $payment->toArray(), null);

        return redirect()->route('payments.index')->with('success', 'Payment deleted successfully.');
    }

    public function downloadPdf(Payment $payment)
    {
        $payment->load(['party', 'account']);
        $settings = \App\Models\Setting::first();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('payments.pdf', compact('payment', 'settings'));
        return $pdf->download('payment-receipt-' . $payment->id . '.pdf');
    }

    public function thermalPrint(Payment $payment)
    {
        $payment->load(['party', 'account']);
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
