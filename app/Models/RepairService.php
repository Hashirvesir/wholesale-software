<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairService extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'default_labor_cost',
        'description',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'default_labor_cost' => 'decimal:2',
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];
}
