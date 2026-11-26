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

    protected static function booted()
    {
        static::saved(function ($account) {
            if ($account->type === 'Expense' && !in_array($account->name, ['Purchase', 'COGS'])) {
                $category = ExpenseCategory::where('account_id', $account->id)->first();
                if (!$category) {
                    $category = ExpenseCategory::where('name', $account->name)->first();
                }

                if ($category) {
                    $category->update([
                        'name' => $account->name,
                        'account_id' => $account->id,
                        'status' => $account->status,
                    ]);
                } else {
                    ExpenseCategory::create([
                        'name' => $account->name,
                        'account_id' => $account->id,
                        'status' => $account->status,
                        'description' => 'Auto-synced from Chart of Accounts',
                    ]);
                }
            } elseif ($account->type !== 'Expense') {
                // If account was changed from Expense to another type
                $category = ExpenseCategory::where('account_id', $account->id)->first();
                if ($category) {
                    if ($category->expenses()->count() === 0) {
                        $category->delete();
                    } else {
                        $category->update(['status' => false, 'account_id' => null]);
                    }
                }
            }
        });

        static::deleting(function ($account) {
            if ($account->type === 'Expense') {
                $categories = ExpenseCategory::where('account_id', $account->id)->get();
                foreach ($categories as $category) {
                    if ($category->expenses()->count() === 0) {
                        $category->delete();
                    } else {
                        $category->update(['account_id' => null, 'status' => false]);
                    }
                }
            }
        });
    }

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

    public function getCurrentBalance()
    {
        return $this->getBalance();
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
