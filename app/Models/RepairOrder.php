<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class RepairOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_no',
        'party_id',
        'customer_name',
        'customer_phone',
        'received_date',
        'expected_delivery_date',
        'total_devices',
        'total_parts_cost',
        'total_labor_cost',
        'total_amount',
        'advance_amount',
        'balance_amount',
        'payment_method',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'received_date' => 'datetime',
        'expected_delivery_date' => 'datetime',
        'total_parts_cost' => 'decimal:2',
        'total_labor_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'advance_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'total_devices' => 'integer',
    ];

    public function customer()
    {
        return $this->belongsTo(Party::class, 'party_id');
    }

    public function devices()
    {
        return $this->hasMany(RepairDevice::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paymentAllocations()
    {
        return $this->morphMany(PaymentAllocation::class, 'allocatable');
    }

    public static function generateOrderNo(): string
    {
        $prefix = 'REP-' . date('Ym') . '-';
        $latest = self::where('order_no', 'like', $prefix . '%')->latest('id')->first();
        if ($latest) {
            $number = intval(substr($latest->order_no, strlen($prefix))) + 1;
        } else {
            $number = 1;
        }
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 border-amber-300',
            'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 border-blue-300',
            'partially_completed' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300 border-purple-300',
            'completed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 border-emerald-300',
            'delivered' => 'bg-teal-100 text-teal-800 dark:bg-teal-900/50 dark:text-teal-300 border-teal-300',
            'cancelled' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-300 border-rose-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
        };
    }
}
