<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $fillable = [
        'name',
        'type',
        'parent_id',
        'opening_balance',
        'balance_type',
        'status'
    ];

    public function parent()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    public function ledgerEntries()
    {
        return $this->hasMany(LedgerEntry::class, 'account_id');
    }

    public function getBalance()
    {
        $debits = $this->ledgerEntries()->sum('debit');
        $credits = $this->ledgerEntries()->sum('credit');

        $opening = $this->opening_balance ?? 0;
        // Adjust debits/credits based on opening balance type
        if ($this->balance_type === 'debit') {
            $debits += $opening;
        } else {
            $credits += $opening;
        }

        if (in_array($this->type, ['Asset', 'Expense'])) {
            return $debits - $credits;
        }

        return $credits - $debits;
    }
}
