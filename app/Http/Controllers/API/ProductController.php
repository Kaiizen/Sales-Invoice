<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::all();
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }
    
    /**
     * Get the price of a product
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getPrice($id)
    {
        Log::info('API getPrice called with ID: ' . $id);
        
        try {
            $product = Product::find($id);
            
            if (!$product) {
                Log::error('API getPrice: Product not found with ID: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }
            
            Log::info('API getPrice: Product found, sales_price: ' . $product->sales_price);
            
            return response()->json([
                'success' => true,
                'price' => $product->sales_price,
                'product_name' => $product->name
            ]);
        } catch (\Exception $e) {
            Log::error('API getPrice error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving product price: ' . $e->getMessage()
            ], 500);
        }
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
