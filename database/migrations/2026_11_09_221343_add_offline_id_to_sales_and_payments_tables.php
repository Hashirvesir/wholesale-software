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
        if (Schema::hasTable('sales') && !Schema::hasColumn('sales', 'offline_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->string('offline_id', 100)->nullable()->unique()->after('id');
            });
        }

        if (Schema::hasTable('payments') && !Schema::hasColumn('payments', 'offline_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('offline_id', 100)->nullable()->unique()->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'offline_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('offline_id');
            });
        }

        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'offline_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('offline_id');
            });
        }
    }
};
