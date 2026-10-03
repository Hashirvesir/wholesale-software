<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'company_name',
        'company_logo',
        'company_phone',
        'company_email',
        'company_address',
        'timezone',
        'currency_symbol',
        'invoice_footer_note',
        'whatsapp_sale_template',
        'whatsapp_purchase_template',
        'whatsapp_ledger_template',
        'invoice_prefix',
        'low_stock_threshold',
        'show_sale_time',
        'walk_in_customer_id',
        'shop_warehouse_id',
    ];

    protected $casts = [
        'show_sale_time' => 'boolean',
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
