<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ChartOfAccount;
use App\Models\ExpenseCategory;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Auto-sync any existing Expense accounts in Chart of Accounts to Expense Categories
        try {
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
                        $cat->update(['account_id' => $acc->id, 'status' => $acc->status]);
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
        } catch (\Throwable $e) {
            // Log or ignore if tables do not exist yet in fresh install
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive action needed on rollback
    }
};
