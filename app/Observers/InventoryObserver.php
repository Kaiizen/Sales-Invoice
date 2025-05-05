<?php

namespace App\Observers;

use App\InventoryMovement;
use App\Notifications\LowStockNotification;
use App\User;
use Illuminate\Support\Facades\Session;

class InventoryObserver
{
    public function created(InventoryMovement $movement)
    {
        $product = $movement->product;
        
        // Check if stock is now below minimum after this movement
        if ($product->isLowStock()) {
            $this->notifyAdmins($product);
            
            // Add to session for UI alerts
            $lowStockProducts = Session::get('low_stock_products', []);
            if (!in_array($product->id, $lowStockProducts)) {
                $lowStockProducts[] = $product->id;
                Session::put('low_stock_products', $lowStockProducts);
            }
        }
    }

    protected function notifyAdmins($product)
    {
        $admins = User::where('is_admin', true)->get();
        
        foreach ($admins as $admin) {
            $admin->notify(new LowStockNotification($product));
        }
    }
}