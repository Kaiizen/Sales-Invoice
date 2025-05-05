<?php

namespace App\Observers;

use App\FlagDetail;

class FlagDetailObserver
{
    /**
     * Handle the flag detail "created" event.
     *
     * @param  \App\FlagDetail  $flagDetail
     * @return void
     */
    public function created(FlagDetail $flagDetail)
    {
        // Deduct fabric from inventory when flag detail is created
        $flagDetail->deductFabricInventory();
    }

    /**
     * Handle the flag detail "updated" event.
     *
     * @param  \App\FlagDetail  $flagDetail
     * @return void
     */
    public function updated(FlagDetail $flagDetail)
    {
        // If square feet or quantity changed, we need to adjust inventory
        if ($flagDetail->isDirty('square_feet') || $flagDetail->isDirty('quantity')) {
            // Calculate the difference in square feet
            $oldSquareFeet = $flagDetail->getOriginal('square_feet') * $flagDetail->getOriginal('quantity');
            $newSquareFeet = $flagDetail->square_feet * $flagDetail->quantity;
            $difference = $newSquareFeet - $oldSquareFeet;
            
            // If there's a difference and we have a product, adjust inventory
            if ($difference != 0 && $flagDetail->product_id) {
                $product = $flagDetail->product;
                if ($product && $product->is_fabric) {
                    // If difference is positive, we need more fabric
                    if ($difference > 0) {
                        $product->recordFabricSale(
                            $difference, 
                            $flagDetail->customOrder, 
                            "Additional fabric used for flag: {$flagDetail->flag_type}"
                        );
                    } 
                    // If difference is negative, we're returning fabric (this might not be physically possible)
                    else {
                        // For accounting purposes, we'll record this as an adjustment
                        $product->recordAdjustment(
                            abs($difference),
                            "Adjustment from flag update: {$flagDetail->flag_type}"
                        );
                    }
                }
            }
        }
    }
}