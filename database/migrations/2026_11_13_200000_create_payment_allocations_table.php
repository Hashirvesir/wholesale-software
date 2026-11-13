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
        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->string('allocatable_type'); // App\Models\RepairOrder, App\Models\Sale, App\Models\Purchase
            $table->unsignedBigInteger('allocatable_id');
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->index(['allocatable_type', 'allocatable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
    }
};
