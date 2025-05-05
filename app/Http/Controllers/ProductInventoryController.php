<?php

namespace App\Http\Controllers;

use App\Product;
use App\InventoryMovement;
use App\InventoryLocation;
use App\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductInventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display inventory management page for a specific product
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function manage($id)
    {
        $product = Product::with(['category', 'unit'])->findOrFail($id);
        
        // Redirect fabric products to their specific management page
        if ($product->is_fabric && $product->track_by_roll) {
            return redirect()->route('fabric-roll.by-product', $product->id);
        }
        
        $movements = InventoryMovement::where('product_id', $id)
            ->with('user')
            ->latest()
            ->paginate(10);
            
        $warehouses = Warehouse::all();
        $locations = InventoryLocation::all();
        
        return view('inventory.product.manage', compact('product', 'movements', 'warehouses', 'locations'));
    }
    
    /**
     * Add stock to a product
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function addStock(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'location_id' => 'nullable|exists:inventory_locations,id',
            'notes' => 'nullable|string|max:255'
        ]);
        
        $product = Product::findOrFail($id);
        
        // Don't allow adding stock to fabric products through this method
        if ($product->is_fabric && $product->track_by_roll) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['quantity' => 'Fabric products must be added as rolls.']);
        }
        
        DB::transaction(function () use ($product, $request) {
            // Update product stock
            $product->increment('current_stock', $request->quantity);
            
            // If location is specified, update location stock
            if ($request->location_id) {
                $inventory = $product->inventoryLocations()
                    ->firstOrCreate(
                        ['location_id' => $request->location_id],
                        ['quantity' => 0, 'reserved_quantity' => 0]
                    );
                    
                $inventory->increment('quantity', $request->quantity);
            }
            
            // Record movement
            InventoryMovement::create([
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'movement_type' => 'in',
                'notes' => $request->notes ?? 'Stock added manually',
                'user_id' => Auth::id()
            ]);
        });
        
        return redirect()->route('inventory.product.manage', $product->id)
            ->with('success', 'Stock added successfully');
    }
    
    /**
     * Remove stock from a product
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function removeStock(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'location_id' => 'nullable|exists:inventory_locations,id',
            'notes' => 'nullable|string|max:255'
        ]);
        
        $product = Product::findOrFail($id);
        
        // Don't allow removing stock from fabric products through this method
        if ($product->is_fabric && $product->track_by_roll) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['quantity' => 'Fabric products must be managed through the fabric roll interface.']);
        }
        
        // Check if there's enough stock
        if ($product->current_stock < $request->quantity) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['quantity' => 'Not enough stock available.']);
        }
        
        // If location is specified, check if there's enough stock at that location
        if ($request->location_id) {
            $inventory = $product->inventoryLocations()
                ->where('location_id', $request->location_id)
                ->first();
                
            if (!$inventory || $inventory->available_quantity < $request->quantity) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['quantity' => 'Not enough stock available at the selected location.']);
            }
        }
        
        DB::transaction(function () use ($product, $request) {
            // Update product stock
            $product->decrement('current_stock', $request->quantity);
            
            // If location is specified, update location stock
            if ($request->location_id) {
                $inventory = $product->inventoryLocations()
                    ->where('location_id', $request->location_id)
                    ->first();
                    
                if ($inventory) {
                    $inventory->decrement('quantity', $request->quantity);
                }
            }
            
            // Record movement
            InventoryMovement::create([
                'product_id' => $product->id,
                'quantity' => -$request->quantity,
                'movement_type' => 'adjustment',
                'notes' => $request->notes ?? 'Stock removed manually',
                'user_id' => Auth::id()
            ]);
        });
        
        return redirect()->route('inventory.product.manage', $product->id)
            ->with('success', 'Stock removed successfully');
    }
    
    /**
     * Transfer stock between locations
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function transferStock(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'from_location_id' => 'required|exists:inventory_locations,id',
            'to_location_id' => 'required|exists:inventory_locations,id|different:from_location_id',
            'notes' => 'nullable|string|max:255'
        ]);
        
        $product = Product::findOrFail($id);
        
        try {
            $product->transferStock(
                $request->from_location_id,
                $request->to_location_id,
                $request->quantity,
                Auth::id()
            );
            
            return redirect()->route('inventory.product.manage', $product->id)
                ->with('success', 'Stock transferred successfully');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['quantity' => $e->getMessage()]);
        }
    }
    
    /**
     * Display a list of products for inventory management
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::where('is_fabric', false)
            ->orWhere('track_by_roll', false)
            ->with(['category', 'unit'])
            ->orderBy('name')
            ->paginate(15);
            
        return view('inventory.product.index', compact('products'));
    }
}