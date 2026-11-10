<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id',
        'product_id',
        'qty',
        'price',
        'total',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getUnitPriceAttribute(): float
    {
        return (float) $this->price;
    }

    public function getTotalPriceAttribute(): float
    {
        return (float) $this->total;
    }
}
