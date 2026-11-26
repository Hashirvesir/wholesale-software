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
        // Self-healing: ensure any existing Expense account in Chart of Accounts has a category
        $expenseAccounts = ChartOfAccount::where('type', 'Expense')
            ->whereNotIn('name', ['Purchase', 'COGS'])
            ->get();

        foreach ($expenseAccounts as $acc) {
            $cat = ExpenseCategory::where('account_id', $acc->id)->first();
            if (!$cat) {
                $cat = ExpenseCategory::where('name', $acc->name)->first();
            }

            if ($cat) {
                if (!$cat->account_id) {
                    $cat->update(['account_id' => $acc->id]);
                }
            } else {
                ExpenseCategory::create([
                    'name' => $acc->name,
                    'account_id' => $acc->id,
                    'description' => 'Auto-synced from Chart of Accounts',
                    'status' => $acc->status,
                ]);
            }
        }

        $categories = ExpenseCategory::with('account')->paginate(15);
        $accounts = ChartOfAccount::where('type', 'Expense')
            ->whereNotIn('name', ['Purchase', 'COGS'])
            ->where('status', true)
            ->get();
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

            // Update or create to prevent double insert from ChartOfAccount booted listener
            ExpenseCategory::updateOrCreate(
                ['account_id' => $accountId],
                [
                    'name' => $request->name,
                    'description' => $request->description ?? 'Created from Expense Categories',
                    'status' => true,
                ]
            );
        });

        return back()->with('success', 'Expense category created and linked to Chart of Accounts.');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        if ($expenseCategory->expenses()->count() > 0) {
            return back()->with('error', 'Cannot delete category that has existing expense records.');
        }

        $expenseCategory->delete();
        return back()->with('success', 'Expense category deleted.');
    }
}
