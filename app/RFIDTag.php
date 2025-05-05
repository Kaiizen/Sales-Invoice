<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RFIDTag extends Model
{
    protected $fillable = [
        'tag_id',
        'product_id',
        'batch_id',
        'last_scanned_at',
        'last_location_id',
        'status'
    ];

    protected $casts = [
        'last_scanned_at' => 'datetime'
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_LOST = 'lost';
    const STATUS_DAMAGED = 'damaged';

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch()
    {
        return $this->belongsTo(ProductBatch::class);
    }

    public function location()
    {
        return $this->belongsTo(InventoryLocation::class, 'last_location_id');
    }

    // Real-time tracking methods
    public function updateLocation($locationId, $userId = null)
    {
        $oldLocationId = $this->last_location_id;
        $this->last_location_id = $locationId;
        $this->last_scanned_at = now();
        $this->save();
        
        // Log the movement
        $this->logMovement($oldLocationId, $locationId, $userId);
        
        // Trigger real-time update event if needed
        // event(new RFIDTagScanned($this));
        
        return $this;
    }
    
    // Log RFID tag movement
    protected function logMovement($fromLocationId, $toLocationId, $userId = null)
    {
        $fromLocation = $fromLocationId ? InventoryLocation::find($fromLocationId) : null;
        $toLocation = $toLocationId ? InventoryLocation::find($toLocationId) : null;
        
        $notes = "RFID Tag {$this->tag_id} moved";
        
        if ($fromLocation) {
            $notes .= " from {$fromLocation->name}";
        }
        
        if ($toLocation) {
            $notes .= " to {$toLocation->name}";
        }
        
        // Record in inventory movements
        return InventoryMovement::create([
            'product_id' => $this->product_id,
            'quantity' => 0, // No quantity change, just tracking movement
            'movement_type' => 'adjustment',
            'notes' => $notes,
            'user_id' => $userId ?? 1, // Default to system user if not provided
            'reference_id' => $this->id,
            'reference_type' => self::class
        ]);
    }
    
    // Mark tag as lost
    public function markAsLost($userId = null, $notes = null)
    {
        $this->status = self::STATUS_LOST;
        $this->save();
        
        // Log the status change
        return InventoryMovement::create([
            'product_id' => $this->product_id,
            'quantity' => 0, // No quantity change
            'movement_type' => 'adjustment',
            'notes' => $notes ?? "RFID Tag {$this->tag_id} marked as lost",
            'user_id' => $userId ?? 1,
            'reference_id' => $this->id,
            'reference_type' => self::class
        ]);
    }
    
    // Mark tag as damaged
    public function markAsDamaged($userId = null, $notes = null)
    {
        $this->status = self::STATUS_DAMAGED;
        $this->save();
        
        // Log the status change
        return InventoryMovement::create([
            'product_id' => $this->product_id,
            'quantity' => 0, // No quantity change
            'movement_type' => 'adjustment',
            'notes' => $notes ?? "RFID Tag {$this->tag_id} marked as damaged",
            'user_id' => $userId ?? 1,
            'reference_id' => $this->id,
            'reference_type' => self::class
        ]);
    }
    
    // Activate tag
    public function activate($userId = null)
    {
        $this->status = self::STATUS_ACTIVE;
        $this->save();
        
        // Log the status change
        return InventoryMovement::create([
            'product_id' => $this->product_id,
            'quantity' => 0, // No quantity change
            'movement_type' => 'adjustment',
            'notes' => "RFID Tag {$this->tag_id} activated",
            'user_id' => $userId ?? 1,
            'reference_id' => $this->id,
            'reference_type' => self::class
        ]);
    }
    
    // Deactivate tag
    public function deactivate($userId = null)
    {
        $this->status = self::STATUS_INACTIVE;
        $this->save();
        
        // Log the status change
        return InventoryMovement::create([
            'product_id' => $this->product_id,
            'quantity' => 0, // No quantity change
            'movement_type' => 'adjustment',
            'notes' => "RFID Tag {$this->tag_id} deactivated",
            'user_id' => $userId ?? 1,
            'reference_id' => $this->id,
            'reference_type' => self::class
        ]);
    }
    
    // Scope for active tags
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
    
    // Scope for inactive tags
    public function scopeInactive($query)
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }
    
    // Scope for lost tags
    public function scopeLost($query)
    {
        return $query->where('status', self::STATUS_LOST);
    }
    
    // Scope for damaged tags
    public function scopeDamaged($query)
    {
        return $query->where('status', self::STATUS_DAMAGED);
    }
}