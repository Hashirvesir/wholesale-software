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
        Schema::table('repair_devices', function (Blueprint $table) {
            $table->string('approval_type')->default('without_approval')->after('problem_description');
            $table->text('approval_notes')->nullable()->after('approval_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repair_devices', function (Blueprint $table) {
            $table->dropColumn(['approval_type', 'approval_notes']);
        });
    }
};
