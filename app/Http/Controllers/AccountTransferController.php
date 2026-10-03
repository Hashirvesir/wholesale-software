<?php

namespace App\Http\Controllers;

use App\Models\AccountTransfer;
use App\Models\ChartOfAccount;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\TracksActivity;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AccountTransferController extends Controller implements HasMiddleware
{
    use TracksActivity;
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view account transfers', only: ['index']),
            new Middleware('permission:create account transfers', except: ['index', 'destroy']),
            new Middleware('permission:delete account transfers', only: ['destroy']),
        ];
    }
    public function index()
    {
        $transfers = AccountTransfer::with(['fromAccount', 'toAccount', 'warehouse'])->latest()->paginate(10);
        return view('account_transfers.index', compact('transfers'));
    }

    public function create()
    {
        // Get Cash and Bank accounts (Assets excluding Inventory and Receivables)
        $accounts = ChartOfAccount::where('type', 'Asset')
            ->whereNotIn('name', ['Inventory', 'Accounts Receivable'])
            ->where('status', true)
            ->get();
        $warehouses = \App\Models\Warehouse::where('status', true)->get();
        
        return view('account_transfers.create', compact('accounts', 'warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_account_id' => 'required|exists:chart_of_accounts,id|different:to_account_id',
            'to_account_id' => 'required|exists:chart_of_accounts,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        $fromAccount = ChartOfAccount::findOrFail($request->from_account_id);
        if ($fromAccount->getBalance() < $request->amount) {
            return back()->with('error', 'Insufficient balance in ' . $fromAccount->name . '. Current balance: Rs. ' . number_format($fromAccount->getBalance(), 2))
                         ->withInput();
        }

        $transfer = DB::transaction(function () use ($request) {
            $transfer = AccountTransfer::create($request->all());
            $warehouseId = $request->warehouse_id ?? \App\Models\Warehouse::first()->id ?? 1;

            // 1. Credit From Account (Decrease Asset)
            LedgerEntry::create([
                'account_id' => $transfer->from_account_id,
                'party_id' => null,
                'warehouse_id' => $warehouseId,
                'debit' => 0,
                'credit' => $transfer->amount,
                'date' => $transfer->date,
                'reference_type' => 'transfer',
                'reference_id' => $transfer->id,
                'note' => $transfer->note ?? "Transfer to {$transfer->toAccount->name}",
            ]);

            // 2. Debit To Account (Increase Asset)
            LedgerEntry::create([
                'account_id' => $transfer->to_account_id,
                'party_id' => null,
                'warehouse_id' => $warehouseId,
                'debit' => $transfer->amount,
                'credit' => 0,
                'date' => $transfer->date,
                'reference_type' => 'transfer',
                'reference_id' => $transfer->id,
                'note' => $transfer->note ?? "Transfer from {$transfer->fromAccount->name}",
            ]);
            return $transfer;
        });

        $this->logActivity('Account Transfer', 'Account Transfers', $transfer->id, null, $transfer->toArray());

        return redirect()->route('account-transfers.index')->with('success', 'Transfer recorded successfully.');
    }

    public function destroy(AccountTransfer $accountTransfer)
    {
        $oldData = $accountTransfer->toArray();
        DB::transaction(function () use ($accountTransfer) {
            LedgerEntry::where('reference_type', 'transfer')->where('reference_id', $accountTransfer->id)->delete();
            $accountTransfer->delete();
        });

        $this->logActivity('Deleted Account Transfer', 'Account Transfers', $accountTransfer->id, $oldData, null);

        return redirect()->route('account-transfers.index')->with('success', 'Transfer deleted successfully.');
    }
}
