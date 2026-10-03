<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'party_id',
        'warehouse_id',
        'account_id',
        'date',
        'currency',
        'exchange_rate',
        'total_amount',
        'discount_amount',
        'paid_amount',
        'note'
    ];

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function cargoCharge()
    {
        return $this->hasOne(CargoCharge::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    public function paymentSplits()
    {
        return $this->hasMany(PaymentSplit::class, 'reference_id')->where('reference_type', 'purchase');
    }
}
