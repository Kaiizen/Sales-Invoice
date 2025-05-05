<?php


namespace App\Http\Controllers;

use App\Sales;
use App\Customer;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function index()
    {
        $sales = Sales::with('product')->get(); // Include products related to sales

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $products = \App\Product::all();
        $customers = Customer::all();
        return view('sales.create', compact('products', 'customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
            'price' => 'required|numeric',
            'dis' => 'nullable|numeric',
            'amount' => 'required|numeric'
        ]);

        Sales::create($validated);

        return redirect()->route('sales.index')
            ->with('success', 'Sale created successfully');
    }
}
