<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InventoryMovement extends Model
{
    protected $fillable = [
        'product_id', 
        'quantity', 
        'unit_type',
        'amount',
        'movement_type', 
        'reference_id', 
        'reference_type', 
        'notes', 
        'user_id'
    ];

    /**
     * Get the product associated with the movement.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who created the movement.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the polymorphic relation.
     */
    public function reference()
    {
        return $this->morphTo();
    }

    /**
     * Get the formatted movement type name.
     */
    public function getMovementTypeNameAttribute()
    {
        $types = [
            'in' => 'Stock In',
            'out' => 'Stock Out'
        ];

        return $types[$this->movement_type] ?? ucfirst($this->movement_type);
    }

    /**
     * Scope a query to only include stock in movements.
     */
    public function scopeStockIn($query)
    {
        return $query->where('movement_type', 'in');
    }

    /**
     * Scope a query to only include stock out movements.
     */
    public function scopeStockOut($query)
    {
        return $query->where('movement_type', 'out');
    }

    /**
     * Record a stock in movement.
     */
    public static function recordStockIn($product_id, $quantity, $notes = null, $reference_id = null, $reference_type = null, $user_id = null, $width = null, $length = null)
    {
        $product = Product::findOrFail($product_id);
        $amount = null;
        $unit_type = 'piece';

        // If it's a fabric product tracked by roll, we need to record square feet
        if ($product->is_fabric && $product->track_by_roll) {
            $unit_type = 'square_feet';
            
            // Use provided dimensions if available, otherwise use product defaults
            $rollWidth = $width ?? $product->roll_width;
            $rollLength = $length ?? $product->roll_length;
            $amount = $rollWidth * $rollLength; // Calculate square feet (dimensions are in feet)
        }

        // Get the current movement_type values from the database
        $columns = DB::select("SHOW COLUMNS FROM inventory_movements WHERE Field = 'movement_type'");
        $type = $columns[0]->Type;
        preg_match("/^enum\((.*)\)$/", $type, $matches);
        $values = array();
        
        if (isset($matches[1])) {
            foreach(explode(',', $matches[1]) as $value) {
                $values[] = trim($value, "'");
            }
        }
        
        // Determine the appropriate movement_type value
        $movementType = 'in';
        if (in_array('stock_in', $values)) {
            $movementType = 'stock_in';
        }

        return self::create([
            'product_id' => $product_id,
            'quantity' => $quantity,
            'unit_type' => $unit_type,
            'amount' => $amount,
            'movement_type' => $movementType,
            'reference_id' => $reference_id,
            'reference_type' => $reference_type,
            'notes' => $notes,
            'user_id' => $user_id ?? auth()->id()
        ]);
    }

    /**
     * Record a stock out movement.
     */
    public static function recordStockOut($product_id, $quantity, $notes = null, $reference_id = null, $reference_type = null, $user_id = null, $width = null, $length = null)
    {
        $product = Product::findOrFail($product_id);
        $amount = null;
        $unit_type = 'piece';

        // If it's a fabric product tracked by roll, we need to record square feet
        if ($product->is_fabric && $product->track_by_roll) {
            $unit_type = 'square_feet';
            
            // Use provided dimensions if available, otherwise use product defaults
            $rollWidth = $width ?? $product->roll_width;
            $rollLength = $length ?? $product->roll_length;
            $amount = $rollWidth * $rollLength; // Calculate square feet (dimensions are in feet)
        }

        // Get the current movement_type values from the database
        $columns = DB::select("SHOW COLUMNS FROM inventory_movements WHERE Field = 'movement_type'");
        $type = $columns[0]->Type;
        preg_match("/^enum\((.*)\)$/", $type, $matches);
        $values = array();
        
        if (isset($matches[1])) {
            foreach(explode(',', $matches[1]) as $value) {
                $values[] = trim($value, "'");
            }
        }
        
        // Determine the appropriate movement_type value
        $movementType = 'out';
        if (in_array('stock_out', $values)) {
            $movementType = 'stock_out';
        }

        return self::create([
            'product_id' => $product_id,
            'quantity' => $quantity,
            'unit_type' => $unit_type,
            'amount' => $amount,
            'movement_type' => $movementType,
            'reference_id' => $reference_id,
            'reference_type' => $reference_type,
            'notes' => $notes,
            'user_id' => $user_id ?? auth()->id()
        ]);
    }
}