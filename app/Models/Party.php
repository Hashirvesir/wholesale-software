<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    protected $fillable = [
        'name',
        'type',
        'price_tier',
        'phone',
        'address',
        'opening_balance',
        'balance_type',
        'credit_limit',
        'credit_days',
        'status'
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'credit_days' => 'integer',
        'status' => 'boolean',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class, 'party_id');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'party_id');
    }

    public function cargoCharges()
    {
        return $this->hasMany(CargoCharge::class, 'cargo_party_id');
    }

    public function getCurrentBalance()
    {
        // Calculate Net Balance (Positive = Debit/Receivable, Negative = Credit/Payable)
        $opening = $this->opening_balance ?? 0;
        // If type is debit, opening is positive (receivable). If credit, it's negative (payable).
        $netOpening = ($this->balance_type === 'debit') ? $opening : -$opening;

        // Sum impact of all ledger entries
        $ledgerImpact = LedgerEntry::where('party_id', $this->id)
            ->select(\Illuminate\Support\Facades\DB::raw('SUM(debit) - SUM(credit) as net_impact'))
            ->first()
            ->net_impact ?? 0;
            
        return $netOpening + $ledgerImpact;
    }

    public function hasCreditLimit(): bool
    {
        return !is_null($this->credit_limit) && (float) $this->credit_limit > 0;
    }

    public function getRemainingCredit(): ?float
    {
        if (!$this->hasCreditLimit()) {
            return null;
        }
        $due = max(0, $this->getCurrentBalance());
        return max(0, (float) $this->credit_limit - $due);
    }

    public function isCreditLimitExceeded(float $additionalAmount = 0): bool
    {
        if (!$this->hasCreditLimit()) {
            return false;
        }
        $due = max(0, $this->getCurrentBalance());
        return ($due + $additionalAmount) > (float) $this->credit_limit;
    }

    public function hasOverdueInvoices(): bool
    {
        if (!$this->credit_days || $this->credit_days <= 0) {
            return false;
        }

        $cutoffDate = now()->subDays((int) $this->credit_days)->format('Y-m-d');

        return $this->sales()
            ->where('date', '<', $cutoffDate)
            ->whereRaw('(total_amount - paid_amount) > 0.01')
            ->exists();
    }

    public function getOverdueInvoices()
    {
        if (!$this->credit_days || $this->credit_days <= 0) {
            return collect();
        }

        $cutoffDate = now()->subDays((int) $this->credit_days)->format('Y-m-d');

        return $this->sales()
            ->where('date', '<', $cutoffDate)
            ->whereRaw('(total_amount - paid_amount) > 0.01')
            ->get();
    }
}
