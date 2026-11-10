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
        Schema::create('cheques', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['received', 'issued'])->default('received');
            $table->foreignId('party_id')->constrained('parties')->cascadeOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->string('cheque_number', 100);
            $table->string('drawer_bank', 150)->nullable();
            $table->decimal('amount', 15, 2);
            $table->date('issue_date');
            $table->date('due_date');
            $table->enum('status', ['pending', 'deposited', 'cleared', 'bounced', 'cancelled'])->default('pending');
            $table->date('deposited_date')->nullable();
            $table->date('cleared_date')->nullable();
            $table->date('bounced_date')->nullable();
            $table->text('bounce_reason')->nullable();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cheques');
    }
};
