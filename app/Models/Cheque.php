<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cheque extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'party_id',
        'bank_account_id',
        'warehouse_id',
        'cheque_number',
        'drawer_bank',
        'amount',
        'issue_date',
        'due_date',
        'status',
        'deposited_date',
        'cleared_date',
        'bounced_date',
        'bounce_reason',
        'payment_id',
        'created_by',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
        'deposited_date' => 'date',
        'cleared_date' => 'date',
        'bounced_date' => 'date',
    ];

    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'bank_account_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isDeposited(): bool
    {
        return $this->status === 'deposited';
    }

    public function isCleared(): bool
    {
        return $this->status === 'cleared';
    }

    public function isBounced(): bool
    {
        return $this->status === 'bounced';
    }

    public function isOverdue(): bool
    {
        return in_array($this->status, ['pending', 'deposited']) && $this->due_date->isPast() && !$this->due_date->isToday();
    }

    public function isDueToday(): bool
    {
        return in_array($this->status, ['pending', 'deposited']) && $this->due_date->isToday();
    }

    public function scopeReceived($query)
    {
        return $query->where('type', 'received');
    }

    public function scopeIssued($query)
    {
        return $query->where('type', 'issued');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'deposited']);
    }
}
