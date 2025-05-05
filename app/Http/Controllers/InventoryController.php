<?php

namespace App\Http\Controllers;

use App\Product;
use App\FabricRoll;
use App\InventoryMovement;
use App\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Display the inventory dashboard.
     */
    public function dashboard()
    {
        // Get low stock products
        $lowStockProducts = Product::lowStock()->get();
        
        // Get out of stock products
        $outOfStockProducts = Product::outOfStock()->get();
        
        // Get low stock fabrics
        $lowStockFabrics = Product::where('is_fabric', true)
            ->where('track_by_roll', true)
            ->whereRaw('total_square_feet <= (total_square_feet * (alert_threshold_percent / 100))')
            ->where('total_square_feet', '>', 0)
            ->get();
            
        // Get recent movements
        $recentMovements = InventoryMovement::with(['product', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        // Get movement summary by day (last 30 days)
        $movementSummary = DB::table('inventory_movements')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(CASE WHEN movement_type = "in" THEN 1 ELSE 0 END) as in_count'),
                DB::raw('SUM(CASE WHEN movement_type = "out" THEN 1 ELSE 0 END) as out_count')
            )
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        return view('inventory.dashboard', compact(
            'lowStockProducts', 
            'outOfStockProducts', 
            'lowStockFabrics',
            'recentMovements',
            'movementSummary'
        ));
    }
    
    /**
     * Display inventory movements.
     */
    public function movements()
    {
        $movements = InventoryMovement::with(['product', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('inventory.movements', compact('movements'));
    }
    
    /**
     * Show the form for adjusting inventory.
     */
    public function adjustForm($productId)
    {
        $product = Product::findOrFail($productId);
        return view('inventory.adjust', compact('product'));
    }
    
    /**
     * Process inventory adjustment.
     */
    public function adjust(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        
        $request->validate([
            'quantity' => 'required|numeric',
            'notes' => 'nullable|string|max:255',
        ]);
        
        $newQuantity = $request->quantity;
        $currentQuantity = $product->current_stock;
        $difference = $newQuantity - $currentQuantity;
        
        if ($difference != 0) {
            // Determine movement type
            $movementType = $difference > 0 ? 'in' : 'out';
            $quantity = abs($difference);
            
            // Record movement
            InventoryMovement::create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_type' => $product->is_fabric && $product->track_by_roll ? 'square_feet' : 'piece',
                'amount' => null, // For simple adjustments, we don't need to specify amount
                'movement_type' => $movementType,
                'notes' => $request->notes ?? 'Manual inventory adjustment',
                'user_id' => auth()->id()
            ]);
            
            // Update product stock
            $product->current_stock = $newQuantity;
            $product->save();
            
            return redirect()->route('inventory.dashboard')
                ->with('success', 'Inventory adjusted successfully.');
        }
        
        return redirect()->route('inventory.dashboard')
            ->with('info', 'No change in inventory quantity.');
    }
    
    /**
     * Show the form for adding a fabric roll.
     */
    public function addFabricRollForm($productId)
    {
        $product = Product::findOrFail($productId);
        
        if (!$product->is_fabric || !$product->track_by_roll) {
            return redirect()->route('inventory.dashboard')
                ->with('error', 'This product is not a fabric tracked by roll.');
        }
        
        $suppliers = \App\Supplier::all();
        
        return view('inventory.add-fabric-roll', compact('product', 'suppliers'));
    }
    
    /**
     * Process adding a fabric roll.
     */
    public function addFabricRoll(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        
        if (!$product->is_fabric || !$product->track_by_roll) {
            return redirect()->route('inventory.dashboard')
                ->with('error', 'This product is not a fabric tracked by roll.');
        }
        
        $request->validate([
            'width' => 'required|numeric|min:0.01',
            'length' => 'required|numeric|min:0.01',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'received_date' => 'nullable|date',
            'notes' => 'nullable|string|max:255',
        ]);
        
        // Create the roll
        FabricRoll::createRoll([
            'product_id' => $product->id,
            'width' => $request->width,
            'length' => $request->length,
            'supplier_id' => $request->supplier_id,
            'received_date' => $request->received_date ?? now(),
            'notes' => $request->notes
        ]);
        
        return redirect()->route('inventory.dashboard')
            ->with('success', 'Fabric roll added successfully.');
    }
    
    /**
     * Show the form for using fabric from a roll.
     */
    public function useFabricForm($rollId)
    {
        $roll = FabricRoll::findOrFail($rollId);
        
        if ($roll->status !== 'active' || $roll->remaining_square_feet <= 0) {
            return redirect()->route('inventory.dashboard')
                ->with('error', 'This fabric roll cannot be used.');
        }
        
        return view('inventory.use-fabric', compact('roll'));
    }
    
    /**
     * Process using fabric from a roll.
     */
    public function useFabric(Request $request, $rollId)
    {
        $roll = FabricRoll::findOrFail($rollId);
        
        if ($roll->status !== 'active' || $roll->remaining_square_feet <= 0) {
            return redirect()->route('inventory.dashboard')
                ->with('error', 'This fabric roll cannot be used.');
        }
        
        $request->validate([
            'square_feet' => 'required|numeric|min:0.01|max:' . $roll->remaining_square_feet,
            'notes' => 'nullable|string|max:255',
        ]);
        
        // Use fabric from the roll
        $roll->useFabric($request->square_feet, $request->notes);
        
        return redirect()->route('inventory.dashboard')
            ->with('success', 'Fabric used successfully.');
    }
    
    /**
     * Show low stock products.
     */
    public function lowStock()
    {
        $lowStockProducts = Product::lowStock()->get();
        $lowStockFabrics = Product::where('is_fabric', true)
            ->where('track_by_roll', true)
            ->whereRaw('total_square_feet <= (total_square_feet * (alert_threshold_percent / 100))')
            ->where('total_square_feet', '>', 0)
            ->get();
            
        return view('inventory.low-stock', compact('lowStockProducts', 'lowStockFabrics'));
    }
    
    /**
     * Show out of stock products.
     */
    public function outOfStock()
    {
        $outOfStockProducts = Product::outOfStock()->get();
        $outOfStockFabrics = Product::where('is_fabric', true)
            ->where('track_by_roll', true)
            ->where('total_square_feet', '<=', 0)
            ->get();
            
        return view('inventory.out-of-stock', compact('outOfStockProducts', 'outOfStockFabrics'));
    }
    
    /**
     * Show inventory report.
     */
    public function report()
    {
        // Get inventory valuation by category
        $valuation = DB::table('products as p')
            ->join('categories as c', 'p.category_id', '=', 'c.id')
            ->leftJoin('product_suppliers as ps', 'p.id', '=', 'ps.product_id')
            ->select(
                'c.name as category_name',
                DB::raw('COUNT(p.id) as total_items'),
                DB::raw('SUM(p.current_stock * COALESCE(ps.price, 0)) as total_cost'),
                DB::raw('SUM(p.current_stock * p.sales_price) as total_retail')
            )
            ->groupBy('c.id', 'c.name')
            ->get();
            
        // Calculate totals
        $totals = [
            'items' => $valuation->sum('total_items'),
            'cost' => $valuation->sum('total_cost'),
            'retail' => $valuation->sum('total_retail')
        ];
        
        // Prepare data for category chart
        $categoryChart = [
            'labels' => $valuation->pluck('category_name')->toArray(),
            'data' => $valuation->pluck('total_retail')->toArray()
        ];
        
        // Get movement data for the last 30 days
        $movements = DB::table('inventory_movements')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(CASE WHEN movement_type = "in" THEN quantity ELSE 0 END) as in_qty'),
                DB::raw('SUM(CASE WHEN movement_type = "out" THEN quantity ELSE 0 END) as out_qty')
            )
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        // Prepare data for movement chart
        $movementChart = [
            'labels' => $movements->pluck('date')->toArray(),
            'in' => $movements->pluck('in_qty')->toArray(),
            'out' => $movements->pluck('out_qty')->toArray()
        ];
        
        return view('inventory.report', compact('valuation', 'totals', 'categoryChart', 'movementChart'));
    }
}