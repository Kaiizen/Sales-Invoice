<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SupplierOrder extends Model
{
    protected $fillable = [
        'supplier_id',
        'status',
        'total_amount',
        'expected_delivery_date',
        'actual_delivery_date',
        'notes',
        'is_auto_generated',
        'created_by',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'is_auto_generated' => 'boolean',
        'approved_at' => 'datetime'
    ];

    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_ORDERED = 'ordered';
    const STATUS_PARTIALLY_RECEIVED = 'partially_received';
    const STATUS_RECEIVED = 'received';
    const STATUS_CANCELLED = 'cancelled';

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(SupplierOrderItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Approve the order
    public function approve($userId)
    {
        if ($this->status !== self::STATUS_PENDING_APPROVAL) {
            throw new \Exception('Only pending orders can be approved');
        }

        $this->status = self::STATUS_APPROVED;
        $this->approved_by = $userId;
        $this->approved_at = now();
        return $this->save();
    }

    // Mark the order as ordered (sent to supplier)
    public function markAsOrdered($expectedDeliveryDate = null)
    {
        if ($this->status !== self::STATUS_APPROVED) {
            throw new \Exception('Only approved orders can be marked as ordered');
        }

        $this->status = self::STATUS_ORDERED;
        if ($expectedDeliveryDate) {
            $this->expected_delivery_date = $expectedDeliveryDate;
        }
        return $this->save();
    }

    // Receive items (partial or complete)
    public function receiveItems(array $receivedItems)
    {
        if (!in_array($this->status, [self::STATUS_ORDERED, self::STATUS_PARTIALLY_RECEIVED])) {
            throw new \Exception('Only ordered or partially received orders can receive items');
        }

        $allReceived = true;

        foreach ($receivedItems as $itemId => $quantity) {
            $item = $this->items()->findOrFail($itemId);
            
            // Update received quantity
            $newReceivedQuantity = $item->received_quantity + $quantity;
            
            if ($newReceivedQuantity > $item->quantity) {
                throw new \Exception("Cannot receive more than ordered quantity for item {$item->id}");
            }
            
            $item->received_quantity = $newReceivedQuantity;
            $item->save();
            
            // Add to inventory
            $product = Product::findOrFail($item->product_id);
            $product->recordPurchase($quantity, $this, "Received from order #{$this->id}");
            
            // Check if all items are fully received
            if ($item->received_quantity < $item->quantity) {
                $allReceived = false;
            }
        }
        
        // Update order status
        if ($allReceived) {
            $this->status = self::STATUS_RECEIVED;
            $this->actual_delivery_date = now();
        } else {
            $this->status = self::STATUS_PARTIALLY_RECEIVED;
        }
        
        return $this->save();
    }

    // Cancel the order
    public function cancel($reason = null)
    {
        if (in_array($this->status, [self::STATUS_RECEIVED, self::STATUS_CANCELLED])) {
            throw new \Exception('Cannot cancel completed or already cancelled orders');
        }

        $this->status = self::STATUS_CANCELLED;
        if ($reason) {
            $this->notes = $this->notes ? $this->notes . "\n\nCancellation reason: " . $reason : "Cancellation reason: " . $reason;
        }
        return $this->save();
    }

    // Calculate total amount based on items
    public function calculateTotal()
    {
        $total = $this->items()->sum('total_price');
        $this->total_amount = $total;
        return $this->save();
    }

    // Get the total received quantity
    public function getTotalReceivedQuantityAttribute()
    {
        return $this->items()->sum('received_quantity');
    }

    // Get the total ordered quantity
    public function getTotalOrderedQuantityAttribute()
    {
        return $this->items()->sum('quantity');
    }

    // Get the completion percentage
    public function getCompletionPercentageAttribute()
    {
        if ($this->total_ordered_quantity == 0) {
            return 0;
        }
        
        return ($this->total_received_quantity / $this->total_ordered_quantity) * 100;
    }

    // Scope for pending approval orders
    public function scopePendingApproval($query)
    {
        return $query->where('status', self::STATUS_PENDING_APPROVAL);
    }

    // Scope for approved orders
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    // Scope for ordered orders
    public function scopeOrdered($query)
    {
        return $query->where('status', self::STATUS_ORDERED);
    }

    // Scope for partially received orders
    public function scopePartiallyReceived($query)
    {
        return $query->where('status', self::STATUS_PARTIALLY_RECEIVED);
    }

    // Scope for received orders
    public function scopeReceived($query)
    {
        return $query->where('status', self::STATUS_RECEIVED);
    }

    // Scope for cancelled orders
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    // Scope for auto-generated orders
    public function scopeAutoGenerated($query)
    {
        return $query->where('is_auto_generated', true);
    }
}