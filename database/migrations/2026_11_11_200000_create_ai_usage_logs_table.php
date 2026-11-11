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
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 50); // e.g. 'chat', 'scan_bill', 'recovery_message', 'daily_briefing'
            $table->string('year_month', 7); // e.g. '2026-11'
            $table->integer('tokens')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->text('metadata')->nullable(); // Optional JSON for query/response debug
            $table->timestamps();

            $table->index('year_month');
            $table->index(['user_id', 'year_month']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
    }
};
