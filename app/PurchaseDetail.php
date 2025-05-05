<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseDetail extends Model
{
    protected $fillable = [
        'purchase_id',
        'supplier_id',
        'product_id',
        'qty',
        'price',
        'discount',
        'amount'
    ];

    /**
     * Get the purchase that owns the purchase detail.
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Get the supplier that owns the purchase detail.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the product that owns the purchase detail.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Check if this purchase detail is for a fabric product tracked by roll.
     */
    public function isFabricRoll()
    {
        return $this->product && $this->product->is_fabric && $this->product->track_by_roll;
    }
    
    /**
     * Get the fabric rolls associated with this purchase detail.
     */
    public function fabricRolls()
    {
        if (!$this->isFabricRoll()) {
            return collect();
        }
        
        return FabricRoll::where('product_id', $this->product_id)
            ->where('notes', 'like', '%Purchase #' . $this->purchase_id . '%')
            ->get();
    }
}