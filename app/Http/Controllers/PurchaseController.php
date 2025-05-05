<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Product;
use App\Purchase;
use App\Sale;
use App\Supplier;
use App\Invoice;
use App\PurchaseDetail;
use App\FabricRoll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {
        $purchases = Purchase::with('supplier')->latest()->get();
        return view('purchase.index', compact('purchases'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $suppliers = Supplier::all();
        $products = Product::all();
        return view('purchase.create', compact('suppliers','products'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'product_id.*' => 'required|exists:products,id',
            'qty.*' => 'required|numeric|min:1',
            'price.*' => 'required|numeric|min:0',
            'dis.*' => 'required|numeric|min:0|max:100',
            'amount.*' => 'required|numeric|min:0',
        ]);
        
        // Add conditional validation for fabric products
        foreach ($request->product_id as $key => $productId) {
            if ($request->is_fabric[$key] === 'true' && $request->track_by_roll[$key] === 'true') {
                $validator->sometimes("width.$key", 'required|numeric|min:1', function() use ($request, $key) {
                    return $request->is_fabric[$key] === 'true' && $request->track_by_roll[$key] === 'true';
                });
                
                $validator->sometimes("length.$key", 'required|numeric|min:1', function() use ($request, $key) {
                    return $request->is_fabric[$key] === 'true' && $request->track_by_roll[$key] === 'true';
                });
            }
        }
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create a new purchase
        $purchase = new Purchase();
        $purchase->supplier_id = $request->supplier_id;
        $purchase->date = $request->date;
        $purchase->notes = $request->notes;
        $purchase->save();

        // Store purchase details
        foreach ($request->product_id as $key => $productId) {
            if ($productId) { // Skip empty rows
                $purchaseDetail = $purchase->purchaseDetails()->create([
                    'supplier_id' => $request->supplier_id,
                    'product_id' => $productId,
                    'qty' => $request->qty[$key],
                    'price' => $request->price[$key],
                    'discount' => $request->dis[$key],
                    'amount' => $request->amount[$key],
                ]);
                
                // Handle fabric rolls if this is a fabric product tracked by roll
                if ($request->is_fabric[$key] === 'true' && $request->track_by_roll[$key] === 'true') {
                    $this->createFabricRolls(
                        $productId,
                        $request->supplier_id,
                        $request->qty[$key],
                        $request->width[$key],
                        $request->length[$key],
                        $purchase->id,
                        $purchase->date
                    );
                }
            }
        }

        // Calculate total
        $purchase->calculateTotal();
        
        // Update inventory for non-fabric products
        // Fabric products are handled by createFabricRolls
        $purchase->updateInventory();

        return redirect()->route('purchases.index')->with('success', 'Purchase added successfully');
    }
    
    /**
     * Create fabric rolls for a purchase.
     */
    private function createFabricRolls($productId, $supplierId, $quantity, $width, $length, $purchaseId, $receivedDate)
    {
        $product = Product::findOrFail($productId);
        
        // Only proceed if this is a fabric product tracked by roll
        if (!$product->is_fabric || !$product->track_by_roll) {
            return;
        }
        
        // Calculate total square feet for all rolls
        $squareFeetPerRoll = $width * $length;
        $totalSquareFeet = $squareFeetPerRoll * $quantity;
        
        // Update product's total square feet
        $product->total_square_feet = $product->total_square_feet + $totalSquareFeet;
        $product->save();
        
        // Create the specified number of rolls
        for ($i = 0; $i < $quantity; $i++) {
            $rollNumber = 'R' . time() . rand(100, 999) . '-' . ($i + 1);
            
            FabricRoll::createRoll([
                'product_id' => $productId,
                'roll_number' => $rollNumber,
                'width' => $width,
                'length' => $length,
                'supplier_id' => $supplierId,
                'purchase_id' => $purchaseId,
                'received_date' => $receivedDate,
                'notes' => 'Added via Purchase #' . $purchaseId
            ]);
        }
    }

    public function findPrice(Request $request){
        $data = DB::table('products')->select('sales_price')->where('id', $request->id)->first();
        return response()->json($data);
    }

    public function findPricePurchase(Request $request) {
        $data = DB::table('product_suppliers')
                ->select('price')
                ->where('product_id', $request->id)
                ->where('supplier_id', $request->supplier_id) // Assuming you pass supplier_id from the frontend
                ->first();
    
        return response()->json($data);
    }    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $purchase = Purchase::with(['supplier', 'purchaseDetails.product'])->findOrFail($id);
        return view('purchase.show', compact('purchase'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $suppliers = Supplier::all();
        $products = Product::all();
        $purchase = Purchase::with('purchaseDetails')->findOrFail($id);
        return view('purchase.edit', compact('suppliers', 'products', 'purchase'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'product_id.*' => 'required|exists:products,id',
            'qty.*' => 'required|numeric|min:1',
            'price.*' => 'required|numeric|min:0',
            'dis.*' => 'required|numeric|min:0|max:100',
            'amount.*' => 'required|numeric|min:0',
        ]);
        
        // Add conditional validation for fabric products
        foreach ($request->product_id as $key => $productId) {
            if ($request->is_fabric[$key] === 'true' && $request->track_by_roll[$key] === 'true') {
                $validator->sometimes("width.$key", 'required|numeric|min:1', function() use ($request, $key) {
                    return $request->is_fabric[$key] === 'true' && $request->track_by_roll[$key] === 'true';
                });
                
                $validator->sometimes("length.$key", 'required|numeric|min:1', function() use ($request, $key) {
                    return $request->is_fabric[$key] === 'true' && $request->track_by_roll[$key] === 'true';
                });
            }
        }
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Find the purchase
        $purchase = Purchase::findOrFail($id);
        
        // Reverse inventory changes
        foreach ($purchase->purchaseDetails as $detail) {
            $product = $detail->product;
            
            // For fabric products, we need to handle differently
            if ($product->is_fabric && $product->track_by_roll) {
                // Find and mark fabric rolls as damaged
                $fabricRolls = FabricRoll::where('product_id', $product->id)
                    ->where('notes', 'like', '%Purchase #' . $purchase->id . '%')
                    ->get();
                
                foreach ($fabricRolls as $roll) {
                    $roll->markAsDamaged('Purchase #' . $purchase->id . ' updated - roll removed');
                }
            } else {
                // For regular products, just stock out
                $product->stockOut(
                    $detail->qty,
                    'Purchase #' . $purchase->id . ' update - reversal',
                    $purchase->id,
                    'App\\Purchase'
                );
            }
        }
        
        // Update purchase
        $purchase->supplier_id = $request->supplier_id;
        $purchase->date = $request->date;
        $purchase->notes = $request->notes;
        $purchase->save();

        // Delete old purchase details
        $purchase->purchaseDetails()->delete();

        // Store new purchase details
        foreach ($request->product_id as $key => $productId) {
            if ($productId) { // Skip empty rows
                $purchaseDetail = $purchase->purchaseDetails()->create([
                    'supplier_id' => $request->supplier_id,
                    'product_id' => $productId,
                    'qty' => $request->qty[$key],
                    'price' => $request->price[$key],
                    'discount' => $request->dis[$key],
                    'amount' => $request->amount[$key],
                ]);
                
                // Handle fabric rolls if this is a fabric product tracked by roll
                if ($request->is_fabric[$key] === 'true' && $request->track_by_roll[$key] === 'true') {
                    $this->createFabricRolls(
                        $productId,
                        $request->supplier_id,
                        $request->qty[$key],
                        $request->width[$key],
                        $request->length[$key],
                        $purchase->id,
                        $purchase->date
                    );
                }
            }
        }

        // Calculate total
        $purchase->calculateTotal();
        
        // Update inventory with new values
        $purchase->updateInventory();

        return redirect()->route('purchases.show', $purchase->id)->with('success', 'Purchase updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {
        $purchase = Purchase::findOrFail($id);
        
        // Reverse inventory changes
        foreach ($purchase->purchaseDetails as $detail) {
            $product = $detail->product;
            
            // For fabric products, we need to handle differently
            if ($product->is_fabric && $product->track_by_roll) {
                // Find and mark fabric rolls as damaged
                $fabricRolls = FabricRoll::where('product_id', $product->id)
                    ->where('notes', 'like', '%Purchase #' . $purchase->id . '%')
                    ->get();
                
                foreach ($fabricRolls as $roll) {
                    $roll->markAsDamaged('Purchase #' . $purchase->id . ' deleted - roll removed');
                }
            } else {
                // For regular products, just stock out
                $product->stockOut(
                    $detail->qty,
                    'Purchase #' . $purchase->id . ' deleted',
                    $purchase->id,
                    'App\\Purchase'
                );
            }
        }
        
        // Delete purchase and its details (cascade)
        $purchase->delete();
        
        return redirect()->route('purchases.index')->with('success', 'Purchase deleted successfully');
    }
    
    /**
     * Get fabric rolls for a specific purchase detail.
     *
     * @param  int  $purchaseId
     * @param  int  $detailId
     * @return \Illuminate\Http\Response
     */
    public function getFabricRolls($purchaseId, $detailId)
    {
        $purchase = Purchase::findOrFail($purchaseId);
        $detail = PurchaseDetail::findOrFail($detailId);
        
        // Verify that the detail belongs to the purchase
        if ($detail->purchase_id != $purchase->id) {
            return response()->json(['error' => 'Purchase detail does not belong to this purchase'], 400);
        }
        
        // Verify that this is a fabric product tracked by roll
        if (!$detail->isFabricRoll()) {
            return response()->json(['error' => 'This product is not a fabric roll product'], 400);
        }
        
        // Get the fabric rolls
        $fabricRolls = FabricRoll::where('product_id', $detail->product_id)
            ->where('notes', 'like', '%Purchase #' . $purchase->id . '%')
            ->get();
        
        return response()->json([
            'detail' => [
                'id' => $detail->id,
                'product_name' => $detail->product->name,
                'quantity' => $detail->qty,
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
                ];
            })
        ]);
    }
}
