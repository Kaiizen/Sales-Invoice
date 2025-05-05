<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Product API endpoints
Route::get('/products/{id}', function ($id) {
    $product = \App\Product::find($id);
    if (!$product) {
        return response()->json(['error' => 'Product not found'], 404);
    }
    return response()->json($product);
});

// Custom Order API endpoints
Route::get('/custom-orders/{id}', function ($id) {
    $order = \App\CustomOrder::with([
        'customer', 
        'flagDetails.product.category', 
        'flagDetails.product.unit'
    ])->find($id);
    
    if (!$order) {
        return response()->json(['error' => 'Order not found'], 404);
    }
    
    // Calculate fabric-specific statistics
    $fabricItems = $order->flagDetails->where('flag_type', 'flag');
    $totalFabricArea = $fabricItems->sum(function($item) {
        return $item->square_feet * $item->quantity;
    });
    
    // Format the response
    $response = [
        'id' => $order->id,
        'customer' => $order->customer ? [
            'id' => $order->customer->id,
            'name' => $order->customer->name,
            'email' => $order->customer->email,
            'phone' => $order->customer->phone
        ] : null,
        'status' => $order->status,
        'created_at' => $order->created_at->format('Y-m-d H:i:s'),
        'updated_at' => $order->updated_at->format('Y-m-d H:i:s'),
        'total_price' => $order->total_price,
        'special_instructions' => $order->special_instructions,
        'fabric_summary' => [
            'count' => $fabricItems->count(),
            'total_area' => $totalFabricArea,
            'items' => $fabricItems->map(function($item) {
                return [
                    'id' => $item->id,
                    'product' => $item->product ? $item->product->name : 'Unknown',
                    'dimensions' => $item->height . ' × ' . $item->breadth . ' ft',
                    'square_feet' => $item->square_feet,
                    'quantity' => $item->quantity,
                    'total_area' => $item->square_feet * $item->quantity,
                    'stitching' => $item->stitching,
                    'price' => $item->total_price
                ];
            })
        ],
        'product_items' => $order->flagDetails->where('flag_type', 'product')->map(function($item) {
            return [
                'id' => $item->id,
                'product' => $item->product ? $item->product->name : 'Unknown',
                'quantity' => $item->quantity,
                'price' => $item->total_price
            ];
        })
    ];
    
    return response()->json($response);
});
