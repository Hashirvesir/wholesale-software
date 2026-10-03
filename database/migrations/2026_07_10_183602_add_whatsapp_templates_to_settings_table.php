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
            $table->text('whatsapp_sale_template')->nullable()->after('invoice_footer_note');
            $table->text('whatsapp_purchase_template')->nullable()->after('whatsapp_sale_template');
        });

        \Illuminate\Support\Facades\DB::table('settings')->update([
            'whatsapp_sale_template' => "Assalam-o-Alaikum {customer_name}, aapka Invoice #{invoice_id} taiyar hai.\nDate: {date}\nTotal: {total}\nBalance Due: {balance_due}\n\nInvoice PDF: {invoice_link}\n\nShukriya,\n{company_name}",
            'whatsapp_purchase_template' => "Assalam-o-Alaikum {vendor_name}, Purchase Invoice #{invoice_id} tayyar hai.\nDate: {date}\nTotal: {total}\nBalance Due: {balance_due}\n\nInvoice PDF: {invoice_link}\n\nShukriya,\n{company_name}",
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_sale_template', 'whatsapp_purchase_template']);
        });
    }
};
