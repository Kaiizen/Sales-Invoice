<?php

namespace App\Observers;

use App\InventoryMovement;
use App\Product;
use Illuminate\Support\Facades\Auth;

class ProductObserver
{
    public function updating(Product $product)
    {
        // Track stock changes
        if ($product->isDirty('current_stock')) {
            $originalStock = $product->getOriginal('current_stock');
            $newStock = $product->current_stock;
            $difference = $newStock - $originalStock;

            if ($difference != 0) {
                InventoryMovement::create([
                    'product_id' => $product->id,
                    'quantity' => $difference,
                    'movement_type' => 'adjustment',
                    'notes' => 'Automatic stock adjustment',
                    'user_id' => Auth::id() ?? 1
                ]);
            }
        }
    }

    public function created(Product $product)
    {
        if ($product->current_stock > 0) {
            InventoryMovement::create([
                'product_id' => $product->id,
                'quantity' => $product->current_stock,
                'movement_type' => 'in',
                'notes' => 'Initial stock',
                'user_id' => Auth::id() ?? 1
            ]);
        }
    }
}