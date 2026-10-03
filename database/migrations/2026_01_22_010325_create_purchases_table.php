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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('party_id')->constrained()->onDelete('cascade'); // Vendor
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->enum('currency', ['PKR', 'RMB'])->default('PKR');
            $table->decimal('exchange_rate', 15, 2)->default(1.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
