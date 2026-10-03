<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    protected $fillable = [
        'account_id',
        'party_id',
        'warehouse_id',
        'debit',
        'credit',
        'date',
        'reference_type',
        'reference_id',
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
}
