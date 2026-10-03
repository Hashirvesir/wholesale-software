<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $row) {
            $row->id();
            $row->foreignId('party_id')->constrained()->onDelete('cascade'); // Customer
            $row->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $row->date('date');
            $row->decimal('total_amount', 15, 2);
            $row->decimal('paid_amount', 15, 2)->default(0);
            $row->text('note')->nullable();
            $row->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
