<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'supplier_id',
        'date',
        'total_amount',
        'notes'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class);
    }
    
    /**
     * Calculate the total amount of the purchase.
     */
    public function calculateTotal()
    {
        $total = $this->purchaseDetails()->sum('amount');
        $this->total_amount = $total;
        $this->save();
        
        return $total;
    }
    
    /**
     * Update inventory for all products in this purchase.
     */
    public function updateInventory()
    {
        foreach ($this->purchaseDetails as $detail) {
            $product = $detail->product;
            
            // Skip fabric products tracked by roll as they're handled separately
            if ($product->is_fabric && $product->track_by_roll) {
                continue;
            }
            
            $product->stockIn(
                $detail->qty,
                'Purchase #' . $this->id,
                $this->id,
                'App\\Purchase'
            );
        }
        
        return $this;
    }
}

