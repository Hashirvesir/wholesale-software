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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('wholesale_price', 15, 2)->nullable()->after('sale_price');
            $table->decimal('retail_price', 15, 2)->nullable()->after('wholesale_price');
            $table->decimal('dealer_price', 15, 2)->nullable()->after('retail_price');
        });

        Schema::table('parties', function (Blueprint $table) {
            $table->string('price_tier', 20)->default('wholesale')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['wholesale_price', 'retail_price', 'dealer_price']);
        });

        Schema::table('parties', function (Blueprint $table) {
            $table->dropColumn('price_tier');
        });
    }
};
