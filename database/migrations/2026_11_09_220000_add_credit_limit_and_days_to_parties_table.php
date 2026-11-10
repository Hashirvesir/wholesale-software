<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->decimal('credit_limit', 15, 2)->nullable()->after('opening_balance');
            $table->unsignedInteger('credit_days')->nullable()->after('credit_limit');
        });
    }

    public function down(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->dropColumn(['credit_limit', 'credit_days']);
        });
    }
};
