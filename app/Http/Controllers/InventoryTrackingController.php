<?php

namespace App\Http\Controllers;

use App\Category;
use App\CustomOrder;
use App\FabricRoll;
use App\FlagDetail;
use App\Invoice;
use App\InvoiceItem;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryTrackingController extends Controller
{
    /**
     * Display inventory tracking dashboard
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get fabric categories (those that track by square feet)
        $fabricCategories = Category::where('stock_type', Category::STOCK_TYPE_SQUARE_FEET)->get();
        
        // Get quantity-based categories
        $quantityCategories = Category::where('stock_type', Category::STOCK_TYPE_QUANTITY)->get();
        
        return view('inventory.tracking.index', compact('fabricCategories', 'quantityCategories'));
    }
    
    /**
     * Display fabric inventory and orders
     *
     * @return \Illuminate\Http\Response
     */
    public function fabricInventory()
    {
        // Get all fabric products
        $fabricProducts = Product::whereHas('category', function($query) {
            $query->where('stock_type', Category::STOCK_TYPE_SQUARE_FEET);
        })->orWhere('is_fabric', true)->get();
        
        // For each product, get active fabric rolls
        $productsWithRolls = [];
        
        foreach ($fabricProducts as $product) {
            $activeRolls = $product->fabricRolls()->active()->get();
            $totalRemainingSquareFeet = $activeRolls->sum('remaining_square_feet');
            
            // Get pending orders that use this fabric
            $pendingOrders = FlagDetail::where('product_id', $product->id)
                ->whereHas('customOrder', function($query) {
                    $query->whereIn('status', [
                        CustomOrder::STATUS_PENDING,
                        CustomOrder::STATUS_IN_PRODUCTION
                    ]);
                })
                ->get();
            
            $orderedSquareFeet = $pendingOrders->sum(function($detail) {
                return $detail->square_feet * $detail->quantity;
            });
            
            $productsWithRolls[] = [
                'product' => $product,
                'rolls' => $activeRolls,
                'total_remaining' => $totalRemainingSquareFeet,
                'ordered_square_feet' => $orderedSquareFeet,
                'available_after_orders' => $totalRemainingSquareFeet - $orderedSquareFeet
            ];
        }
        
        return view('inventory.tracking.fabric', compact('productsWithRolls'));
    }
    
    /**
     * Display quantity-based inventory and orders
     *
     * @return \Illuminate\Http\Response
     */
    public function quantityInventory()
    {
        // Get all quantity-based products
        $quantityProducts = Product::whereHas('category', function($query) {
            $query->where('stock_type', Category::STOCK_TYPE_QUANTITY);
        })->where('is_fabric', false)->get();
        
        $productsWithOrders = [];
        
        foreach ($quantityProducts as $product) {
            // Get current inventory
            $currentStock = $product->current_stock;
            
            // Get pending orders for this product
            $pendingOrderItems = InvoiceItem::where('product_id', $product->id)
                ->whereHas('invoice', function($query) {
                    $query->where('status', 'pending');
                })
                ->get();
            
            $orderedQuantity = $pendingOrderItems->sum('quantity');
            
            $productsWithOrders[] = [
                'product' => $product,
                'current_stock' => $currentStock,
                'ordered_quantity' => $orderedQuantity,
                'available_after_orders' => $currentStock - $orderedQuantity
            ];
        }
        
        return view('inventory.tracking.quantity', compact('productsWithOrders'));
    }
    
    /**
     * Display detailed view for a specific fabric product
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function fabricDetail($id)
    {
        $product = Product::findOrFail($id);
        
        // Check if product is fabric or in a square feet category
        $isFabric = $product->is_fabric || ($product->category && $product->category->tracksBySquareFeet());
        
        if (!$isFabric) {
            return redirect()->route('inventory.tracking.quantity.detail', $id);
        }
        
        // Get all rolls for this product
        $activeRolls = $product->fabricRolls()->active()->get();
        $depletedRolls = $product->fabricRolls()->depleted()->get();
        $damagedRolls = $product->fabricRolls()->damaged()->get();
        
        // Get pending orders that use this fabric
        $pendingOrders = FlagDetail::where('product_id', $product->id)
            ->whereHas('customOrder', function($query) {
                $query->whereIn('status', [
                    CustomOrder::STATUS_PENDING,
                    CustomOrder::STATUS_IN_PRODUCTION
                ]);
            })
            ->with('customOrder')
            ->get();
        
        // Get completed orders
        $completedOrders = FlagDetail::where('product_id', $product->id)
            ->whereHas('customOrder', function($query) {
                $query->whereIn('status', [
                    CustomOrder::STATUS_READY,
                    CustomOrder::STATUS_DELIVERED
                ]);
            })
            ->with('customOrder')
            ->get();
        
        // Calculate totals
        $totalRemainingSquareFeet = $activeRolls->sum('remaining_square_feet');
        $orderedSquareFeet = $pendingOrders->sum(function($detail) {
            return $detail->square_feet * $detail->quantity;
        });
        
        return view('inventory.tracking.fabric-detail', compact(
            'product',
            'activeRolls',
            'depletedRolls',
            'damagedRolls',
            'pendingOrders',
            'completedOrders',
            'totalRemainingSquareFeet',
            'orderedSquareFeet'
        ));
    }
    
    /**
     * Display detailed view for a specific quantity-based product
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function quantityDetail($id)
    {
        $product = Product::findOrFail($id);
        
        // Check if product is quantity-based
        $isQuantityBased = !$product->is_fabric && (!$product->category || $product->category->tracksByQuantity());
        
        if (!$isQuantityBased) {
            return redirect()->route('inventory.tracking.fabric.detail', $id);
        }
        
        // Get current inventory
        $currentStock = $product->current_stock;
        
        // Get inventory movements
        $movements = $product->inventoryMovements()
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get pending orders for this product
        $pendingOrderItems = InvoiceItem::where('product_id', $product->id)
            ->whereHas('invoice', function($query) {
                $query->where('status', 'pending');
            })
            ->with('invoice')
            ->get();
        
        // Get completed orders
        $completedOrderItems = InvoiceItem::where('product_id', $product->id)
            ->whereHas('invoice', function($query) {
                $query->whereIn('status', ['paid', 'completed']);
            })
            ->with('invoice')
            ->get();
        
        // Calculate totals
        $orderedQuantity = $pendingOrderItems->sum('quantity');
        
        return view('inventory.tracking.quantity-detail', compact(
            'product',
            'currentStock',
            'movements',
            'pendingOrderItems',
            'completedOrderItems',
            'orderedQuantity'
        ));
    }
}