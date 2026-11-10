<?php

namespace App\Http\Controllers;

use App\Models\Cheque;
use App\Models\Party;
use App\Models\ChartOfAccount;
use App\Models\Warehouse;
use App\Models\Payment;
use App\Models\LedgerEntry;
use App\Models\Setting;
use App\Traits\TracksActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ChequeController extends Controller implements HasMiddleware
{
    use TracksActivity;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view payments', only: ['index', 'show']),
            new Middleware('permission:create payments', only: ['create', 'store', 'deposit', 'clear', 'bounce']),
            new Middleware('permission:delete payments', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = Cheque::with(['party', 'bankAccount', 'warehouse', 'payment']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('party_id')) {
            $query->where('party_id', $request->party_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('cheque_number', 'like', "%{$search}%")
                  ->orWhere('drawer_bank', 'like', "%{$search}%")
                  ->orWhereHas('party', function ($qp) use ($search) {
                      $qp->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('due_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('due_date', '<=', $request->date_to);
        }

        $cheques = $query->orderBy('due_date', 'asc')->paginate(15)->withQueryString();

        // Metrics
        $totalReceivedPending = Cheque::where('type', 'received')
            ->whereIn('status', ['pending', 'deposited'])
            ->sum('amount');

        $totalIssuedPending = Cheque::where('type', 'issued')
            ->whereIn('status', ['pending', 'deposited'])
            ->sum('amount');

        $dueTodayCount = Cheque::whereIn('status', ['pending', 'deposited'])
            ->whereDate('due_date', today())
            ->count();

        $overdueCount = Cheque::whereIn('status', ['pending', 'deposited'])
            ->whereDate('due_date', '<', today())
            ->count();

        $parties = Party::where('status', true)->get();
        $bankAccounts = ChartOfAccount::where('type', 'Asset')->where('status', true)->get();

        return view('cheques.index', compact(
            'cheques',
            'totalReceivedPending',
            'totalIssuedPending',
            'dueTodayCount',
            'overdueCount',
            'parties',
            'bankAccounts'
        ));
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'received');
        $parties = Party::where('status', true)->get()->map(function ($party) {
            return [
                'id' => $party->id,
                'name' => $party->name,
                'type' => $party->type,
                'phone' => $party->phone ?? '',
                'balance' => (float)$party->getCurrentBalance(),
            ];
        });

        $bankAccounts = ChartOfAccount::where('type', 'Asset')->where('status', true)->get();
        $warehouses = Warehouse::where('status', true)->get();
        $settings = Setting::first();

        return view('cheques.create', compact('type', 'parties', 'bankAccounts', 'warehouses', 'settings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:received,issued',
            'party_id' => 'required|exists:parties,id',
            'bank_account_id' => 'nullable|exists:chart_of_accounts,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'cheque_number' => 'required|string|max:100',
            'drawer_bank' => 'nullable|string|max:150',
            'amount' => 'required|numeric|min:0.01',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'note' => 'nullable|string|max:1000',
        ]);

        $settings = Setting::first();
        if (empty($validated['warehouse_id'])) {
            $validated['warehouse_id'] = $settings->shop_warehouse_id ?? Warehouse::first()?->id;
        }

        // Prevent duplicate double submission within 15 seconds
        $recentDuplicate = Cheque::where('party_id', $validated['party_id'])
            ->where('cheque_number', $validated['cheque_number'])
            ->where('amount', $validated['amount'])
            ->where('type', $validated['type'])
            ->where('created_at', '>=', now()->subSeconds(15))
            ->first();

        if ($recentDuplicate) {
            return redirect()->route('cheques.index')->with('warning', 'Cheque #' . $validated['cheque_number'] . ' was already recorded just now. Duplicate entry prevented.');
        }

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'pending';

        $cheque = Cheque::create($validated);

        $this->logActivity('Registered Cheque', 'Cheques', $cheque->id, null, $cheque->toArray());

        return redirect()->route('cheques.index')->with('success', 'Cheque registered successfully.');
    }

    public function show(Cheque $cheque)
    {
        $cheque->load(['party', 'bankAccount', 'warehouse', 'payment', 'creator']);
        $bankAccounts = ChartOfAccount::where('type', 'Asset')->where('status', true)->get();
        return view('cheques.show', compact('cheque', 'bankAccounts'));
    }

    public function edit(Cheque $cheque)
    {
        if ($cheque->status === 'cleared') {
            return redirect()->route('cheques.show', $cheque)->with('error', 'Cleared cheques cannot be edited.');
        }

        $parties = Party::where('status', true)->get();
        $bankAccounts = ChartOfAccount::where('type', 'Asset')->where('status', true)->get();
        $warehouses = Warehouse::where('status', true)->get();

        return view('cheques.edit', compact('cheque', 'parties', 'bankAccounts', 'warehouses'));
    }

    public function update(Request $request, Cheque $cheque)
    {
        if ($cheque->status === 'cleared') {
            return redirect()->route('cheques.show', $cheque)->with('error', 'Cleared cheques cannot be edited.');
        }

        $validated = $request->validate([
            'type' => 'required|in:received,issued',
            'party_id' => 'required|exists:parties,id',
            'bank_account_id' => 'nullable|exists:chart_of_accounts,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'cheque_number' => 'required|string|max:100',
            'drawer_bank' => 'nullable|string|max:150',
            'amount' => 'required|numeric|min:0.01',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'note' => 'nullable|string|max:1000',
        ]);

        $oldData = $cheque->toArray();
        $cheque->update($validated);

        $this->logActivity('Updated Cheque', 'Cheques', $cheque->id, $oldData, $cheque->toArray());

        return redirect()->route('cheques.show', $cheque)->with('success', 'Cheque updated successfully.');
    }

    public function destroy(Cheque $cheque)
    {
        if ($cheque->status === 'cleared') {
            return redirect()->route('cheques.index')->with('error', 'Cleared cheques cannot be deleted. Bounce or cancel it first.');
        }

        $oldData = $cheque->toArray();
        $cheque->delete();

        $this->logActivity('Deleted Cheque', 'Cheques', $cheque->id, $oldData, null);

        return redirect()->route('cheques.index')->with('success', 'Cheque deleted successfully.');
    }

    public function deposit(Request $request, Cheque $cheque)
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|exists:chart_of_accounts,id',
            'deposited_date' => 'required|date',
        ]);

        $cheque->update([
            'status' => 'deposited',
            'bank_account_id' => $validated['bank_account_id'],
            'deposited_date' => $validated['deposited_date'],
        ]);

        $this->logActivity('Deposited Cheque to Bank', 'Cheques', $cheque->id, null, $cheque->toArray());

        return redirect()->back()->with('success', 'Cheque marked as deposited.');
    }

    public function clear(Request $request, Cheque $cheque)
    {
        if ($cheque->status === 'cleared') {
            return redirect()->back()->with('error', 'This cheque has already been cleared.');
        }

        $validated = $request->validate([
            'bank_account_id' => 'nullable|exists:chart_of_accounts,id',
            'cleared_date' => 'required|date',
        ]);

        $bankAccountId = $validated['bank_account_id'] ?? $cheque->bank_account_id;

        if (!$bankAccountId) {
            return redirect()->back()->with('error', 'Please select a Bank Account for clearance.');
        }

        DB::transaction(function () use ($cheque, $bankAccountId, $validated) {
            $party = $cheque->party;
            $warehouseId = $cheque->warehouse_id;

            // 1. Create linked Payment
            $paymentType = ($cheque->type === 'received') ? 'received' : 'paid';

            $payment = Payment::create([
                'party_id' => $cheque->party_id,
                'from_account_id' => $bankAccountId,
                'warehouse_id' => $warehouseId,
                'amount' => $cheque->amount,
                'payment_type' => $paymentType,
                'date' => $validated['cleared_date'],
                'note' => 'Cheque Cleared: #' . $cheque->cheque_number . ($cheque->drawer_bank ? ' (' . $cheque->drawer_bank . ')' : ''),
            ]);

            // 2. Post Double-Entry Ledger Entries
            $partyAccountName = ($party->type === 'customer') ? 'Accounts Receivable' : 'Accounts Payable';
            $partyAccount = ChartOfAccount::where('name', $partyAccountName)->first();

            if (!$partyAccount) {
                $partyAccount = ChartOfAccount::create([
                    'name' => $partyAccountName,
                    'type' => ($party->type === 'customer') ? 'Asset' : 'Liability',
                    'opening_balance' => 0,
                    'balance_type' => ($party->type === 'customer') ? 'debit' : 'credit',
                    'status' => true,
                ]);
            }

            if ($paymentType === 'received') {
                // Debit Bank
                LedgerEntry::create([
                    'account_id' => $bankAccountId,
                    'party_id' => null,
                    'warehouse_id' => $warehouseId,
                    'debit' => $cheque->amount,
                    'credit' => 0,
                    'date' => $validated['cleared_date'],
                    'reference_type' => 'payment',
                    'reference_id' => $payment->id,
                    'note' => 'Cheque Cleared #' . $cheque->cheque_number,
                ]);

                // Credit Customer Party
                LedgerEntry::create([
                    'account_id' => $partyAccount->id,
                    'party_id' => $cheque->party_id,
                    'warehouse_id' => $warehouseId,
                    'debit' => 0,
                    'credit' => $cheque->amount,
                    'date' => $validated['cleared_date'],
                    'reference_type' => 'payment',
                    'reference_id' => $payment->id,
                    'note' => 'Payment Received via Cheque #' . $cheque->cheque_number,
                ]);
            } else {
                // Debit Vendor Party
                LedgerEntry::create([
                    'account_id' => $partyAccount->id,
                    'party_id' => $cheque->party_id,
                    'warehouse_id' => $warehouseId,
                    'debit' => $cheque->amount,
                    'credit' => 0,
                    'date' => $validated['cleared_date'],
                    'reference_type' => 'payment',
                    'reference_id' => $payment->id,
                    'note' => 'Payment Made via Cheque #' . $cheque->cheque_number,
                ]);

                // Credit Bank
                LedgerEntry::create([
                    'account_id' => $bankAccountId,
                    'party_id' => null,
                    'warehouse_id' => $warehouseId,
                    'debit' => 0,
                    'credit' => $cheque->amount,
                    'date' => $validated['cleared_date'],
                    'reference_type' => 'payment',
                    'reference_id' => $payment->id,
                    'note' => 'Cheque Issued Cleared #' . $cheque->cheque_number,
                ]);
            }

            // 3. Update Cheque status
            $cheque->update([
                'status' => 'cleared',
                'bank_account_id' => $bankAccountId,
                'cleared_date' => $validated['cleared_date'],
                'payment_id' => $payment->id,
            ]);

            $this->logActivity('Cleared Cheque', 'Cheques', $cheque->id, null, $cheque->toArray());
        });

        return redirect()->back()->with('success', 'Cheque cleared successfully! Ledger and accounts have been updated.');
    }

    public function bounce(Request $request, Cheque $cheque)
    {
        if ($cheque->status === 'bounced') {
            return redirect()->back()->with('error', 'This cheque has already been marked as bounced.');
        }

        $validated = $request->validate([
            'bounced_date' => 'required|date',
            'bounce_reason' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($cheque, $validated) {
            // If it was previously cleared, delete ledger and payment
            if ($cheque->payment_id) {
                LedgerEntry::where('reference_type', 'payment')
                    ->where('reference_id', $cheque->payment_id)
                    ->delete();

                Payment::where('id', $cheque->payment_id)->delete();
            }

            $cheque->update([
                'status' => 'bounced',
                'bounced_date' => $validated['bounced_date'],
                'bounce_reason' => $validated['bounce_reason'],
                'payment_id' => null,
            ]);

            $this->logActivity('Cheque Bounced', 'Cheques', $cheque->id, null, $cheque->toArray());
        });

        return redirect()->back()->with('warning', 'Cheque has been marked as Bounced.');
    }
}
