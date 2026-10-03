<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpeningStock extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'qty',
        'rate',
        'cargo_amount',
        'total',
        'date',
        'note'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
