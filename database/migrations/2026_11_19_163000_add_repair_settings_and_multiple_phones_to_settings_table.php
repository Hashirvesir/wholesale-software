<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('company_phone_2')->nullable()->after('company_phone');
            $table->string('company_phone_3')->nullable()->after('company_phone_2');
            $table->text('repair_invoice_terms')->nullable()->after('invoice_footer_note');
            $table->text('repair_receipt_terms')->nullable()->after('repair_invoice_terms');
            $table->text('repair_custom_note')->nullable()->after('repair_receipt_terms');
        });

        // Seed default repair terms into existing setting
        DB::table('settings')->update([
            'repair_invoice_terms' => "- Checking warranty sirf counter tak ki hai (Counter se nikalne ke baad dukaandar kisi cheez ka zimmedar nahi hoga).\n- Physical damage, broken screen, display lines/dots, ya water damage ki koi warranty nahi hai.\n- Har qisam ke claim ke liye yeh original printed invoice pesh karna lazmi hai.",
            'repair_receipt_terms' => "1. Mobile wapsi lene ke waqt yeh slip pesh karna lazmi hai.\n2. Checking warranty sirf counter tak ki hai (Counter se nikalne ke baad dukaandar kisi cheez ka zimmedar nahi hoga).\n3. Slip gum hone ki soorat mein CNIC copy aur tasdeeq lazmi hogi.\n4. Phone data (photos, contacts) backup customer ki zimmedari hai.\n5. 30 din baad mobile ki dukaan zimmedar nahi hogi.",
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'company_phone_2',
                'company_phone_3',
                'repair_invoice_terms',
                'repair_receipt_terms',
                'repair_custom_note',
            ]);
        });
    }
};
