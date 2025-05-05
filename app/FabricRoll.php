<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FabricRoll extends Model
{
    protected $fillable = [
        'product_id',
        'roll_number',
        'width',
        'length',
        'original_square_feet',
        'remaining_square_feet',
        'remaining_percentage',
        'supplier_id',
        'purchase_id',
        'received_date',
        'status',
        'notes'
    ];

    protected $casts = [
        'width' => 'decimal:2',
        'length' => 'decimal:2',
        'original_square_feet' => 'decimal:2',
        'remaining_square_feet' => 'decimal:2',
        'remaining_percentage' => 'decimal:2',
        'received_date' => 'date'
    ];

    /**
     * Get the product that owns the fabric roll.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the supplier that provided the fabric roll.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Scope a query to only include active rolls.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include depleted rolls.
     */
    public function scopeDepleted($query)
    {
        return $query->where('status', 'depleted');
    }

    /**
     * Scope a query to only include damaged rolls.
     */
    public function scopeDamaged($query)
    {
        return $query->where('status', 'damaged');
    }

    /**
     * Calculate square feet from dimensions.
     */
    public static function calculateSquareFeet($width, $length)
    {
        // Calculate square feet (width and length are in feet)
        return $width * $length;
    }

    /**
     * Create a new fabric roll and record inventory movement.
     */
    public static function createRoll($data)
    {
        // Calculate square feet
        $squareFeet = self::calculateSquareFeet($data['width'], $data['length']);
        
        // Create the roll
        $roll = self::create([
            'product_id' => $data['product_id'],
            'roll_number' => $data['roll_number'] ?? 'R' . time() . rand(100, 999),
            'width' => $data['width'],
            'length' => $data['length'],
            'original_square_feet' => $squareFeet,
            'remaining_square_feet' => $squareFeet,
            'remaining_percentage' => 100,
            'supplier_id' => $data['supplier_id'] ?? null,
            'received_date' => $data['received_date'] ?? now(),
            'status' => 'active',
            'notes' => $data['notes'] ?? 'Initial roll creation'
        ]);

        // Record inventory movement using the recordStockIn method
        InventoryMovement::recordStockIn(
            $data['product_id'],
            1,
            'New fabric roll added: ' . $roll->roll_number,
            $roll->id,
            'App\\FabricRoll',
            auth()->id() ?? 1,
            $data['width'],
            $data['length']
        );

        // Update product total square feet
        $product = Product::find($data['product_id']);
        if ($product) {
            $product->total_square_feet = $product->total_square_feet + $squareFeet;
            $product->save();
        }

        return $roll;
    }

    /**
     * Use fabric from a roll and record inventory movement.
     */
    public function useFabric($squareFeetUsed, $notes = null)
    {
        // Make sure we don't use more than available
        $squareFeetUsed = min($squareFeetUsed, $this->remaining_square_feet);
        
        // Update remaining square feet
        $this->remaining_square_feet -= $squareFeetUsed;
        $this->remaining_percentage = ($this->remaining_square_feet / $this->original_square_feet) * 100;
        
        // Check if roll is depleted
        if ($this->remaining_square_feet <= 0) {
            $this->status = 'depleted';
            $this->remaining_square_feet = 0;
            $this->remaining_percentage = 0;
        }
        
        $this->save();

        // Record inventory movement using the recordStockOut method
        InventoryMovement::recordStockOut(
            $this->product_id,
            1,
            $notes ?? 'Fabric used from roll: ' . $this->roll_number,
            $this->id,
            'App\\FabricRoll',
            auth()->id() ?? 1,
            $this->width,
            $this->length
        );

        // Update product total square feet
        $product = $this->product;
        if ($product) {
            $product->total_square_feet = max(0, $product->total_square_feet - $squareFeetUsed);
            $product->save();
        }

        return $this;
    }

    /**
     * Mark a roll as damaged and record inventory movement.
     */
    public function markAsDamaged($notes = null)
    {
        $previousStatus = $this->status;
        $remainingSquareFeet = $this->remaining_square_feet;
        
        $this->status = 'damaged';
        $this->save();

        // Only record movement if the roll wasn't already depleted
        if ($previousStatus !== 'depleted' && $remainingSquareFeet > 0) {
            // Record inventory movement using the recordStockOut method
            InventoryMovement::recordStockOut(
                $this->product_id,
                1,
                $notes ?? 'Fabric roll marked as damaged: ' . $this->roll_number,
                $this->id,
                'App\\FabricRoll',
                auth()->id() ?? 1,
                $this->width,
                $this->length
            );

            // Update product total square feet
            $product = $this->product;
            if ($product) {
                $product->total_square_feet = max(0, $product->total_square_feet - $remainingSquareFeet);
                $product->save();
            }
        }

        return $this;
    }
}