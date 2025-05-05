<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Product;
use App\Sale;
use App\Sales;
use App\Supplier;
use App\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
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
        $invoices = Invoice::all();
        return view('invoice.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $customers = Customer::all();
        $products = Product::all();
        return view('invoice.create', compact('customers','products'));
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
            'customer_id' => 'required|exists:customers,id',
            'product_id' => 'sometimes|array|min:1',
            'product_id.*' => 'sometimes|exists:products,id',
            'qty' => 'required|array|min:1',
            'qty.*' => 'numeric|min:0.01',
            'price' => 'required|array|min:1',
            'price.*' => 'numeric|min:0.01',
            'dis' => 'required|array|min:1',
            'dis.*' => 'numeric|min:0|max:100',
            'amount' => 'required|array|min:1',
            'amount.*' => 'numeric|min:0.01',
            'qty' => 'required|array|min:1',
            'qty.*' => 'numeric|min:0.01',
            'price' => 'required|array|min:1',
            'price.*' => 'numeric|min:0.01',
            'dis' => 'required|array|min:1',
            'dis.*' => 'numeric|min:0|max:100',
            'amount' => 'required|array|min:1',
            'amount.*' => 'numeric|min:0.01',
        ]);

        // Calculate total from all product amounts
        $total = array_sum($request->amount);

        $invoice = new Invoice();
        $invoice->customer_id = $request->customer_id;
        $invoice->total = $total;
        $invoice->save();

        foreach ($request->product_id as $key => $product_id) {
            // Validate product exists
            if (!Product::where('id', $product_id)->exists()) {
                return back()
                    ->withErrors(['product_id' => 'Invalid product selected'])
                    ->withInput();
            }

            // Validate and cast product ID
            $product_id = (int)$product_id;
            if ($product_id <= 0 || !Product::where('id', $product_id)->exists()) {
                continue;
            }

            try {
                $sale = new Sale();
                $sale->qty = $request->qty[$key];
                $sale->price = $request->price[$key];
                $sale->dis = $request->dis[$key];
                $sale->amount = $request->amount[$key];
                $sale->product_id = $product_id ?: null; // Allow null for custom orders
                $sale->invoice_id = $invoice->id;
                $sale->save();
            } catch (\Exception $e) {
                return back()
                    ->withErrors(['product_id' => 'Invalid product selection: '.$e->getMessage()])
                    ->withInput();
            }
        }

        return redirect('invoice/'.$invoice->id)->with('message','Invoice created Successfully');


    }

    public function findPrice(Request $request){
        // Log the request for debugging
        Log::info('findPrice called with ID: ' . $request->id);
        
        if (!$request->has('id') || !$request->id) {
            Log::error('findPrice: No product ID provided');
            return response()->json(['error' => 'No product ID provided'], 400);
        }
        
        try {
            $product = DB::table('products')->where('id', $request->id)->first();
            
            if (!$product) {
                Log::error('findPrice: Product not found with ID: ' . $request->id);
                return response()->json(['error' => 'Product not found'], 404);
            }
            
            Log::info('findPrice: Product found, sales_price: ' . $product->sales_price);
            
            return response()->json([
                'success' => true,
                'sales_price' => $product->sales_price,
                'product_name' => $product->name
            ]);
        } catch (\Exception $e) {
            Log::error('findPrice error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $invoice = Invoice::findOrFail($id);
        $sales = Sale::where('invoice_id', $id)->get();
        return view('invoice.show', compact('invoice','sales'));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $customers = Customer::all();
        $products = Product::orderBy('id', 'DESC')->get();
        $invoice = Invoice::findOrFail($id);
        $sales = Sale::where('invoice_id', $id)->get();
        return view('invoice.edit', compact('customers','products','invoice','sales'));
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
        $request->validate([
            'customer_id' => 'required',
            'product_id' => 'nullable', // Optional for custom orders
            'qty' => 'required',
            'price' => 'required',
            'dis' => 'required',
            'amount' => 'required',
        ]);

        // Calculate total from all product amounts
        $total = array_sum($request->amount);

        $invoice = Invoice::findOrFail($id);
        $invoice->customer_id = $request->customer_id;
        $invoice->total = $total;
        $invoice->save();

        Sale::where('invoice_id', $id)->delete();

        foreach ($request->product_id as $key => $product_id) {
            $sale = new Sale();
            $sale->qty = $request->qty[$key];
            $sale->price = $request->price[$key];
            $sale->dis = $request->dis[$key];
            $sale->amount = $request->amount[$key];
            $sale->product_id = $request->product_id[$key];
            $sale->invoice_id = $invoice->id;
            $sale->save();
        }

        return redirect('invoice/'.$invoice->id)->with('message','Invoice updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {
        Sales::where('invoice_id', $id)->delete();
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();
        return redirect()->back();

    }
}
