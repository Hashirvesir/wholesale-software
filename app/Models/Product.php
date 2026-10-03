<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'barcode',
        'category_id',
        'brand_id',
        'unit_id',
        'sale_price',
        'alert_quantity',
        'status'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function getCurrentStock($warehouseId = null)
    {
        $query = $this->stocks();
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        return (float) $query->sum('qty');
    }

    public function getBarcodeSVG()
    {
        $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
        $barcode_value = $this->barcode ?? $this->sku;
        return $generator->getBarcode($barcode_value, $generator::TYPE_CODE_128);
    }
}
