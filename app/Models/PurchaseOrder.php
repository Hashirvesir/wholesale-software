<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'party_id',
        'warehouse_id',
        'order_date',
        'expected_delivery_date',
        'status',
        'currency',
        'exchange_rate',
        'total_amount',
        'note',
        'created_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'exchange_rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'purchase_order_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPartial(): bool
    {
        return $this->status === 'partial';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getTotalOrderedQty(): float
    {
        return (float) $this->items()->sum('ordered_qty');
    }

    public function getTotalReceivedQty(): float
    {
        return (float) $this->items()->sum('received_qty');
    }

    public function getPendingQty(): float
    {
        return max(0, $this->getTotalOrderedQty() - $this->getTotalReceivedQty());
    }

    public function updateReceivingStatus(): void
    {
        $allCompleted = true;
        $anyReceived = false;

        foreach ($this->items as $item) {
            if ($item->received_qty > 0) {
                $anyReceived = true;
            }
            if ($item->received_qty < $item->ordered_qty) {
                $allCompleted = false;
            }
        }

        if ($allCompleted && $anyReceived) {
            $this->update(['status' => 'completed']);
        } elseif ($anyReceived) {
            $this->update(['status' => 'partial']);
        } else {
            $this->update(['status' => 'pending']);
        }
    }
}
