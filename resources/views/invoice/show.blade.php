@extends('layouts.master')

@section('title', 'Invoice | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-file-text-o"></i> Invoice</h1>
                <p>A Printable Invoice Format</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item"><a href="#">Invoice</a></li>
            </ul>
        </div>
        @if(session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <section class="invoice">
                        <div class="row mb-4">
                            <div class="col-6">
                                <h2 class="page-header"><i class="fa fa-file"></i> I M S</h2>
                            </div>
                            <div class="col-6">
                                <h5 class="text-right">Date: {{$invoice->created_at->format('Y-m-d')}}</h5>
                            </div>
                        </div>
                        <div class="row invoice-info">
                            <div class="col-4">From
                                <address><strong>CodeAstro</strong><br>Demo,<br>Address<br>codeastro.com</address>
                            </div>
                            <div class="col-4">To
                                 <address><strong>{{$invoice->customer->name}}</strong><br>{{$invoice->customer->address}}<br>Phone: {{$invoice->customer->mobile}}<br>Email: {{$invoice->customer->email}}</address>
                             </div>
                            <div class="col-4"><b>Invoice #{{1000+$invoice->id}}</b><br><br><b>Order ID:</b> 4F3S8J<br><b>Payment Due:</b> {{$invoice->created_at->format('Y-m-d')}}<br><b>Account:</b> 000-12345</div>
                        </div>
                        <div class="row">
                            <div class="col-12 table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Discount</th>
                                        <th>Amount</th>
                                     </tr>
                                    </thead>
                                    <tbody>
                                    <div style="display: none">
                                        {{$total=0}}
                                    </div>
                                    @if($invoice->custom_order_id)
                                    <tr>
                                        <td colspan="5"><strong>Custom Order Details:</strong></td>
                                    </tr>
                                    <tr>
                                        <td>{{$invoice->customOrder->job_type}}</td>
                                        <td>{{$invoice->customOrder->quantity}}</td>
                                        <td>{{$invoice->customOrder->price_per_square_feet}}</td>
                                        <td>-</td>
                                        <td>{{$invoice->customOrder->total_price}}</td>
                                    </tr>
                                    @if($invoice->customOrder->job_type === 'flag')
                                    <tr>
                                        <td colspan="5">
                                            <table class="table table-sm table-bordered mt-2">
                                                <tr>
                                                    <th width="30%">Flag Height × Breadth</th>
                                                    <td>
                                                        @if(isset($invoice->customOrder->height) && isset($invoice->customOrder->breadth))
                                                            {{$invoice->customOrder->height}} × {{$invoice->customOrder->breadth}} ft
                                                        @else
                                                            0.00 × 0.00 ft
                                                        @endif
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    @elseif($invoice->customOrder->job_type === 'product')
                                    <tr>
                                        <td colspan="5">
                                            Product Details: {{$invoice->customOrder->product->name ?? 'N/A'}}<br>
                                            Category: {{$invoice->customOrder->product->category->name ?? 'N/A'}}<br>
                                            Price: Rs. {{$invoice->customOrder->product->price ?? 'N/A'}}
                                        </td>
                                    </tr>
                                    @endif
                                    @else
                                    @foreach($sales as $sale)
                                    <tr>
                                        <td>{{$sale->product->name}}</td>
                                        <td>{{$sale->qty}}</td>
                                        <td>{{$sale->price}}</td>
                                        <td>{{$sale->dis}}%</td>
                                        <td>{{$sale->amount}}</td>
                                        <div style="display: none">
                                            {{$total +=$sale->amount}}
                                        </div>
                                     </tr>
                                    @endforeach
                                    @endif
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td><b>Total</b></td>
                                        <td><b class="total">{{$total}}</b></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="row d-print-none mt-2">
                            <div class="col-12 text-right"><a class="btn btn-primary" href="javascript:void(0);" onclick="printInvoice();"><i class="fa fa-print"></i> Print</a></div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>


    <script>
    function printInvoice() {
        window.print();
    }
    </script>

@endsection
@push('js')
@endpush





