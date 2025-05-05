<?php

namespace App\Services;

use App\Product;
use App\Supplier;
use App\SupplierOrder;
use App\SupplierOrderItem;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OrderRequiresApprovalNotification;

class AutoOrderService
{
    /**
     * Create an auto-generated order for a product
     *
     * @param Product $product
     * @return SupplierOrder|false
     */
    public function createOrder(Product $product)
    {
        // Check if auto-reordering is enabled for this product
        if (!$product->auto_reorder) {
            return false;
        }
        
        // Get preferred supplier
        $supplier = $product->preferredSupplier();
        
        if (!$supplier) {
            return false;
        }
        
        // Calculate order quantity based on forecasts
        $recommendedLevel = $product->recommended_stock_level;
        $orderQuantity = $recommendedLevel - $product->current_stock;
        
        if ($orderQuantity <= 0) {
            return false;
        }
        
        // Create supplier order
        return DB::transaction(function () use ($product, $supplier, $orderQuantity) {
            $order = SupplierOrder::create([
                'supplier_id' => $supplier->id,
                'status' => SupplierOrder::STATUS_PENDING_APPROVAL,
                'is_auto_generated' => true,
                'notes' => 'Auto-generated order due to low stock',
                'created_by' => 1 // System user
            ]);
            
            // Add product to order
            $item = SupplierOrderItem::create([
                'supplier_order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $orderQuantity,
                'unit_price' => $product->cost_price ?? $product->price * 0.7, // Default to 70% of selling price if cost price not set
                'total_price' => ($product->cost_price ?? $product->price * 0.7) * $orderQuantity,
                'received_quantity' => 0
            ]);
            
            // Update order total
            $order->calculateTotal();
            
            // Notify purchasing managers for approval
            $this->notifyPurchasingManagers($order);
            
            return $order;
        });
    }
    
    /**
     * Check all products and create orders for those that need reordering
     *
     * @return array Created orders
     */
    public function checkAllProducts()
    {
        $products = Product::where('is_active', true)
            ->where('auto_reorder', true)
            ->get();
            
        $createdOrders = [];
        
        foreach ($products as $product) {
            if ($product->needsReordering()) {
                $order = $this->createOrder($product);
                
                if ($order) {
                    $createdOrders[] = $order;
                }
            }
        }
        
        return $createdOrders;
    }
    
    /**
     * Group products by supplier and create consolidated orders
     *
     * @param array $productIds
     * @return array Created orders
     */
    public function createConsolidatedOrders(array $productIds = [])
    {
        $query = Product::where('is_active', true)
            ->where('auto_reorder', true);
            
        if (!empty($productIds)) {
            $query->whereIn('id', $productIds);
        }
        
        $products = $query->get();
        
        // Group products by supplier
        $supplierProducts = [];
        
        foreach ($products as $product) {
            if ($product->needsReordering() && $product->supplier_id) {
                if (!isset($supplierProducts[$product->supplier_id])) {
                    $supplierProducts[$product->supplier_id] = [];
                }
                
                $supplierProducts[$product->supplier_id][] = $product;
            }
        }
        
        $createdOrders = [];
        
        // Create one order per supplier
        foreach ($supplierProducts as $supplierId => $products) {
            $supplier = Supplier::find($supplierId);
            
            if (!$supplier) {
                continue;
            }
            
            $order = $this->createSupplierOrder($supplier, $products);
            
            if ($order) {
                $createdOrders[] = $order;
            }
        }
        
        return $createdOrders;
    }
    
    /**
     * Create an order for multiple products from the same supplier
     *
     * @param Supplier $supplier
     * @param array $products
     * @return SupplierOrder|false
     */
    protected function createSupplierOrder(Supplier $supplier, array $products)
    {
        if (empty($products)) {
            return false;
        }
        
        return DB::transaction(function () use ($supplier, $products) {
            $order = SupplierOrder::create([
                'supplier_id' => $supplier->id,
                'status' => SupplierOrder::STATUS_PENDING_APPROVAL,
                'is_auto_generated' => true,
                'notes' => 'Auto-generated consolidated order',
                'created_by' => 1 // System user
            ]);
            
            // Add products to order
            foreach ($products as $product) {
                $orderQuantity = $product->recommended_stock_level - $product->current_stock;
                
                if ($orderQuantity <= 0) {
                    continue;
                }
                
                SupplierOrderItem::create([
                    'supplier_order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $orderQuantity,
                    'unit_price' => $product->cost_price ?? $product->price * 0.7,
                    'total_price' => ($product->cost_price ?? $product->price * 0.7) * $orderQuantity,
                    'received_quantity' => 0
                ]);
            }
            
            // If no items were added, delete the order and return false
            if ($order->items()->count() == 0) {
                $order->delete();
                return false;
            }
            
            // Update order total
            $order->calculateTotal();
            
            // Notify purchasing managers for approval
            $this->notifyPurchasingManagers($order);
            
            return $order;
        });
    }
    
    /**
     * Notify purchasing managers about an order requiring approval
     *
     * @param SupplierOrder $order
     * @return void
     */
    protected function notifyPurchasingManagers(SupplierOrder $order)
    {
        // In a real implementation, you would use roles to find purchasing managers
        // For now, we'll notify all admin users
        $purchasingManagers = User::where('is_admin', true)->get();
        
        if ($purchasingManagers->isEmpty()) {
            // Fallback to the first user if no admins
            $purchasingManagers = User::first() ? [User::first()] : [];
        }
        
        // Create the notification
        $notification = new OrderRequiresApprovalNotification($order);
        
        // Send to all purchasing managers
        foreach ($purchasingManagers as $manager) {
            $manager->notify($notification);
        }
    }
}