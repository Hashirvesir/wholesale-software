<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairDevicePart extends Model
{
    use HasFactory;

    protected $fillable = [
        'repair_device_id',
        'product_id',
        'warehouse_id',
        'quantity',
        'unit_cost',
        'unit_price',
        'subtotal',
        'is_deducted',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'is_deducted' => 'boolean',
    ];

    public function device()
    {
        return $this->belongsTo(RepairDevice::class, 'repair_device_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
