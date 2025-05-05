<?php

namespace App\Http\Controllers;

use App\CustomOrder;
use App\FlagDetail;
use App\User;
use App\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CustomOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        
        // Improve eager loading to include all necessary relationships
        $orders = $user->role === 'admin'
            ? CustomOrder::with([
                'customer',
                'flagDetails.product.category',
                'flagDetails.product.unit'
              ])->latest()->paginate(10)
            : CustomOrder::where('user_id', $user->id)
                ->with([
                    'customer',
                    'flagDetails.product.category',
                    'flagDetails.product.unit'
                ])->latest()->paginate(10);

        return view('custom-orders.index', compact('orders'));
    }

    public function create(Request $request)
    {
        $customers = \App\Customer::all();
        
        // Get fabric products for the dropdown
        $fabricProducts = \App\Product::where('is_fabric', true)
                                     ->where('track_by_roll', true)
                                     ->where('total_square_feet', '>', 0)
                                     ->get();
        
        // Get regular products for the dropdown
        $products = \App\Product::where(function($query) {
                                    $query->where('is_fabric', false)
                                          ->orWhereNull('is_fabric');
                                })
                                ->get();
        
        // Check if a preset type was requested
        $presetType = $request->query('preset_type');
        
        // Check if we're editing an existing order
        $customOrder = null;
        $isEditing = false;
        
        if ($request->has('edit_order')) {
            $customOrder = CustomOrder::with('flagDetails.product')->find($request->edit_order);
            
            if ($customOrder) {
                $isEditing = true;
                $this->authorize('update', $customOrder);
                
                // No need for debug logging in production
            }
        }
                                     
        return view('custom-orders.create', compact('customers', 'fabricProducts', 'products', 'presetType', 'customOrder', 'isEditing'));
    }
    
    public function createFlagOrder()
    {
        $customers = \App\Customer::all();
        
        // Find the "flag fabric" parent category
        $flagFabricCategory = \App\Category::where('name', 'Flag Fabric')
            ->whereNull('parent_id')
            ->first();
            
        // Get only categories that are children of the "flag fabric" parent category
        if ($flagFabricCategory) {
            $categories = \App\Category::where('parent_id', $flagFabricCategory->id)->get();
        } else {
            // Fallback to all categories if "flag fabric" parent category doesn't exist
            $categories = \App\Category::all();
        }
        
        $currentUser = auth()->user();
        // Generate a unique order number
        $orderNumber = 'F' . date('ymd') . rand(1000, 9999);
        $customer_id = null; // You can set a default customer ID here if needed
        $customer = null;
        if ($customer_id) {
            $customer = \App\Customer::find($customer_id);
        }
        return view('custom-orders.flag-order-form', compact('customers', 'categories', 'currentUser', 'orderNumber', 'customer_id', 'customer'));
    }

    public function store(Request $request)
    {
        // Remove excessive logging
        
        try {
            // SIMPLIFIED VALIDATION: Only validate customer_id
            try {
                // Enhanced validation rules
                $validatedData = $request->validate([
                    'customer_id' => 'required|exists:customers,id',
                    'job_type' => 'sometimes|array',
                    'job_type.*' => 'in:product,flag',
                    'fabric_id' => 'sometimes|array',
                    'fabric_id.*' => 'exists:products,id',
                    'fabric_height' => 'sometimes|array',
                    'fabric_height.*' => 'numeric|min:0',
                    'fabric_breadth' => 'sometimes|array',
                    'fabric_breadth.*' => 'numeric|min:0',
                    'fabric_quantity' => 'sometimes|array',
                    'fabric_quantity.*' => 'integer|min:1',
                    'fabric_price_per_square_feet' => 'sometimes|array',
                    'fabric_price_per_square_feet.*' => 'numeric|min:0',
                    'fabric_stitching' => 'sometimes|array',
                    'product_id' => 'sometimes|array',
                    'product_id.*' => 'exists:products,id',
                    'product_quantity' => 'sometimes|array',
                    'product_quantity.*' => 'integer|min:1',
                    'product_price' => 'sometimes|array',
                    'product_price.*' => 'numeric|min:0',
                    'special_instructions' => 'nullable|string',
                ]);
                
                // Ensure job_type is an array
                if (!isset($validatedData['job_type']) || !is_array($validatedData['job_type'])) {
                    $validatedData['job_type'] = ['product']; // Default to product
                }
            } catch (\Illuminate\Validation\ValidationException $e) {
                Log::error('Validation failed', ['errors' => $e->errors()]);
                throw $e;
            }

        // This block is duplicated below - removing this instance
        
        // Create a single order record
        $orderData = [
            'customer_id' => $validatedData['customer_id'],
            'user_id' => Auth::id(),
            'status' => CustomOrder::STATUS_PENDING,
            'special_instructions' => $validatedData['special_instructions'] ?? null,
            'design_file' => null,
            'job_type' => $validatedData['job_type'][0] ?? null,
            'fabric_type' => isset($validatedData['fabric_id'][0]) ? \App\Product::find($validatedData['fabric_id'][0])->name ?? null : null,
            'contact_through' => $validatedData['contact_through'] ?? null,
            'received_by' => $validatedData['received_by'] ?? Auth::user()->name,
            // Set a default flag type for the main order
            'flag_type' => $validatedData['job_type'][0] ?? 'Custom Flag',
            // Set default dimensions for the main order (can be updated later)
            'height' => $validatedData['height'][0] ?? 0,
            'breadth' => $validatedData['breadth'][0] ?? 0,
            'quantity' => array_sum($validatedData['quantity'] ?? [1]),
            'stitching' => isset($validatedData['stitching'][0]) ? true : false,
        ];

        // Calculate total price from all items
        $totalPrice = 0;
        
        // Check if quantity field exists in validated data
        if (!isset($validatedData['quantity'])) {
            // Create quantity array from product_quantity and fabric_quantity
            $validatedData['quantity'] = [];
            
            // Add product quantities
            if (isset($request->product_quantity)) {
                foreach ($request->product_quantity as $qty) {
                    $validatedData['quantity'][] = $qty;
                }
            }
            
            // Add fabric quantities
            if (isset($request->fabric_quantity)) {
                foreach ($request->fabric_quantity as $qty) {
                    $validatedData['quantity'][] = $qty;
                }
            }
            
            // Remove excessive logging
        }
        
        // Check if height field exists in validated data
        if (!isset($validatedData['height'])) {
            // Create height array from fabric_height
            $validatedData['height'] = [];
            
            // Add zeros for products
            if (isset($request->product_id)) {
                foreach ($request->product_id as $id) {
                    $validatedData['height'][] = 0;
                }
            }
            
            // Add fabric heights
            if (isset($request->fabric_height)) {
                foreach ($request->fabric_height as $height) {
                    $validatedData['height'][] = $height;
                }
            }
            
            // Remove excessive logging
        }
        
        // Check if breadth field exists in validated data
        if (!isset($validatedData['breadth'])) {
            // Create breadth array from fabric_breadth
            $validatedData['breadth'] = [];
            
            // Add zeros for products
            if (isset($request->product_id)) {
                foreach ($request->product_id as $id) {
                    $validatedData['breadth'][] = 0;
                }
            }
            
            // Add fabric breadths
            if (isset($request->fabric_breadth)) {
                foreach ($request->fabric_breadth as $breadth) {
                    $validatedData['breadth'][] = $breadth;
                }
            }
            
            // Remove excessive logging
        }
        
        // Check if price_per_square_feet field exists in validated data
        if (!isset($validatedData['price_per_square_feet'])) {
            // Create price_per_square_feet array from product_price and fabric_price_per_square_feet
            $validatedData['price_per_square_feet'] = [];
            
            // Add product prices
            if (isset($request->product_price)) {
                foreach ($request->product_price as $price) {
                    $validatedData['price_per_square_feet'][] = $price;
                }
            }
            
            // Add fabric prices
            if (isset($request->fabric_price_per_square_feet)) {
                foreach ($request->fabric_price_per_square_feet as $price) {
                    $validatedData['price_per_square_feet'][] = $price;
                }
            }
            
            // Remove excessive logging
        }
        
        // Check if stitching field exists in validated data
        if (!isset($validatedData['stitching'])) {
            // Create stitching array
            $validatedData['stitching'] = [];
            
            // Add false for products
            if (isset($request->product_id)) {
                foreach ($request->product_id as $id) {
                    $validatedData['stitching'][] = false;
                }
            }
            
            // Add true for fabrics
            if (isset($request->fabric_id)) {
                foreach ($request->fabric_id as $id) {
                    $validatedData['stitching'][] = true;
                }
            }
            
            // Remove excessive logging
        }
        
        foreach ($validatedData['job_type'] as $index => $jobType) {
            $quantity = isset($validatedData['quantity'][$index]) ? $validatedData['quantity'][$index] : 1;
            $pricePerUnit = isset($validatedData['price_per_square_feet'][$index]) ? $validatedData['price_per_square_feet'][$index] : 0;
            
            // Different calculation based on job type
            if ($jobType === 'flag') {
                // For flag type, calculate based on square feet
                $height = $validatedData['height'][$index];
                $breadth = $validatedData['breadth'][$index];
                $squareFeet = $height * $breadth; // Height and breadth are in feet
                $itemPrice = $squareFeet * $pricePerUnit * $quantity;
                
                // If this is a flag item and has a fabric product selected, deduct the square feet from the fabric product
                if (isset($validatedData['fabric_id'][$index]) && $validatedData['fabric_id'][$index]) {
                    $fabricProduct = \App\Product::find($validatedData['fabric_id'][$index]);
                    if ($fabricProduct && $fabricProduct->is_fabric && $fabricProduct->track_by_roll) {
                        try {
                            // Deduct the square feet from the fabric product
                            $totalSquareFeet = $squareFeet * $quantity;
                            $fabricProduct->useFabricFromRolls(
                                $totalSquareFeet,
                                null,
                                'Used for custom order - ' . $jobType
                            );
                        } catch (\Exception $e) {
                            // If there's not enough fabric, return with an error
                            return redirect()->back()
                                ->withInput()
                                ->withErrors(['fabric_id.' . $index => 'Not enough fabric available: ' . $e->getMessage()]);
                        }
                    }
                }
            } else {
                // For product or other types, simple price * quantity calculation
                $itemPrice = $pricePerUnit * $quantity;
            }
            
            $totalPrice += $itemPrice;
        }
        
        $orderData['square_feet'] = ($orderData['height'] * $orderData['breadth']) / 144;
        $orderData['total_price'] = $totalPrice;

        if ($request->hasFile('design_file')) {
            $path = $request->file('design_file')->store('designs', 'public');
            $orderData['design_file'] = $path;
        }
        
        // Store VAT information in the special_instructions field if VAT is added
        if ($request->has('add_vat') && $request->add_vat) {
            $vatInfo = "VAT Added: Yes\n";
            $vatInfo .= "Subtotal: " . ($request->subtotal ?? 0) . "\n";
            $vatInfo .= "VAT Amount: " . ($request->vat_amount ?? 0) . "\n";
            $vatInfo .= "Total with VAT: " . ($request->total ?? 0);
            
            $orderData['special_instructions'] = ($orderData['special_instructions'] ? $orderData['special_instructions'] . "\n\n" : '') . $vatInfo;
        }

        // Create a single order record
        $order = CustomOrder::create($orderData);
        
        // Remove excessive logging

        // Create flag detail records for each item
        foreach ($validatedData['job_type'] as $index => $jobType) {
            $quantity = $validatedData['quantity'][$index] ?? 1;
            $pricePerUnit = $validatedData['price_per_square_feet'][$index] ?? 0;
            
            // Get the correct product ID based on job type
            $productId = null;
            if ($jobType === 'flag' && isset($validatedData['fabric_id'][$index])) {
                $productId = $validatedData['fabric_id'][$index];
                // Remove excessive logging
            } elseif ($jobType === 'product' && isset($validatedData['product_id'][$index])) {
                $productId = $validatedData['product_id'][$index];
                // Remove excessive logging
            } else {
                // Try to get product ID from the generic product_id array
                $productId = $validatedData['product_id'][$index] ?? null;
                // Remove excessive logging
            }
            
            // Skip if no product ID is found
            if (!$productId) {
                // Remove excessive logging
                continue;
            }
            
            $stitching = isset($validatedData['stitching'][$index]) ? true : false;
            
            // Different handling based on job type
            if ($jobType === 'flag') {
                // For flag type, use square feet calculation
                $height = $validatedData['height'][$index] ?? 0;
                $breadth = $validatedData['breadth'][$index] ?? 0;
                $squareFeet = $height * $breadth; // Height and breadth are in feet
                $itemPrice = $squareFeet * $pricePerUnit * $quantity;
                
                // Remove excessive logging
                
                try {
                    // Make sure we have valid dimensions for fabric
                    if ($height <= 0 || $breadth <= 0) {
                        // Remove excessive logging
                        
                        // Use default dimensions if not provided
                        $fabricProduct = \App\Product::find($productId);
                        if ($fabricProduct) {
                            if ($height <= 0) $height = 1;
                            if ($breadth <= 0) $breadth = 1;
                            
                            // Recalculate square feet and price
                            $squareFeet = $height * $breadth;
                            $itemPrice = $squareFeet * $pricePerUnit * $quantity;
                            
                            // Remove excessive logging
                        }
                    }
                    
                    // Get the flag type from the form if available, otherwise use the job type
                    $flagTypeValue = isset($validatedData['flag_type'][$index]) ? $validatedData['flag_type'][$index] : $jobType;
                    
                    // Create flag detail with validation
                    $flagDetailData = [
                        'custom_order_id' => $order->id,
                        'product_id' => $productId,
                        'flag_type' => 'flag', // Always set to 'flag' to ensure it's recognized as a flag
                        'height' => $height,
                        'breadth' => $breadth,
                        'square_feet' => $squareFeet,
                        'price_per_square_feet' => $pricePerUnit,
                        'quantity' => $quantity,
                        'stitching' => $stitching,
                        'total_price' => $itemPrice,
                        'notes' => 'Flag Type: ' . ucfirst($flagTypeValue)
                    ];
                    
                    // Validate flag detail data
                    $validator = Validator::make($flagDetailData, FlagDetail::$rules);
                    if ($validator->fails()) {
                        throw new \Exception('Flag validation failed: ' . implode(', ', $validator->errors()->all()));
                    }
                    
                    $flagDetail = FlagDetail::create($flagDetailData);
                    
                    // Store the fabric product information in the notes field if not already set
                    if ($productId && empty($flagDetail->notes)) {
                        $fabricProduct = \App\Product::find($productId);
                        if ($fabricProduct) {
                            $flagDetail->notes = 'Fabric: ' . $fabricProduct->name;
                            $flagDetail->save();
                        }
                    }
                    
                    // Remove excessive logging
                } catch (\Exception $e) {
                    // Keep only essential error logging
                    Log::error('Error creating flag detail: ' . $e->getMessage());
                }
                
                // Store the fabric product ID in the notes field
                if ($productId) {
                    $fabricProduct = \App\Product::find($productId);
                    if ($fabricProduct) {
                        $flagDetail->notes = 'Fabric: ' . $fabricProduct->name;
                        $flagDetail->save();
                    }
                }
                
                // Remove excessive logging
            } else {
                // For product or other types, simple price * quantity calculation
                $itemPrice = $pricePerUnit * $quantity;
                
                // Remove excessive logging
                
                // For product type, set height and breadth to 0
                try {
                    // Create product detail with validation
                    $productDetailData = [
                        'custom_order_id' => $order->id,
                        'product_id' => $productId,
                        'flag_type' => 'product', // Always set to 'product' to ensure it's recognized as a product
                        'height' => 0,
                        'breadth' => 0,
                        'square_feet' => 0,
                        'price_per_square_feet' => $pricePerUnit,
                        'quantity' => $quantity,
                        'stitching' => false, // No stitching for products
                        'total_price' => $itemPrice
                    ];
                    
                    // Validate product detail data
                    $validator = Validator::make($productDetailData, FlagDetail::$rules);
                    if ($validator->fails()) {
                        throw new \Exception('Product validation failed: ' . implode(', ', $validator->errors()->all()));
                    }
                    
                    $flagDetail = FlagDetail::create($productDetailData);
                    
                    // Remove excessive logging
                } catch (\Exception $e) {
                    // Keep only essential error logging
                    Log::error('Error creating product detail: ' . $e->getMessage());
                }
                
                // Store the product ID and name in the notes field
                if ($productId) {
                    $product = \App\Product::find($productId);
                    if ($product) {
                        $flagDetail->notes = 'Product: ' . $product->name;
                        $flagDetail->save();
                    }
                }
                
                // Remove excessive logging
            }
        }

        // Redirect to the index page to show the list of orders including the newly created one
        return redirect()->route('custom-orders.index')
            ->with('success', 'Order #' . $order->id . ' has been created successfully!');
        
        } catch (\Exception $e) {
            // Keep only essential error logging
            Log::error('Error creating custom order: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'An error occurred while creating the custom order: ' . $e->getMessage()]);
        }
    }

    public function show(CustomOrder $customOrder, Request $request)
    {
        $this->authorize('view', $customOrder);
        
        // Eager load all necessary relationships for better performance
        $customOrder->load([
            'customer',
            'flagDetails.product.category',
            'flagDetails.product.unit'
        ]);
        
        // Check if this is a newly created order view
        $newlyCreated = $request->has('new') && $request->new == 1;
        
        // Check if status update is requested
        if ($request->isMethod('post') && $request->has('status')) {
            $request->validate([
                'status' => 'required|in:' . implode(',', CustomOrder::STATUSES)
            ]);
            
            $customOrder->update(['status' => $request->status]);
            
            return redirect()->route('custom-orders.show', $customOrder)
                ->with('success', 'Order status updated successfully.');
        }
        
        // Calculate fabric-specific statistics
        $fabricItems = $customOrder->flagDetails->where('flag_type', 'flag');
        $fabricItemsCount = $fabricItems->count();
        $productItemsCount = $customOrder->flagDetails->where('flag_type', '!=', 'flag')->count();
        $totalItemsCount = $fabricItemsCount + $productItemsCount;
        
        $totalFabricArea = $fabricItems->sum(function($item) {
            return $item->square_feet * $item->quantity;
        });
        
        if ($newlyCreated) {
            return view('custom-orders.show', compact('customOrder', 'fabricItems', 'fabricItemsCount', 'productItemsCount', 'totalItemsCount', 'totalFabricArea'))
                ->with('success', 'Order #' . $customOrder->id . ' has been created successfully!');
        }
        
        return view('custom-orders.show', compact('customOrder', 'fabricItems', 'fabricItemsCount', 'productItemsCount', 'totalItemsCount', 'totalFabricArea'));
    }

    public function updateStatus(Request $request, CustomOrder $custom_order)
    {
        $this->authorize('update', $custom_order);

        // If it's a GET request, show the status update form
        if ($request->isMethod('get')) {
            return view('custom-orders.show', compact('custom_order'));
        }

        $request->validate([
            'status' => 'required|in:' . implode(',', CustomOrder::STATUSES)
        ]);
        
        // Check if this is an AJAX request
        $isAjax = $request->ajax() || $request->wantsJson();
        
        // Update the status
        $oldStatus = $custom_order->status;
        $custom_order->update(['status' => $request->status]);
        
        // Remove excessive logging
        
        // Return JSON response for AJAX requests
        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'order' => [
                    'id' => $custom_order->id,
                    'status' => $custom_order->status,
                    'updated_at' => $custom_order->updated_at->format('Y-m-d H:i:s')
                ]
            ]);
        }
        
        // For regular requests, redirect back with a success message
        return redirect()->route('custom-orders.show', $custom_order)
            ->with('success', 'Order status updated successfully to ' . $request->status);

        // This is duplicate code - the status is already updated above and there's already a JSON response handler
    }


    public function createInvoice(CustomOrder $custom_order)
    {
        $this->authorize('update', $custom_order);

        if ($custom_order->status !== CustomOrder::STATUS_READY) {
            return redirect()->back()->with('error', 'Invoice can only be created for orders with Ready status');
        }

        $invoice = \App\Invoice::create([
            'customer_id' => $custom_order->customer_id,
            'total' => $custom_order->total_price,
            'custom_order_id' => $custom_order->id
        ]);

        // Create a sale record for each flag detail
        foreach ($custom_order->flagDetails as $flagDetail) {
            \App\Sale::create([
                'invoice_id' => $invoice->id,
                'product_id' => null, // Null allowed for custom orders
                'quantity' => $flagDetail->quantity,
                'unit_price' => $flagDetail->price_per_square_feet,
                'total_price' => $flagDetail->total_price,
                'description' => $flagDetail->flag_type . ' (' . $flagDetail->height . ' X ' . $flagDetail->breadth . ')'
            ]);
        }

        return redirect()->route('sales.index', ['tab' => 'invoice'])
            ->with('success', 'Invoice created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CustomOrder  $customOrder
     * @return \Illuminate\Http\Response
     */
    public function edit(CustomOrder $customOrder)
    {
        $this->authorize('update', $customOrder);
        
        // Get the customers and products for the dropdowns
        $customers = \App\Customer::all();
        
        // Get fabric products for the dropdown
        $fabricProducts = \App\Product::where('is_fabric', true)
                                     ->where('track_by_roll', true)
                                     ->where('total_square_feet', '>', 0)
                                     ->get();
        
        // Get regular products for the dropdown
        $products = \App\Product::where(function($query) {
                                    $query->where('is_fabric', false)
                                          ->orWhereNull('is_fabric');
                                })
                                ->get();
        
        // Load the flag details with their products
        $customOrder->load('flagDetails.product');
        
        // Remove excessive logging
        
        // Return the edit view with the necessary data
        return view('custom-orders.edit', compact('customOrder', 'customers', 'fabricProducts', 'products'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CustomOrder  $customOrder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CustomOrder $customOrder)
    {
        $this->authorize('update', $customOrder);
        
        try {
            // Start a database transaction
            DB::beginTransaction();
            
            // Process the update using similar logic to the store method
            // First, delete all existing flag details
            $customOrder->flagDetails()->delete();
            
            // Then update the main order data
            $customOrder->update([
                'customer_id' => $request->customer_id,
                'special_instructions' => $request->special_instructions,
                'status' => $request->status ?? CustomOrder::STATUS_PENDING,
                'contact_through' => $request->contact_through,
                'received_by' => $request->received_by ?? Auth::user()->name,
                'delivery_date' => $request->delivery_date,
            ]);
            
            // Process flag details from the form
            if (isset($request->flag_details) && is_array($request->flag_details)) {
                foreach ($request->flag_details as $detail) {
                    // Skip empty entries
                    if (empty($detail['product_id'])) continue;
                    
                    // Calculate square feet for flag items
                    $squareFeet = 0;
                    if ($detail['flag_type'] === 'flag' && isset($detail['height']) && isset($detail['breadth'])) {
                        $squareFeet = $detail['height'] * $detail['breadth'];
                    }
                    
                    // Calculate total price
                    $totalPrice = 0;
                    if ($detail['flag_type'] === 'flag') {
                        $totalPrice = $squareFeet * $detail['price_per_square_feet'] * $detail['quantity'];
                    } else {
                        $totalPrice = $detail['price_per_square_feet'] * $detail['quantity'];
                    }
                    
                    // Create the flag detail
                    FlagDetail::create([
                        'custom_order_id' => $customOrder->id,
                        'product_id' => $detail['product_id'],
                        'flag_type' => $detail['flag_type'],
                        'height' => $detail['height'] ?? 0,
                        'breadth' => $detail['breadth'] ?? 0,
                        'square_feet' => $squareFeet,
                        'price_per_square_feet' => $detail['price_per_square_feet'],
                        'quantity' => $detail['quantity'],
                        'stitching' => isset($detail['stitching']) ? $detail['stitching'] : false,
                        'total_price' => $totalPrice,
                        'notes' => 'Updated: ' . date('Y-m-d H:i:s')
                    ]);
                }
            }
            
            // Calculate and update the total price of the order
            $totalPrice = $customOrder->flagDetails()->sum('total_price');
            $customOrder->update(['total_price' => $totalPrice]);
            
            // Commit the transaction
            DB::commit();
            
            // After successful update, redirect to the show page
            return redirect()->route('custom-orders.show', $customOrder)
                ->with('success', 'Order #' . $customOrder->id . ' updated successfully');
                
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();
            
            // Keep only essential error logging
            Log::error('Error updating custom order: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'An error occurred while updating the custom order: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CustomOrder  $customOrder
     * @return \Illuminate\Http\Response
     */
    public function destroy(CustomOrder $customOrder)
    {
        $this->authorize('delete', $customOrder);
        
        try {
            // Delete flag details first
            $customOrder->flagDetails()->delete();
            
            // Delete the order
            $customOrder->delete();
            
            if (request()->wantsJson()) {
                return response()->json(['success' => true]);
            }
            
            return redirect()->route('custom-orders.index')
                ->with('success', 'Order deleted successfully');
                
        } catch (\Exception $e) {
            // Keep only essential error logging
            Log::error('Error deleting custom order: ' . $e->getMessage());
            
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            
            return redirect()->back()
                ->withErrors(['error' => 'An error occurred while deleting the custom order: ' . $e->getMessage()]);
        }
    }
}