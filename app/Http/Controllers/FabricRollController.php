<?php

namespace App\Http\Controllers;

use App\Category;
use App\Product;
use App\ProductSupplier;
use App\Supplier;
use App\Tax;
use App\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FabricRollController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the form for creating a new fabric roll.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $suppliers = Supplier::all();
        $categories = $this->getCategoriesForDropdown();
        $taxes = Tax::all();
        
        // Find or create Square Feet unit
        $squareFeetUnit = Unit::firstOrCreate(
            ['name' => 'Square Feet'],
            ['slug' => 'sq-ft', 'status' => true]
        );
        
        $units = Unit::all();
        $defaultUnitId = $squareFeetUnit->id;

        return view('product.create-fabric-roll', compact('categories', 'taxes', 'units', 'suppliers', 'defaultUnitId'));
    }

    /**
     * Store a newly created fabric roll in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3|regex:/^[a-zA-Z0-9 ]+$/',
            'serial_number' => 'required',
            'model' => 'required|min:3',
            'category_id' => 'required',
            'sales_price' => 'required',
            'unit_id' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'tax_id' => 'required',
            'roll_width' => 'required|numeric|min:0.01',
            'roll_length' => 'required|numeric|min:0.01',
            'number_of_rolls' => 'required|integer|min:1',
            'alert_threshold_percent' => 'required|integer|min:1|max:100',
            'supplier_id.*' => 'required',
            'supplier_price.*' => 'required|numeric|min:0',
        ]);

        // Validate that the selected category is a parent category
        if (!$this->isParentCategory($request->category_id)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['category_id' => 'Products can only be added to parent categories.']);
        }

        // Find or create Square Feet unit
        $squareFeetUnit = Unit::firstOrCreate(
            ['name' => 'Square Feet'],
            ['slug' => 'sq-ft', 'status' => true]
        );

        // Create the product with fabric roll settings
        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->serial_number = $request->serial_number;
        $product->model = $request->model;
        $product->category_id = $request->category_id;
        $product->sales_price = $request->sales_price;
        $product->unit_id = $squareFeetUnit->id; // Always use Square Feet unit
        $product->tax_id = $request->tax_id;
        
        // Set fabric-specific fields
        $product->is_fabric = true;
        $product->track_by_roll = true;
        $product->roll_width = $request->roll_width;
        $product->roll_length = $request->roll_length;
        $product->alert_threshold_percent = $request->alert_threshold_percent;
        
        // Calculate square feet (width and length are in feet)
        $squareFeetPerRoll = $request->roll_width * $request->roll_length;
        $numberOfRolls = $request->number_of_rolls;
        $totalSquareFeet = $squareFeetPerRoll * $numberOfRolls;
        $product->total_square_feet = $totalSquareFeet;
        
        // Set inventory fields
        $product->current_stock = $numberOfRolls; // Number of rolls
        $product->minimum_stock = 1;
        // auto_reorder column doesn't exist in the database

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();        
            $image->move(public_path('images/product/'), $imageName);
            $product->image = $imageName;
        } else {
            $product->image = 'default.jpg'; // Set a default image name
        }

        $product->save();
        
        // Add the fabric rolls
        for ($i = 0; $i < $numberOfRolls; $i++) {
            $product->addFabricRoll(
                $request->roll_width,
                $request->roll_length,
                null,
                'Roll #' . ($i + 1) . ' added during fabric roll creation'
            );
        }

        // Add supplier information
        foreach($request->supplier_id as $key => $supplier_id){
            $supplier = new ProductSupplier();
            $supplier->product_id = $product->id;
            $supplier->supplier_id = $request->supplier_id[$key];
            $supplier->price = $request->supplier_price[$key];
            $supplier->save();
        }

        return redirect()->route('products.index')->with('message', 'New fabric roll has been added successfully');
    }

    /**
     * Get parent categories formatted for dropdown
     *
     * @return array
     */
    private function getCategoriesForDropdown()
    {
        // Get only parent categories (no children)
        $parentCategories = Category::whereNull('parent_id')->get();
        
        $categories = [];
        
        // Format parent categories only
        foreach ($parentCategories as $category) {
            $categories[$category->id] = $category->name;
        }
        
        return $categories;
    }
    
    /**
     * Check if a category is a parent category
     *
     * @param int $categoryId
     * @return bool
     */
    private function isParentCategory($categoryId)
    {
        return Category::where('id', $categoryId)
            ->whereNull('parent_id')
            ->exists();
    }
    
    /**
     * Get fabric rolls for a specific product
     *
     * @param int $id Product ID
     * @return \Illuminate\Http\Response
     */
    public function getByProduct($id)
    {
        $product = Product::findOrFail($id);
        
        if (!$product->is_fabric || !$product->track_by_roll) {
            return response()->json([
                'error' => 'This product is not a fabric roll product'
            ], 400);
        }
        
        $fabricRolls = $product->fabricRolls()
            ->orderBy('status')
            ->orderBy('received_date', 'desc')
            ->get();
            
        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'total_square_feet' => $product->total_square_feet,
                'active_rolls' => $fabricRolls->where('status', 'active')->count(),
                'depleted_rolls' => $fabricRolls->where('status', 'depleted')->count(),
                'damaged_rolls' => $fabricRolls->where('status', 'damaged')->count(),
            ],
            'rolls' => $fabricRolls->map(function($roll) {
                return [
                    'id' => $roll->id,
                    'roll_number' => $roll->roll_number,
                    'width' => $roll->width,
                    'length' => $roll->length,
                    'original_square_feet' => $roll->original_square_feet,
                    'remaining_square_feet' => $roll->remaining_square_feet,
                    'remaining_percentage' => $roll->remaining_percentage,
                    'status' => $roll->status,
                    'received_date' => $roll->received_date->format('Y-m-d'),
                    'location' => $roll->location ? $roll->location->name : null,
                    'supplier' => $roll->supplier ? $roll->supplier->name : null,
                    'notes' => $roll->notes
                ];
            })
        ]);
    }
    
    /**
     * Add a fabric roll to a product
     *
     * @param Request $request
     * @param Product $product
     * @return \Illuminate\Http\Response
     */
    public function addToProduct(Request $request, Product $product)
    {
        $request->validate([
            'width' => 'required|numeric|min:0.01',
            'length' => 'required|numeric|min:0.01',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'location_id' => 'nullable|exists:inventory_locations,id',
            'notes' => 'nullable|string|max:255'
        ]);
        
        if (!$product->is_fabric && !($product->category && $product->category->tracksBySquareFeet())) {
            return redirect()->back()->with('error', 'This product is not a fabric product');
        }
        
        try {
            $fabricRoll = $product->addFabricRoll(
                $request->width,
                $request->length,
                null,
                $request->notes,
                $request->location_id,
                $request->supplier_id
            );
            
            return redirect()->back()->with('success', 'Fabric roll added successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error adding fabric roll: ' . $e->getMessage());
        }
    }
    
    /**
     * Use fabric from a product
     *
     * @param Request $request
     * @param Product $product
     * @return \Illuminate\Http\Response
     */
    public function useFabric(Request $request, Product $product)
    {
        $request->validate([
            'square_feet' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:255'
        ]);
        
        if (!$product->is_fabric && !($product->category && $product->category->tracksBySquareFeet())) {
            return redirect()->back()->with('error', 'This product is not a fabric product');
        }
        
        try {
            $product->useFabricFromRolls(
                $request->square_feet,
                null,
                $request->notes
            );
            
            return redirect()->back()->with('success', 'Fabric used successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error using fabric: ' . $e->getMessage());
        }
    }
    
    /**
     * Mark a fabric roll as damaged
     *
     * @param Request $request
     * @param int $roll
     * @return \Illuminate\Http\Response
     */
    public function markDamaged(Request $request, $roll)
    {
        $request->validate([
            'notes' => 'required|string|max:255'
        ]);
        
        $fabricRoll = \App\FabricRoll::findOrFail($roll);
        
        try {
            $fabricRoll->markAsDamaged($request->notes);
            
            return redirect()->back()->with('success', 'Fabric roll marked as damaged');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error marking roll as damaged: ' . $e->getMessage());
        }
    }
}