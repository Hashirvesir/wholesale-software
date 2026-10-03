<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    protected $fillable = [
        'name',
        'type',
        'phone',
        'address',
        'opening_balance',
        'balance_type',
        'status'
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
}
