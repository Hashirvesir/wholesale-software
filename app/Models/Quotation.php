<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $fillable = [
        'party_id',
        'warehouse_id',
        'date',
        'valid_until',
        'total_amount',
        'discount_amount',
        'status',
        'converted_sale_id',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
        'valid_until' => 'date',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

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
        return $this->hasMany(QuotationItem::class);
    }

    public function convertedSale()
    {
        return $this->belongsTo(Sale::class, 'converted_sale_id');
    }

    public function isConverted(): bool
    {
        return $this->status === 'converted' || !empty($this->converted_sale_id);
    }

    public function getSubtotalAttribute(): float
    {
        return (float) ($this->total_amount + $this->discount_amount);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft Bill',
            'sent' => 'Sent to Customer',
            'accepted' => 'Accepted',
            'converted' => 'Converted to Sale',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }
}
