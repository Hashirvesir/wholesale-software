<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'party_id', 'warehouse_id', 'from_account_id', 'amount', 'payment_type', 'date', 'note'
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
}
