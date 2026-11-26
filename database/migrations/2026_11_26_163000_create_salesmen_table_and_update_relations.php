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
        // 1. Create salesmen table
        Schema::create('salesmen', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(0.00);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // 2. Add salesman_id to parties table
        Schema::table('parties', function (Blueprint $table) {
            $table->foreignId('salesman_id')->nullable()->after('type')->constrained('salesmen')->nullOnDelete();
        });

        // 3. Re-point salesman_id on sales table from users to salesmen
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['salesman_id']);
            $table->foreign('salesman_id')->references('id')->on('salesmen')->nullOnDelete();
        });

        // 4. Re-point salesman_id on payments table from users to salesmen
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['salesman_id']);
            $table->foreign('salesman_id')->references('id')->on('salesmen')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['salesman_id']);
            $table->foreign('salesman_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['salesman_id']);
            $table->foreign('salesman_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('parties', function (Blueprint $table) {
            $table->dropForeign(['salesman_id']);
            $table->dropColumn('salesman_id');
        });

        Schema::dropIfExists('salesmen');
    }
};
