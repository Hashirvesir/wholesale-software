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
        $oldSaleDefault = "Assalam-o-Alaikum {customer_name}, aapka Invoice #{invoice_id} taiyar hai.\nDate: {date}\nTotal: {total}\nBalance Due: {balance_due}\n\nInvoice PDF: {invoice_link}\n\nShukriya,\n{company_name}";
        $newSaleDefault = "Assalam-o-Alaikum {customer_name}, aapka Invoice #{invoice_id} taiyar hai.\nDate: {date} {time}\nStatus: {status}\nTotal: {total}\nPrevious Balance: {previous_balance}\nBalance Due: {balance_due}\n\nInvoice PDF: {invoice_link}\n\nShukriya,\n{company_name}";

        $oldPurchaseDefault = "Assalam-o-Alaikum {vendor_name}, Purchase Invoice #{invoice_id} tayyar hai.\nDate: {date}\nTotal: {total}\nBalance Due: {balance_due}\n\nInvoice PDF: {invoice_link}\n\nShukriya,\n{company_name}";
        $newPurchaseDefault = "Assalam-o-Alaikum {vendor_name}, Purchase Invoice #{invoice_id} tayyar hai.\nDate: {date}\nStatus: {status}\nTotal: {total}\nPrevious Balance: {previous_balance}\nBalance Due: {balance_due}\n\nInvoice PDF: {invoice_link}\n\nShukriya,\n{company_name}";

        // Only overwrite rows that are still on the exact old default, so any custom edit a
        // shop owner already made to their template is left untouched.
        \Illuminate\Support\Facades\DB::table('settings')
            ->where('whatsapp_sale_template', $oldSaleDefault)
            ->update(['whatsapp_sale_template' => $newSaleDefault]);

        \Illuminate\Support\Facades\DB::table('settings')
            ->where('whatsapp_purchase_template', $oldPurchaseDefault)
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
