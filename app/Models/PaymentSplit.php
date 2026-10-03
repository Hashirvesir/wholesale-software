<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSplit extends Model
{
    protected $fillable = [
        'reference_type',
        'reference_id',
        'account_id',
        'amount',
        'note',
    ];

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
