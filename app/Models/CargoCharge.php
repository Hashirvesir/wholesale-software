<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CargoCharge extends Model
{
    protected $fillable = [
        'purchase_id',
        'cargo_party_id',
        'total_weight',
        'rate_per_kg',
        'total_amount',
        'is_paid'
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function cargoParty()
    {
        return $this->belongsTo(Party::class, 'cargo_party_id');
    }
}
