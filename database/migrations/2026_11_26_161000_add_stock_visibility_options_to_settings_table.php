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
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('hide_zero_stock_in_sale')->default(false)->after('show_sale_time');
            $table->boolean('hide_negative_stock_in_sale')->default(false)->after('hide_zero_stock_in_sale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['hide_zero_stock_in_sale', 'hide_negative_stock_in_sale']);
        });
    }
};
