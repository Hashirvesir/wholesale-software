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
        Schema::table('settings', function (Blueprint $table) {
            $table->text('whatsapp_ledger_template')->nullable()->after('whatsapp_purchase_template');
        });

        \Illuminate\Support\Facades\DB::table('settings')->update([
            'whatsapp_ledger_template' => "Assalam-o-Alaikum {party_name}, aapka statement bhej raha hun.\nPeriod: {start_date} to {end_date}\nBalance: {balance}\n\nLedger PDF: {ledger_link}\n\nShukriya,\n{company_name}",
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('whatsapp_ledger_template');
        });
    }
};
