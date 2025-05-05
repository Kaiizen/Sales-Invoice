<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryLocation extends Model
{
    protected $fillable = [
        'warehouse_id',
        'name',
        'code',
        'zone',
        'aisle',
        'shelf',
        'bin',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function inventory()
    {
        return $this->hasMany(ProductInventory::class, 'location_id');
    }

    public function batches()
    {
        return $this->hasMany(ProductBatch::class, 'location_id');
    }

    public function rfidTags()
    {
        return $this->hasMany(RFIDTag::class, 'last_location_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFullLocationNameAttribute()
    {
        $parts = [];
        
        if ($this->warehouse) {
            $parts[] = $this->warehouse->name;
        }
        
        if ($this->zone) {
            $parts[] = "Zone: {$this->zone}";
        }
        
        if ($this->aisle) {
            $parts[] = "Aisle: {$this->aisle}";
        }
        
        if ($this->shelf) {
            $parts[] = "Shelf: {$this->shelf}";
        }
        
        if ($this->bin) {
            $parts[] = "Bin: {$this->bin}";
        }
        
        return implode(' | ', $parts);
    }
}