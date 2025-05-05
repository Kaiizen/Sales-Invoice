<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    protected $fillable = [
        'product_id',
        'batch_number',
        'manufactured_date',
        'expiry_date',
        'quantity',
        'cost_price',
        'location_id',
        'supplier_id',
        'purchase_id',
        'notes'
    ];

    protected $casts = [
        'manufactured_date' => 'date',
        'expiry_date' => 'date',
        'quantity' => 'integer',
        'cost_price' => 'decimal:2'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function location()
    {
        return $this->belongsTo(InventoryLocation::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function rfidTags()
    {
        return $this->hasMany(RFIDTag::class);
    }

    // Check if batch is expired
    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    // Check if batch is expiring soon (within 30 days by default)
    public function isExpiringSoon($days = 30)
    {
        return $this->expiry_date && 
               $this->expiry_date->isFuture() && 
               $this->expiry_date->diffInDays(now()) <= $days;
    }

    // Scope for non-expired batches
    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expiry_date')
              ->orWhere('expiry_date', '>', now());
        });
    }

    // Scope for expired batches
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<=', now());
    }

    // Scope for batches expiring soon
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '>', now())
                     ->where('expiry_date', '<=', now()->addDays($days));
    }

    // Add stock to this batch
    public function addStock($quantity, $userId, $reference = null, $notes = null)
    {
        $this->quantity += $quantity;
        $this->save();

        // Record movement
        return InventoryMovement::create([
            'product_id' => $this->product_id,
            'quantity' => $quantity,
            'movement_type' => 'adjustment',
            'notes' => $notes ?? "Batch {$this->batch_number} stock adjustment",
            'user_id' => $userId,
            'reference_id' => $reference ? $reference->id : null,
            'reference_type' => $reference ? get_class($reference) : null
        ]);
    }

    // Remove stock from this batch
    public function removeStock($quantity, $userId, $reference = null, $notes = null)
    {
        if ($quantity > $this->quantity) {
            throw new \Exception('Cannot remove more than available quantity');
        }

        $this->quantity -= $quantity;
        $this->save();

        // Record movement
        return InventoryMovement::create([
            'product_id' => $this->product_id,
            'quantity' => -$quantity,
            'movement_type' => 'adjustment',
            'notes' => $notes ?? "Batch {$this->batch_number} stock adjustment",
            'user_id' => $userId,
            'reference_id' => $reference ? $reference->id : null,
            'reference_type' => $reference ? get_class($reference) : null
        ]);
    }

    // Get days until expiry
    public function getDaysUntilExpiryAttribute()
    {
        if (!$this->expiry_date) {
            return null;
        }

        if ($this->isExpired()) {
            return 0;
        }

        return now()->diffInDays($this->expiry_date);
    }
}