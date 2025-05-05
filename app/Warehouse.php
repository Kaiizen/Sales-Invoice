<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = [
        'name',
        'code',
        'address',
        'contact_person',
        'contact_number',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function locations()
    {
        return $this->hasMany(InventoryLocation::class);
    }

    public function inventory()
    {
        return $this->hasManyThrough(
            ProductInventory::class,
            InventoryLocation::class,
            'warehouse_id',
            'location_id'
        );
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getTotalProductsAttribute()
    {
        return $this->inventory()
            ->select('product_id')
            ->distinct()
            ->count();
    }

    public function getTotalStockValueAttribute()
    {
        return $this->inventory()
            ->join('products', 'product_inventories.product_id', '=', 'products.id')
            ->selectRaw('SUM(product_inventories.quantity * products.price) as total_value')
            ->value('total_value') ?? 0;
    }
}