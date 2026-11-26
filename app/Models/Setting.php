<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'company_name',
        'company_logo',
        'company_phone',
        'company_phone_2',
        'company_phone_3',
        'company_email',
        'company_address',
        'timezone',
        'currency_symbol',
        'invoice_footer_note',
        'repair_invoice_terms',
        'repair_receipt_terms',
        'repair_custom_note',
        'whatsapp_sale_template',
        'whatsapp_purchase_template',
        'whatsapp_ledger_template',
        'invoice_prefix',
        'low_stock_threshold',
        'show_sale_time',
        'hide_zero_stock_in_sale',
        'hide_negative_stock_in_sale',
        'walk_in_customer_id',
        'shop_warehouse_id',
    ];

    protected $casts = [
        'show_sale_time' => 'boolean',
        'hide_zero_stock_in_sale' => 'boolean',
        'hide_negative_stock_in_sale' => 'boolean',
    ];

    public function walkInCustomer()
    {
        return $this->belongsTo(Party::class, 'walk_in_customer_id');
    }

    public function shopWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'shop_warehouse_id');
    }
}
