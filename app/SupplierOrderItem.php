<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SupplierOrderItem extends Model
{
    protected $fillable = [
        'supplier_order_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'received_quantity'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'received_quantity' => 'integer'
    ];

    public function order()
    {
        return $this->belongsTo(SupplierOrder::class, 'supplier_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Calculate total price based on quantity and unit price
    public function calculateTotal()
    {
        $this->total_price = $this->quantity * $this->unit_price;
        return $this->save();
    }

    // Get the remaining quantity to be received
    public function getRemainingQuantityAttribute()
    {
        return $this->quantity - $this->received_quantity;
    }

    // Check if the item is fully received
    public function isFullyReceived()
    {
        return $this->remaining_quantity <= 0;
    }

    // Get the completion percentage
    public function getCompletionPercentageAttribute()
    {
        if ($this->quantity == 0) {
            return 0;
        }
        
        return ($this->received_quantity / $this->quantity) * 100;
    }

    // Receive a quantity of this item
    public function receive($quantity)
    {
        if ($quantity > $this->remaining_quantity) {
            throw new \Exception('Cannot receive more than the remaining quantity');
        }
        
        $this->received_quantity += $quantity;
        return $this->save();
    }
}