<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::with('account')->paginate(10);
        $accounts = ChartOfAccount::where('type', 'Expense')->where('status', true)->get();
        return view('expenses.categories', compact('categories', 'accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:expense_categories,name',
            'description' => 'nullable|string',
            'account_id' => 'nullable|exists:chart_of_accounts,id',
        ]);

        DB::transaction(function () use ($request) {
            $accountId = $request->account_id;

            // If no account selected, create a new Chart of Account
            if (!$accountId) {
                $account = ChartOfAccount::create([
                    'name' => $request->name,
                    'type' => 'Expense',
                    'status' => true,
                ]);
                $accountId = $account->id;
            }

            ExpenseCategory::create([
                'name' => $request->name,
                'description' => $request->description,
                'account_id' => $accountId,
                'status' => true,
            ]);
        });

        return back()->with('success', 'Expense category created and linked to Chart of Accounts.');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        // Optional: Prevention logic or just delete
        $expenseCategory->delete();
        return back()->with('success', 'Expense category deleted.');
    }
}
