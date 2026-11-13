<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ChartOfAccount;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        ChartOfAccount::firstOrCreate(
            ['name' => 'Repair Services Income'],
            [
                'type' => 'Income',
                'opening_balance' => 0,
                'balance_type' => 'credit',
                'status' => 1,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        ChartOfAccount::where('name', 'Repair Services Income')->delete();
    }
};
