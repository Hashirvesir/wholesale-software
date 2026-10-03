<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_category_id',
        'warehouse_id',
        'amount',
        'paid_from_account_id',
        'date',
        'note',
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'paid_from_account_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
