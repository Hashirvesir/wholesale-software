<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ChartOfAccount;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\TracksActivity;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ExpenseController extends Controller implements HasMiddleware
{
    use TracksActivity;
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view expenses', only: ['index']),
            new Middleware('permission:manage expenses', except: ['index']),
        ];
    }
    public function index(Request $request)
    {
        $query = Expense::with(['category', 'account', 'warehouse']);

        // Filter by Category
        if ($request->filled('expense_category_id')) {
            $query->where('expense_category_id', $request->expense_category_id);
        }

        // Filter by Warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Search by Note or Expense ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%");
            });
        }

        $expenses = $query->latest()->paginate(20)->withQueryString();
        $categories = ExpenseCategory::all();
        $warehouses = \App\Models\Warehouse::all();

        return view('expenses.index', compact('expenses', 'categories', 'warehouses'));
    }

    public function create()
    {
        $categories = ExpenseCategory::where('status', true)->get();
        // Paid from: Assets excluding Inventory and Receivables
        $paymentAccounts = ChartOfAccount::where('type', 'Asset')
            ->whereNotIn('name', ['Inventory', 'Accounts Receivable'])
            ->where('status', true)
            ->get();
        $warehouses = \App\Models\Warehouse::where('status', true)->get();
        return view('expenses.create', compact('categories', 'paymentAccounts', 'warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'amount' => 'required|numeric|min:0.01',
            'paid_from_account_id' => 'required|exists:chart_of_accounts,id',
            'date' => 'required|date|after_or_equal:2020-01-01|before_or_equal:today',
            'note' => 'nullable|string|max:1000',
        ], [
            'date.before_or_equal' => 'Expense date cannot be in the future.',
            'date.after_or_equal' => 'Please enter a valid expense date.',
        ]);

        $paymentAccount = ChartOfAccount::findOrFail($request->paid_from_account_id);
        if ($paymentAccount->getBalance() < $request->amount) {
            return back()->with('error', 'Insufficient balance in ' . $paymentAccount->name . '. Current balance: Rs. ' . number_format($paymentAccount->getBalance(), 2))
                         ->withInput();
        }

        $category = ExpenseCategory::find($request->expense_category_id);
        
        // Determine which ledger account to hit
        $account = null;
        if ($category->account_id) {
            $account = ChartOfAccount::find($category->account_id);
        }

        if (!$account) {
            // Fallback to "Operating Expenses"
            $account = ChartOfAccount::where('name', 'Operating Expenses')->where('type', 'Expense')->first();
            
            if (!$account) {
                // Last resort: Create it if it doesn't exist
                $account = ChartOfAccount::create([
                    'name' => 'Operating Expenses',
                    'type' => 'Expense',
                    'status' => true
                ]);
            }
        }

        $expense = DB::transaction(function () use ($request, $category, $account) {
            // Create Expense Record
            $expense = Expense::create($request->all());
            $warehouseId = $request->warehouse_id ?? \App\Models\Warehouse::first()->id ?? 1;

            // 1. Debit Expense Account (Increase Expense)
            LedgerEntry::create([
                'account_id' => $account->id,
                'warehouse_id' => $warehouseId,
                'debit' => $request->amount,
                'credit' => 0,
                'date' => $request->date,
                'reference_type' => 'expense',
                'reference_id' => $expense->id,
                'note' => $request->note ? "Expense [{$category->name}]: " . $request->note : "Expense [{$category->name}]",
            ]);

            // 2. Credit Payment Account (Decrease Asset)
            LedgerEntry::create([
                'account_id' => $request->paid_from_account_id,
                'warehouse_id' => $warehouseId,
                'debit' => 0,
                'credit' => $request->amount,
                'date' => $request->date,
                'reference_type' => 'expense',
                'reference_id' => $expense->id,
                'note' => $request->note ? 'Expense Payment: ' . $request->note : 'Expense Payment',
            ]);
            return $expense;
        });

        $this->logActivity('Recorded Expense', 'Expenses', $expense->id, null, $expense->toArray());

        return redirect()->route('expenses.index')->with('success', 'Expense recorded successfully.');
    }

    public function edit(Expense $expense)
    {
        $expense->load(['category', 'account', 'warehouse']);
        $categories = ExpenseCategory::where('status', true)->get();
        $paymentAccounts = ChartOfAccount::where('type', 'Asset')
            ->whereNotIn('name', ['Inventory', 'Accounts Receivable'])
            ->where('status', true)
            ->get();
        $warehouses = \App\Models\Warehouse::where('status', true)->get();
        return view('expenses.edit', compact('expense', 'categories', 'paymentAccounts', 'warehouses'));
    }

    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'amount' => 'required|numeric|min:0.01',
            'paid_from_account_id' => 'required|exists:chart_of_accounts,id',
            'date' => 'required|date|after_or_equal:2020-01-01|before_or_equal:today',
            'note' => 'nullable|string|max:1000',
        ], [
            'date.before_or_equal' => 'Expense date cannot be in the future.',
            'date.after_or_equal' => 'Please enter a valid expense date.',
        ]);

        $category = ExpenseCategory::find($request->expense_category_id);

        // Determine which ledger account to hit
        $account = null;
        if ($category->account_id) {
            $account = ChartOfAccount::find($category->account_id);
        }
        if (!$account) {
            $account = ChartOfAccount::where('name', 'Operating Expenses')->where('type', 'Expense')->first();
            if (!$account) {
                $account = ChartOfAccount::create([
                    'name' => 'Operating Expenses',
                    'type' => 'Expense',
                    'status' => true
                ]);
            }
        }

        $oldData = $expense->toArray();

        DB::transaction(function () use ($request, $expense, $category, $account) {
            // 1. Reverse old ledger entries
            LedgerEntry::where('reference_type', 'expense')
                ->where('reference_id', $expense->id)
                ->delete();

            // 2. Update expense record
            $expense->update($request->only([
                'expense_category_id', 'warehouse_id', 'amount',
                'paid_from_account_id', 'date', 'note'
            ]));

            $warehouseId = $request->warehouse_id ?? \App\Models\Warehouse::first()->id ?? 1;

            // 3. Create new ledger entries
            // Debit Expense Account (Increase Expense)
            LedgerEntry::create([
                'account_id' => $account->id,
                'warehouse_id' => $warehouseId,
                'debit' => $request->amount,
                'credit' => 0,
                'date' => $request->date,
                'reference_type' => 'expense',
                'reference_id' => $expense->id,
                'note' => $request->note ? "Expense [{$category->name}]: " . $request->note : "Expense [{$category->name}]",
            ]);

            // Credit Payment Account (Decrease Asset)
            LedgerEntry::create([
                'account_id' => $request->paid_from_account_id,
                'warehouse_id' => $warehouseId,
                'debit' => 0,
                'credit' => $request->amount,
                'date' => $request->date,
                'reference_type' => 'expense',
                'reference_id' => $expense->id,
                'note' => $request->note ? 'Expense Payment: ' . $request->note : 'Expense Payment',
            ]);
        });

        $this->logActivity('Updated Expense', 'Expenses', $expense->id, $oldData, $expense->fresh()->toArray());

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $oldData = $expense->toArray();

        DB::transaction(function () use ($expense) {
            // Reverse ledger entries
            LedgerEntry::where('reference_type', 'expense')
                ->where('reference_id', $expense->id)
                ->delete();

            $expense->delete();
        });

        $this->logActivity('Deleted Expense', 'Expenses', $expense->id, $oldData, null);

        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
    }
}
