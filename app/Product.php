<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'code',
        'model',
        'serial_number',
        'description',
        'category_id',
        'unit_id',
        'tax_id',
        'image',
        'current_stock',
        'minimum_stock',
        'barcode',
        'location',
        'is_active',
        'is_fabric',
        'track_by_roll',
        'roll_width',
        'roll_length',
        'total_square_feet',
        'alert_threshold_percent',
        'sales_price'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_fabric' => 'boolean',
        'track_by_roll' => 'boolean',
        'roll_width' => 'decimal:2',
        'roll_length' => 'decimal:2',
        'total_square_feet' => 'decimal:2',
        'alert_threshold_percent' => 'integer',
        'current_stock' => 'integer',
        'minimum_stock' => 'integer',
        'sales_price' => 'decimal:2'
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            // Generate slug if not provided
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            
            // Set default value for alert_threshold_percent if not provided
            if ($product->alert_threshold_percent === null) {
                $product->alert_threshold_percent = 20; // Default to 20%
            }
            
            // Set default values for fabric-related fields if not provided
            if ($product->total_square_feet === null) {
                $product->total_square_feet = 0;
            }
            
            if ($product->roll_width === null) {
                $product->roll_width = 0;
            }
            
            if ($product->roll_length === null) {
                $product->roll_length = 0;
            }
        });
    }

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the unit that owns the product.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the tax that owns the product.
     */
    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    /**
     * Get the additional product information.
     */
    public function additionalProduct()
    {
        return $this->hasMany(ProductSupplier::class);
    }

    /**
     * Get the fabric rolls for this product.
     */
    public function fabricRolls()
    {
        return $this->hasMany(FabricRoll::class);
    }

    /**
     * Get the inventory movements for this product.
     */
    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /**
     * Scope a query to only include low stock products.
     */
    public function scopeLowStock($query)
    {
        return $query->where('is_fabric', false)
            ->where('current_stock', '>', 0)
            ->whereRaw('current_stock <= minimum_stock');
    }

    /**
     * Scope a query to only include out of stock products.
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('is_fabric', false)
            ->where('current_stock', '<=', 0);
    }

    /**
     * Check if the product is low on stock.
     */
    public function isLowStock()
    {
        if ($this->is_fabric && $this->track_by_roll) {
            return $this->total_square_feet > 0 && 
                   $this->total_square_feet <= ($this->total_square_feet * ($this->alert_threshold_percent / 100));
        }
        
        return $this->current_stock > 0 && $this->current_stock <= $this->minimum_stock;
    }

    /**
     * Check if the product is out of stock.
     */
    public function isOutOfStock()
    {
        if ($this->is_fabric && $this->track_by_roll) {
            return $this->total_square_feet <= 0;
        }
        
        return $this->current_stock <= 0;
    }

    /**
     * Get the stock status.
     */
    public function getStockStatusAttribute()
    {
        if ($this->isOutOfStock()) {
            return 'out_of_stock';
        } elseif ($this->isLowStock()) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }

    /**
     * Get the remaining square feet percentage for fabric products.
     */
    public function getRemainingSquareFeetPercentageAttribute()
    {
        if (!$this->is_fabric || !$this->track_by_roll || $this->total_square_feet <= 0) {
            return 0;
        }
        
        // Calculate based on alert threshold
        $threshold = $this->alert_threshold_percent;
        $percentage = ($this->total_square_feet / ($this->total_square_feet * (100 / $threshold))) * 100;
        
        return min(100, $percentage);
    }

    /**
     * Record stock in.
     */
    public function stockIn($quantity, $notes = null, $reference_id = null, $reference_type = null, $width = null, $length = null)
    {
        // Record movement
        InventoryMovement::recordStockIn(
            $this->id,
            $quantity,
            $notes,
            $reference_id,
            $reference_type,
            null, // user_id
            $width ?? $this->roll_width,
            $length ?? $this->roll_length
        );
        
        // Update stock
        if (!$this->is_fabric || !$this->track_by_roll) {
            $this->current_stock += $quantity;
            $this->save();
        }
        
        return $this;
    }

    /**
     * Record stock out.
     */
    public function stockOut($quantity, $notes = null, $reference_id = null, $reference_type = null, $width = null, $length = null)
    {
        // Make sure we don't take out more than available
        $quantity = min($quantity, $this->current_stock);
        
        // Record movement
        InventoryMovement::recordStockOut(
            $this->id,
            $quantity,
            $notes,
            $reference_id,
            $reference_type,
            null, // user_id
            $width ?? $this->roll_width,
            $length ?? $this->roll_length
        );
        
        // Update stock
        if (!$this->is_fabric || !$this->track_by_roll) {
            $this->current_stock -= $quantity;
            $this->save();
        }
        
        return $this;
    }
    
    /**
     * Add a fabric roll to this product.
     *
     * @param float $width Width in feet
     * @param float $length Length in feet
     * @param int|null $supplierId Supplier ID
     * @param string|null $notes Notes
     * @param int|null $locationId Location ID
     * @return \App\FabricRoll
     */
    public function addFabricRoll($width, $length, $supplierId = null, $notes = null, $locationId = null, $supplierId2 = null)
    {
        // Use the second supplier ID if provided (for backward compatibility)
        $finalSupplierId = $supplierId2 ?? $supplierId;
        
        // Create the fabric roll
        $fabricRoll = FabricRoll::createRoll([
            'product_id' => $this->id,
            'width' => $width,
            'length' => $length,
            'supplier_id' => $finalSupplierId,
            'received_date' => now(),
            'notes' => $notes ?? 'Added via Product::addFabricRoll'
        ]);
        
        return $fabricRoll;
    }
    
    /**
     * Use fabric from rolls.
     *
     * @param float $squareFeet Square feet to use
     * @param int|null $rollId Specific roll ID to use, or null to use from any available roll
     * @param string|null $notes Notes
     * @return bool
     */
    public function useFabricFromRolls($squareFeet, $rollId = null, $notes = null)
    {
        if (!$this->is_fabric && !$this->track_by_roll) {
            throw new \Exception('This product is not a fabric roll product');
        }
        
        if ($rollId) {
            // Use from specific roll
            $roll = FabricRoll::where('id', $rollId)
                ->where('product_id', $this->id)
                ->where('status', 'active')
                ->first();
                
            if (!$roll) {
                throw new \Exception('Fabric roll not found or not active');
            }
            
            $roll->useFabric($squareFeet, $notes);
            return true;
        } else {
            // Use from any available roll
            $remainingToUse = $squareFeet;
            
            // Get active rolls ordered by oldest first (FIFO)
            $rolls = $this->fabricRolls()
                ->where('status', 'active')
                ->orderBy('received_date', 'asc')
                ->get();
                
            if ($rolls->isEmpty()) {
                throw new \Exception('No active fabric rolls available');
            }
            
            foreach ($rolls as $roll) {
                if ($remainingToUse <= 0) {
                    break;
                }
                
                $toUseFromRoll = min($remainingToUse, $roll->remaining_square_feet);
                $roll->useFabric($toUseFromRoll, $notes);
                $remainingToUse -= $toUseFromRoll;
            }
            
            if ($remainingToUse > 0) {
                throw new \Exception('Not enough fabric available. Short by ' . $remainingToUse . ' square feet');
            }
            
            return true;
        }
    }
}
