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
        // The previous migration's exact-string match failed on some setups because the
        // stored text came back with \r\n line endings instead of \n. Match more loosely
        // here (by leading wording), and jump straight to the final default with all
        // placeholders (status, previous balance, time, discount) so a shop owner's real
        // customization is left alone but anyone still on our own default gets upgraded.
        $newSaleDefault = "Assalam-o-Alaikum {customer_name}, aapka Invoice #{invoice_id} taiyar hai.\nDate: {date} {time}\nStatus: {status}\nTotal: {total}\n{discount}Previous Balance: {previous_balance}\nBalance Due: {balance_due}\n\nInvoice PDF: {invoice_link}\n\nShukriya,\n{company_name}";
        $newPurchaseDefault = "Assalam-o-Alaikum {vendor_name}, Purchase Invoice #{invoice_id} tayyar hai.\nDate: {date} {time}\nStatus: {status}\nTotal: {total}\n{discount}Previous Balance: {previous_balance}\nBalance Due: {balance_due}\n\nInvoice PDF: {invoice_link}\n\nShukriya,\n{company_name}";

        \Illuminate\Support\Facades\DB::table('settings')
            ->where('whatsapp_sale_template', 'like', 'Assalam-o-Alaikum {customer_name}%')
            ->where('whatsapp_sale_template', 'not like', '%{discount}%')
            ->update(['whatsapp_sale_template' => $newSaleDefault]);

        \Illuminate\Support\Facades\DB::table('settings')
            ->where('whatsapp_purchase_template', 'like', 'Assalam-o-Alaikum {vendor_name}%')
            ->where('whatsapp_purchase_template', 'not like', '%{discount}%')
            ->update(['whatsapp_purchase_template' => $newPurchaseDefault]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
