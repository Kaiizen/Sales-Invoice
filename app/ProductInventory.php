<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductInventory extends Model
{
    protected $fillable = [
        'product_id',
        'location_id',
        'quantity',
        'reserved_quantity'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function location()
    {
        return $this->belongsTo(InventoryLocation::class);
    }

    // Available quantity (not reserved)
    public function getAvailableQuantityAttribute()
    {
        return $this->quantity - $this->reserved_quantity;
    }

    // Reserve stock for orders
    public function reserve($quantity)
    {
        if ($quantity > $this->available_quantity) {
            throw new \Exception('Cannot reserve more than available quantity');
        }

        $this->reserved_quantity += $quantity;
        return $this->save();
    }

    // Release reserved stock
    public function release($quantity)
    {
        $this->reserved_quantity = max(0, $this->reserved_quantity - $quantity);
        return $this->save();
    }

    // Add stock to this location
    public function addStock($quantity, $userId, $reference = null, $notes = null)
    {
        $this->quantity += $quantity;
        $this->save();

        // Record movement
        return $this->recordMovement($quantity, 'adjustment', $userId, $reference, $notes);
    }

    // Remove stock from this location
    public function removeStock($quantity, $userId, $reference = null, $notes = null)
    {
        if ($quantity > $this->available_quantity) {
            throw new \Exception('Cannot remove more than available quantity');
        }

        $this->quantity -= $quantity;
        $this->save();

        // Record movement
        return $this->recordMovement(-$quantity, 'adjustment', $userId, $reference, $notes);
    }

    // Record inventory movement
    protected function recordMovement($quantity, $type, $userId, $reference = null, $notes = null)
    {
        return InventoryMovement::create([
            'product_id' => $this->product_id,
            'quantity' => $quantity,
            'movement_type' => $type,
            'notes' => $notes ?? "Stock adjustment at location {$this->location->name}",
            'user_id' => $userId,
            'reference_id' => $reference ? $reference->id : null,
            'reference_type' => $reference ? get_class($reference) : null
        ]);
    }
}