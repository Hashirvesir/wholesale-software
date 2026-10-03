<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChartOfAccount;

class ChartOfAccountSeeder extends Seeder
{
    public function run()
    {
        $accounts = [
            ['name' => 'Cash', 'type' => 'Asset'],
            ['name' => 'Bank', 'type' => 'Asset'],
            ['name' => 'Inventory', 'type' => 'Asset'],
            ['name' => 'Accounts Receivable', 'type' => 'Asset'],
            ['name' => 'Accounts Payable', 'type' => 'Liability'],
            ['name' => 'Sales', 'type' => 'Income'],
            ['name' => 'Purchase', 'type' => 'Expense'],
            ['name' => 'COGS', 'type' => 'Expense'],
            ['name' => 'Cargo Expense', 'type' => 'Expense'],
            ['name' => 'Operating Expenses', 'type' => 'Expense'],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::firstOrCreate(['name' => $account['name']], $account);
        }
    }
}
