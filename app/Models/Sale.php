<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'party_id',
        'warehouse_id',
        'account_id',
        'date',
        'total_amount',
        'discount_amount',
        'paid_amount',
        'note'
    ];

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function paymentSplits()
    {
        return $this->hasMany(PaymentSplit::class, 'reference_id')->where('reference_type', 'sale');
    }
}
