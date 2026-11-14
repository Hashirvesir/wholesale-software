<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairBrand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function models()
    {
        return $this->hasMany(RepairModel::class, 'repair_brand_id')->orderBy('sort_order')->orderBy('name');
    }
}
