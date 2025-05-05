<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FlagDetail extends Model
{
    protected $fillable = [
        'custom_order_id',
        'product_id',
        'flag_type',
        'height',
        'breadth',
        'square_feet',
        'price_per_square_feet',
        'quantity',
        'stitching',
        'total_price',
        'notes'
    ];
    
    /**
     * Validation rules for flag details
     */
    public static $rules = [
        'custom_order_id' => 'required|exists:custom_orders,id',
        'product_id' => 'nullable|exists:products,id',
        'flag_type' => 'required|in:flag,product',
        'height' => 'required_if:flag_type,flag|numeric|min:0',
        'breadth' => 'required_if:flag_type,flag|numeric|min:0',
        'square_feet' => 'required_if:flag_type,flag|numeric|min:0',
        'price_per_square_feet' => 'required|numeric|min:0',
        'quantity' => 'required|integer|min:1',
        'stitching' => 'boolean',
        'total_price' => 'required|numeric|min:0',
        'notes' => 'nullable|string|max:255'
    ];

    public function customOrder()
    {
        return $this->belongsTo(CustomOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate square feet based on height and breadth
     *
     * @return float
     */
    public function calculateSquareFeet()
    {
        if ($this->height && $this->breadth) {
            // If flag_type is 'flag', calculate square feet
            if ($this->flag_type === 'flag') {
                $this->square_feet = $this->height * $this->breadth;
                return $this->square_feet;
            }
        }
        
        // For non-flag items or missing dimensions, set square feet to 0
        $this->square_feet = 0;
        return 0;
    }

    /**
     * Calculate total price based on dimensions and quantity
     *
     * @return float
     */
    public function calculateTotalPrice()
    {
        if ($this->flag_type === 'flag' && $this->square_feet && $this->price_per_square_feet) {
            // For fabric/flag items, calculate based on square feet
            $this->total_price = $this->square_feet * $this->price_per_square_feet * $this->quantity;
            return $this->total_price;
        } elseif ($this->flag_type === 'product' && $this->price_per_square_feet) {
            // For product items, calculate based on unit price
            $this->total_price = $this->price_per_square_feet * $this->quantity;
            return $this->total_price;
        }
        
        return 0;
    }
    
    /**
     * Boot method to set up model event listeners
     */
    protected static function boot()
    {
        parent::boot();
        
        // Before saving, calculate square feet and total price
        static::saving(function ($model) {
            if ($model->flag_type === 'flag') {
                $model->calculateSquareFeet();
            } else {
                $model->square_feet = 0;
            }
            
            $model->calculateTotalPrice();
        });
    }

    /**
     * Deduct fabric from inventory when flag detail is created
     */
    public function deductFabricInventory()
    {
        if (!$this->product_id || !$this->square_feet) {
            return;
        }

        $product = $this->product;
        
        // Check if product belongs to a flag fabric category that tracks by square feet
        $trackBySquareFeet = $product && $product->category && $product->category->tracksBySquareFeet();
        
        if (($product && $product->is_fabric) || $trackBySquareFeet) {
            try {
                // Try to use fabric from rolls first
                $product->useFabricFromRolls(
                    $this->square_feet * $this->quantity,
                    $this->customOrder,
                    "Fabric used for flag: {$this->flag_type}"
                );
            } catch (\Exception $e) {
                // Fall back to the old method if rolls are not available
                $product->recordFabricSale(
                    $this->square_feet * $this->quantity,
                    $this->customOrder,
                    "Fabric used for flag: {$this->flag_type}"
                );
            }
        }
    }
}