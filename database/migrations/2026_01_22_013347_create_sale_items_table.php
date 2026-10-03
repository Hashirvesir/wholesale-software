<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $row) {
            $row->id();
            $row->foreignId('sale_id')->constrained()->onDelete('cascade');
            $row->foreignId('product_id')->constrained()->onDelete('cascade');
            $row->decimal('qty', 15, 2);
            $row->decimal('price', 15, 2);
            $row->decimal('total', 15, 2);
            $row->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
