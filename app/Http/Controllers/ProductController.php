<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Category;
use App\Product;
use App\ProductSupplier;
use App\Supplier;
use App\Tax;
use App\Unit;
use App\InventoryMovement;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get the price of a product
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function getPrice(Product $product)
    {
        return response()->json([
            'price' => $product->sales_price
        ]);
    }


    public function index()
    {
        $products = Product::with(['additionalProduct.supplier', 'category', 'unit', 'tax'])->get();
        
        // Get low stock products for alert
        $lowStockProducts = Product::lowStock()->pluck('id')->toArray();
        session(['low_stock_products' => $lowStockProducts]);
        
        return view('product.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $suppliers = Supplier::all();
        $categories = $this->getCategoriesForDropdown();
        $taxes = Tax::all();
        $units = Unit::all();

        // Check if we're creating a fabric product
        $productType = $request->query('type') === 'fabric' ? 'fabric' : 'regular';

        if ($productType === 'fabric') {
            return view('product.create-fabric-roll', compact('categories', 'taxes', 'units', 'suppliers'));
        }

        return view('product.create', compact('categories', 'taxes', 'units', 'suppliers', 'productType'));
    }
    
    /**
     * Show fabric products
     *
     * @return \Illuminate\Http\Response
     */
    public function fabricProducts()
    {
        $products = Product::where('is_fabric', true)
            ->orWhereHas('category', function($query) {
                $query->where('stock_type', Category::STOCK_TYPE_SQUARE_FEET);
            })
            ->with(['additionalProduct.supplier', 'category', 'unit', 'tax'])
            ->get();
        
        return view('product.fabric', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
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
            'current_stock' => 'sometimes|integer|min:0',
            'minimum_stock' => 'sometimes|integer|min:0',
            'auto_reorder' => 'sometimes|boolean',
            'location' => 'sometimes|string|max:255',
        ]);
        
        // Redirect fabric product creation to the fabric roll form
        if ($request->has('is_fabric') && $request->is_fabric) {
            return redirect()->route('fabric-roll.create')
                ->withInput()
                ->with('message', 'Please use this form to create fabric products.');
        }

        // Validate that the selected category is a parent category
        if (!$this->isParentCategory($request->category_id)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['category_id' => 'Products can only be added to parent categories.']);
        }


        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->serial_number = $request->serial_number;
        $product->model = $request->model;
        $product->category_id = $request->category_id;
        $product->sales_price = $request->sales_price;
        $product->unit_id = $request->unit_id;
        $product->tax_id = $request->tax_id;
        
        // Handle inventory fields for quantity-based products
        $product->current_stock = $request->has('current_stock') ? $request->current_stock : 0;
        $product->minimum_stock = $request->has('minimum_stock') ? $request->minimum_stock : 0;
        $product->location = $request->location;
        
        // Explicitly set fabric-related fields to false/null
        $product->is_fabric = false;
        $product->track_by_roll = false;
        $product->total_square_feet = 0;
        $product->alert_threshold_percent = 20; // Set a default value (20%)
        $product->roll_width = 0;
        $product->roll_length = 0;


        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();        
            $image->move(public_path('images/product/'), $imageName);
            $product->image = $imageName;
        } else {
            $product->image = 'default.jpg'; // Set a default image name
        }



        $product->save();

        foreach($request->supplier_id as $key => $supplier_id){
            $supplier = new ProductSupplier();
            $supplier->product_id = $product->id;
            $supplier->supplier_id = $request->supplier_id[$key];
            $supplier->price = $request->supplier_price[$key];
            $supplier->save();
        }
        return redirect()->back()->with('message', 'New product has been added successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        
        // Get the associated ProductSupplier record
        $additional = ProductSupplier::where('product_id', $id)->first();
        
        // If no ProductSupplier record exists, create a dummy object to avoid null reference errors
        if (!$additional) {
            $additional = new \stdClass();
            $additional->product = $product;
            $additional->supplier_id = null;
            $additional->price = 0;
            $additional->supplier = null;
        }
        
        $suppliers = Supplier::all();
        $categories = $this->getCategoriesForDropdown();
        $taxes = Tax::all();
        $units = Unit::all();
        
        return view('product.edit', compact('additional', 'suppliers', 'categories', 'taxes', 'units', 'product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    // public function update(Request $request, $id)
    // {
    //     $request->validate([
    //         'name' => 'required|min:3|unique:products|regex:/^[a-zA-Z ]+$/',
    //         'serial_number' => 'required',
    //         'model' => 'required|min:3',
    //         'category_id' => 'required',
    //         'sales_price' => 'required',
    //         'unit_id' => 'required',
    //         'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    //         'tax_id' => 'required',

    // ]);

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

    //     $product = new Product();
    //     $product->name = $request->name;
    //     $product->serial_number = $request->serial_number;
    //     $product->model = $request->model;
    //     $product->category_id = $request->category_id;
    //     $product->sales_price = $request->sales_price;
    //     $product->unit_id = $request->unit_id;
    //     $product->tax_id = $request->tax_id;


    //     if ($request->hasFile('image')){
    //         $image_path ="images/product/".$product->image;
    //         if (file_exists($image_path)){
    //             unlink($image_path);
    //         }
    //         $imageName =request()->image->getClientOriginalName();
    //         request()->image->move(public_path('images/product/'), $imageName);
    //         $product->image = $imageName;
    //     }



    //     $product->save();

    //     foreach($request->supplier_id as $key => $supplier_id){
    //         $supplier = new ProductSupplier();
    //         $supplier->product_id = $product->id;
    //         $supplier->supplier_id = $request->supplier_id[$key];
    //         $supplier->price = $request->supplier_price[$key];
    //         $supplier->save();
    //     }
    //     return redirect()->back()->with('message', 'Product Updated Successfully');
    // }

    public function update(Request $request, $id)
    {
        // Check if this is a stock update from the modal form
        if ($request->has('quantity') && !$request->has('name')) {
            return $this->updateStock($request, $id);
        }
        
        $request->validate([
            'name' => 'required|min:3|unique:products,name,' . $id . '|regex:/^[a-zA-Z0-9 ]+$/',
            'serial_number' => 'required',
            'model' => 'required|min:3',
            'category_id' => 'required',
            'sales_price' => 'required',
            'unit_id' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'tax_id' => 'required',
            'supplier_id.*' => 'required|exists:suppliers,id',
            'supplier_price.*' => 'required|numeric|min:0',
            'current_stock' => 'sometimes|integer|min:0',
            'minimum_stock' => 'sometimes|integer|min:0',
            'auto_reorder' => 'sometimes|boolean',
            'location' => 'sometimes|string|max:255',
            'is_fabric' => 'sometimes|boolean',
            'track_by_roll' => 'sometimes|boolean',
            'total_square_feet' => 'sometimes|numeric|min:0',
            'alert_threshold_percent' => 'sometimes|integer|min:1|max:100',
            'roll_width' => 'sometimes|numeric|min:0',
            'roll_length' => 'sometimes|numeric|min:0',
            'add_new_roll' => 'sometimes|boolean',
        ]);

        // Validate that the selected category is a parent category
        if (!$this->isParentCategory($request->category_id)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['category_id' => 'Products can only be added to parent categories.']);
        }

        $product = Product::find($id);

        if (!$product) {
            return redirect()->back()->with('error', 'Product not found');
        }

        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->serial_number = $request->serial_number;
        $product->model = $request->model;
        $product->category_id = $request->category_id;
        $product->sales_price = $request->sales_price;
        $product->unit_id = $request->unit_id;
        $product->tax_id = $request->tax_id;
        
        // Handle inventory fields for all products
        $oldStock = $product->current_stock;
        $product->current_stock = $request->has('current_stock') ? $request->current_stock : 0;
        $product->minimum_stock = $request->has('minimum_stock') ? $request->minimum_stock : 0;
        $product->location = $request->location;
        
        // Handle fabric-specific fields
        $product->is_fabric = $request->has('is_fabric');
        $product->track_by_roll = $request->has('track_by_roll');
        
        if ($request->has('total_square_feet')) {
            $product->total_square_feet = $request->total_square_feet;
        }
        
        if ($request->has('alert_threshold_percent')) {
            $product->alert_threshold_percent = $request->alert_threshold_percent;
        }

        if ($request->hasFile('image')) {
            // Delete the existing image file if it exists
            $existingImagePath = public_path("images/product/{$product->image}");
            if (file_exists($existingImagePath) && is_file($existingImagePath) && $product->image != 'default.jpg') {
                unlink($existingImagePath);
            }
        
            $imageName = time() . '_' . uniqid() . '.' . $request->image->getClientOriginalExtension();
            $request->image->move(public_path('images/product/'), $imageName);
        
            $product->image = $imageName;
        }
        
        $product->save();
        
        // Record inventory movement if stock changed
        $stockDifference = $product->current_stock - $oldStock;
        if ($stockDifference != 0) {
            $movementType = $stockDifference > 0 ? 'in' : 'out';
            $quantity = abs($stockDifference);
            
            if ($movementType === 'in') {
                InventoryMovement::recordStockIn(
                    $product->id,
                    $quantity,
                    'Stock updated via product edit form',
                    null,
                    null,
                    auth()->id(),
                    $product->roll_width,
                    $product->roll_length
                );
            } else {
                InventoryMovement::recordStockOut(
                    $product->id,
                    $quantity,
                    'Stock updated via product edit form',
                    null,
                    null,
                    auth()->id(),
                    $product->roll_width,
                    $product->roll_length
                );
            }
        }
        
        // Handle adding a new fabric roll if requested
        if ($request->has('add_new_roll') && $product->is_fabric && $product->track_by_roll &&
            $request->has('roll_width') && $request->has('roll_length')) {
            
            $width = $request->roll_width;
            $length = $request->roll_length;
            $numberOfRolls = $request->number_of_rolls ?? 1;
            
            if ($width > 0 && $length > 0) {
                for ($i = 0; $i < $numberOfRolls; $i++) {
                    $product->addFabricRoll($width, $length, null, 'Roll #' . ($i + 1) . ' added via product edit form');
                }
            }
        }

        // Update or create product suppliers
        foreach ($request->supplier_id as $key => $supplier_id) {
            $supplier = ProductSupplier::where('product_id', $product->id)
                ->where('supplier_id', $supplier_id)
                ->first();

            if (!$supplier) {
                $supplier = new ProductSupplier();
                $supplier->product_id = $product->id;
                $supplier->supplier_id = $supplier_id;
            }

            $supplier->price = $request->supplier_price[$key];
            $supplier->save();
        }

        // Redirect to the edit page with the product ID to ensure fresh data is loaded
        return redirect()->route('products.edit', $product->id)->with('message', 'Product has been updated successfully');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            
            // Delete related product suppliers first
            ProductSupplier::where('product_id', $id)->delete();
            
            // Delete the product image if it exists and is not the default image
            if ($product->image && $product->image !== 'default.jpg') {
                $imagePath = public_path('images/product/' . $product->image);
                if (file_exists($imagePath) && is_file($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            // Delete the product
            $product->delete();
            
            return redirect()->back()->with('message', 'Product deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting product: ' . $e->getMessage());
        }
    }

    /**
     * Update the product stock quantity
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int|null  $id
     * @return \Illuminate\Http\Response
     */
    public function updateStock(Request $request, $id = null)
    {
        $request->validate([
            'product_id' => 'sometimes|exists:products,id',
            'quantity' => 'required|integer|min:0',
        ]);

        try {
            // Get product ID either from route parameter or request
            $productId = $id ?? $request->product_id;
            $product = Product::findOrFail($productId);
            
            // Calculate the difference between current and new quantity
            $oldStock = $product->current_stock;
            $newStock = $request->quantity;
            $difference = $newStock - $oldStock;
            
            if ($difference == 0) {
                return redirect()->back()->with('info', 'No change in stock quantity.');
            }
            
            // Handle different product types differently
            if ($product->is_fabric && $product->track_by_roll) {
                // For fabric products tracked by roll, we need to record an inventory movement
                // rather than just updating the current_stock field
                
                // Update the product stock
                $product->current_stock = $newStock;
                $product->save();
                
                // Record inventory movement
                $movementType = $difference > 0 ? 'in' : 'out';
                $quantity = abs($difference);
                
                if ($movementType === 'in') {
                    InventoryMovement::recordStockIn(
                        $product->id,
                        $quantity,
                        'Stock adjustment via update form',
                        null,
                        null,
                        auth()->id(),
                        $product->roll_width,
                        $product->roll_length
                    );
                } else {
                    InventoryMovement::recordStockOut(
                        $product->id,
                        $quantity,
                        'Stock adjustment via update form',
                        null,
                        null,
                        auth()->id(),
                        $product->roll_width,
                        $product->roll_length
                    );
                }
                
                if ($difference > 0) {
                    // If increasing stock, show a message about adding rolls
                    return redirect()->back()->with('success',
                        'Stock quantity updated. For fabric products, please use the "Add Roll" button to properly track fabric inventory.');
                }
            } else {
                // For regular products, update the current_stock field
                $product->current_stock = $newStock;
                $product->save();
                
                // Record inventory movement
                $movementType = $difference > 0 ? 'in' : 'out';
                $quantity = abs($difference);
                
                if ($movementType === 'in') {
                    InventoryMovement::recordStockIn(
                        $product->id,
                        $quantity,
                        'Stock adjustment via update form',
                        null,
                        null,
                        auth()->id()
                    );
                } else {
                    InventoryMovement::recordStockOut(
                        $product->id,
                        $quantity,
                        'Stock adjustment via update form',
                        null,
                        null,
                        auth()->id()
                    );
                }
            }

            return redirect()->back()->with('success', 'Product stock updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating product stock: ' . $e->getMessage());
        }
    }
}
