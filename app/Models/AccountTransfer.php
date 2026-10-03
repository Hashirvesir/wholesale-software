<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountTransfer extends Model
{
    protected $fillable = [
        'warehouse_id',
        'from_account_id',
        'to_account_id',
        'amount',
        'date',
        'note'
    ];

    public function fromAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'to_account_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
