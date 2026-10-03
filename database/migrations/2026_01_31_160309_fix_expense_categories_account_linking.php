<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $categories = \App\Models\ExpenseCategory::whereNull('account_id')->get();

        foreach ($categories as $category) {
            // 1. Create or Find specific Chart of Account for this category
            $account = \App\Models\ChartOfAccount::firstOrCreate(
                ['name' => $category->name, 'type' => 'Expense'], // Match criteria
                ['status' => true] // Creation attributes
            );

            // 2. Link Category to this Account
            $category->account_id = $account->id;
            $category->save();

            // 3. Update Historical Ledger Entries
            // Find all expenses that used this category
            $expenseIds = \App\Models\Expense::where('expense_category_id', $category->id)->pluck('id');
            
            if ($expenseIds->isNotEmpty()) {
                // Update Ledger Entries: Type='expense', ID match, and Debit > 0 (The expense side, not payment side)
                \App\Models\LedgerEntry::where('reference_type', 'expense')
                    ->whereIn('reference_id', $expenseIds)
                    ->where('debit', '>', 0) 
                    ->update(['account_id' => $account->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_categories', function (Blueprint $table) {
            //
        });
    }
};
