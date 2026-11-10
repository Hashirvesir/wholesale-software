<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'offline_id', 'party_id', 'warehouse_id', 'salesman_id', 'from_account_id', 'amount', 'payment_type', 'date', 'note'
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
        return $this->belongsTo(User::class, 'salesman_id');
    }
}
