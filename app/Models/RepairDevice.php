<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'repair_order_id',
        'device_tracking_no',
        'brand',
        'model',
        'imei',
        'color',
        'security_type',
        'security_code',
        'problem_description',
        'accessories_received',
        'condition_checklist',
        'technician_id',
        'status',
        'labor_cost',
        'parts_cost',
        'total_cost',
        'warranty_days',
        'completed_at',
        'delivered_at',
    ];

    protected $casts = [
        'condition_checklist' => 'array',
        'labor_cost' => 'decimal:2',
        'parts_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'warranty_days' => 'integer',
        'completed_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function repairOrder()
    {
        return $this->belongsTo(RepairOrder::class);
    }

    public function parts()
    {
        return $this->hasMany(RepairDevicePart::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border-amber-300',
            'diagnosing' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300 border-indigo-300',
            'waiting_parts' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300 border-orange-300',
            'completed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border-emerald-300',
            'delivered' => 'bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300 border-teal-300',
            'unrepairable' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 border-rose-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
        };
    }
}
