<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'repair_brand_id',
        'name',
        'series',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function brand()
    {
        return $this->belongsTo(RepairBrand::class, 'repair_brand_id');
    }
}
