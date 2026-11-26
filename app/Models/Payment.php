<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'offline_id', 'party_id', 'is_direct_payment', 'warehouse_id', 'salesman_id', 'from_account_id', 'amount', 'payment_type', 'date', 'note'
    ];

    protected $casts = [
        'is_direct_payment' => 'boolean',
    ];

    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'from_account_id');
    }
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function salesman()
    {
        return $this->belongsTo(Salesman::class, 'salesman_id');
    }

    public function allocations()
    {
        return $this->hasMany(PaymentAllocation::class);
    }
}
