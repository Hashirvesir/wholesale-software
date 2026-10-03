<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use App\Traits\TracksActivity;

class ChartOfAccountController extends Controller
{
    use TracksActivity;
    public function index(Request $request)
    {
        $query = ChartOfAccount::with('parent');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $accounts = $query->with('parent')
            ->withSum('ledgerEntries as total_debit', 'debit')
            ->withSum('ledgerEntries as total_credit', 'credit')
            ->paginate(20)->withQueryString();
        return view('chart_of_accounts.index', compact('accounts'));
    }

    public function create()
    {
        $types = ['Asset', 'Liability', 'Income', 'Expense', 'Equity'];
        $parentAccounts = ChartOfAccount::whereNull('parent_id')->get();
        return view('chart_of_accounts.create', compact('types', 'parentAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:chart_of_accounts,name',
            'type' => 'required|in:Asset,Liability,Income,Expense,Equity',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'opening_balance' => 'required|numeric|min:0',
            'balance_type' => 'required|in:debit,credit',
            'status' => 'required|boolean',
        ]);

        $account = ChartOfAccount::create($request->all());

        $this->logActivity('Created Account', 'Chart of Accounts', $account->id, null, $account->toArray());

        return redirect()->route('chart-of-accounts.index')->with('success', 'Account created successfully.');
    }

    public function edit(ChartOfAccount $chartOfAccount)
    {
        $types = ['Asset', 'Liability', 'Income', 'Expense', 'Equity'];
        $parentAccounts = ChartOfAccount::where('id', '!=', $chartOfAccount->id)->whereNull('parent_id')->get();
        return view('chart_of_accounts.edit', compact('chartOfAccount', 'types', 'parentAccounts'));
    }

    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:Asset,Liability,Income,Expense,Equity',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'opening_balance' => 'required|numeric|min:0',
            'balance_type' => 'required|in:debit,credit',
            'status' => 'required|boolean',
        ]);

        $oldData = $chartOfAccount->toArray();
        $chartOfAccount->update($request->all());

        $this->logActivity('Updated Account', 'Chart of Accounts', $chartOfAccount->id, $oldData, $chartOfAccount->toArray());

        return redirect()->route('chart-of-accounts.index')->with('success', 'Account updated successfully.');
    }

    public function destroy(ChartOfAccount $chartOfAccount)
    {
        // Check if it has children or transactions (later)
        if ($chartOfAccount->children()->count() > 0) {
            return back()->with('error', 'Cannot delete account with child accounts.');
        }

        if (\App\Models\LedgerEntry::where('account_id', $chartOfAccount->id)->exists()) {
            return back()->with('error', 'Cannot delete account with existing transactions.');
        }

        $oldData = $chartOfAccount->toArray();
        $chartOfAccount->delete();
        $this->logActivity('Deleted Account', 'Chart of Accounts', $chartOfAccount->id, $oldData, null);
        return redirect()->route('chart-of-accounts.index')->with('success', 'Account deleted successfully.');
    }
}
