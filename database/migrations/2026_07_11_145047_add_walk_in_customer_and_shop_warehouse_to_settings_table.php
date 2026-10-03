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
            $table->foreignId('walk_in_customer_id')->nullable()->after('low_stock_threshold')->constrained('parties')->nullOnDelete();
            $table->foreignId('shop_warehouse_id')->nullable()->after('walk_in_customer_id')->constrained('warehouses')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropForeign(['walk_in_customer_id']);
            $table->dropForeign(['shop_warehouse_id']);
            $table->dropColumn(['walk_in_customer_id', 'shop_warehouse_id']);
        });
    }
};
