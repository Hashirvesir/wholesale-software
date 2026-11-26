<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salesman extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'commission_rate',
        'status',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function customers()
    {
        return $this->hasMany(Party::class, 'salesman_id');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'salesman_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'salesman_id');
    }
}
