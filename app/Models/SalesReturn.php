<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    protected $fillable = [
        'sale_id',
        'party_id',
        'warehouse_id',
        'date',
        'total_amount',
        'note'
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
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
        return $this->hasMany(SalesReturnItem::class);
    }
}
