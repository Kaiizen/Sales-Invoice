@extends('layouts.master')
@section('title', 'View Custom Order')

@section('content')
    @include('partials.header')
    @include('partials.sidebar')
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-shopping-cart"></i> Order Details #{{ $customOrder->id }}</h1>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item"><a href="{{ route('custom-orders.index') }}">Custom Orders</a></li>
                <li class="breadcrumb-item">View Order</li>
            </ul>
        </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                    <h5 class="mb-0"><i class="fa fa-file-text-o"></i> Order Details #{{ $customOrder->id }}</h5>
                    <div>
                        <a href="{{ route('custom-orders.index') }}" class="btn btn-light btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('custom-orders.edit', ['custom_order' => $customOrder->id]) }}" class="btn btn-warning btn-sm">
                            <i class="fa fa-edit"></i> Edit Order
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Action buttons -->
                    <div class="mb-4 text-right">
                        <form method="POST" action="{{ route('custom-orders.destroy', ['custom_order' => $customOrder->id]) }}" class="d-inline"
                              onsubmit="return confirm('Are you sure you want to delete this order? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fa fa-trash"></i> Delete Order
                            </button>
                        </form>
                        
                        @if($customOrder->status === 'Ready')
                            <form method="POST" action="{{ route('custom-orders.create-invoice', ['custom_order' => $customOrder->id]) }}" class="d-inline ml-2">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-file-invoice"></i> Create Invoice
                                </button>
                            </form>
                        @endif
                    </div>
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fa fa-check-circle mr-2"></i> <strong>Success!</strong> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    
                    <!-- Order Created Alert -->
                    <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fa fa-info-circle fa-2x mr-3"></i>
                            <div>
                                <h5 class="alert-heading mb-1">Order #{{ $customOrder->id }} Details</h5>
                                <p class="mb-0">You are viewing the complete details for this order. All items and specifications are displayed below.</p>
                            </div>
                        </div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="row">
                        <!-- Customer Information -->
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fa fa-user"></i> Customer Information</h5>
                                </div>
                                <div class="card-body">
                                    @if($customOrder->customer)
                                        <h5>{{ $customOrder->customer->name }}</h5>
                                        <p><i class="fa fa-envelope"></i> {{ $customOrder->customer->email }}</p>
                                        <p><i class="fa fa-phone"></i> {{ $customOrder->customer->phone }}</p>
                                        <p><i class="fa fa-map-marker"></i> {{ $customOrder->customer->address }}</p>
                                        <a href="{{ route('customers.show', $customOrder->customer) }}" class="btn btn-sm btn-info">
                                            <i class="fa fa-user"></i> View Customer Profile
                                        </a>
                                    @else
                                        <p class="text-muted">No customer information available</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Order Information -->
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fa fa-info-circle"></i> Order Information</h5>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-5">Order ID</dt>
                                        <dd class="col-sm-7">#{{ $customOrder->id }}</dd>
                                        
                                        <dt class="col-sm-5">Order Type</dt>
                                        <dd class="col-sm-7">
                                            @if($customOrder->flagDetails->count() > 0)
                                                @php
                                                    $types = [];
                                                    foreach($customOrder->flagDetails as $detail) {
                                                        if (!in_array($detail->flag_type, $types)) {
                                                            $types[] = $detail->flag_type;
                                                        }
                                                    }
                                                @endphp
                                                {{ implode(', ', $types) }}
                                            @else
                                                {{ $customOrder->flag_type }}
                                            @endif
                                        </dd>
                                        
                                        <dt class="col-sm-5">Quantity</dt>
                                        <dd class="col-sm-7">
                                            @if($customOrder->flagDetails->count() > 0)
                                                @php
                                                    $quantities = [];
                                                    foreach($customOrder->flagDetails as $detail) {
                                                        $quantities[] = $detail->flag_type . ': ' . $detail->quantity;
                                                    }
                                                @endphp
                                                {{ implode(', ', $quantities) }}
                                            @else
                                                {{ $customOrder->quantity }}
                                            @endif
                                        </dd>
                                        
                                        <dt class="col-sm-5">Total Price</dt>
                                        <dd class="col-sm-7">Rs. {{ number_format($customOrder->total_price, 2) }}</dd>
                                        
                                        <dt class="col-sm-5">Created Date</dt>
                                        <dd class="col-sm-7">{{ $customOrder->created_at->setTimezone('Asia/Katmandu')->format('Y-m-d') }}</dd>
                                        
                                        <dt class="col-sm-5">Created Time</dt>
                                        <dd class="col-sm-7">{{ $customOrder->created_at->setTimezone('Asia/Katmandu')->format('H:i:s') }}</dd>
                                        
                                        <dt class="col-sm-5">Last Updated</dt>
                                        <dd class="col-sm-7">{{ $customOrder->updated_at->setTimezone('Asia/Katmandu')->format('Y-m-d H:i:s') }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Status Information -->
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0"><i class="fa fa-tasks"></i> Status Information</h5>
                                </div>
                                <div class="card-body">
                                    <h5 class="mb-3">Current Status:
                                        <span class="badge badge-{{ $customOrder->status === 'Pending' ? 'warning' : ($customOrder->status === 'In Production' ? 'info' : ($customOrder->status === 'Ready' ? 'success' : 'primary')) }}">
                                            {{ $customOrder->status }}
                                        </span>
                                    </h5>
                                    
                                    <form method="POST" action="{{ route('custom-orders.update-status', ['custom_order' => $customOrder->id]) }}" class="mb-4">
                                        @csrf
                                        @method('PATCH')
                                        <div class="form-group">
                                            <label for="status"><strong>Update Status:</strong></label>
                                            <div class="input-group">
                                                <select name="status" id="status" class="form-control">
                                                    @foreach(App\CustomOrder::STATUSES as $status)
                                                        <option value="{{ $status }}" {{ $customOrder->status === $status ? 'selected' : '' }}>
                                                            {{ $status }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="input-group-append">
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    
                                    <div class="status-timeline">
                                        <h6>Status Timeline:</h6>
                                        <ul class="list-group">
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Created
                                                <span class="badge badge-primary badge-pill">{{ $customOrder->created_at->setTimezone('Asia/Katmandu')->format('Y-m-d H:i') }}</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center {{ $customOrder->status == 'Pending' ? 'active' : '' }}">
                                                Pending
                                                <span class="badge badge-warning badge-pill">
                                                    {{ $customOrder->created_at->setTimezone('Asia/Katmandu')->format('Y-m-d H:i') }}
                                                </span>
                                            </li>
                                            @if($customOrder->status != 'Pending')
                                                <li class="list-group-item d-flex justify-content-between align-items-center {{ $customOrder->status == 'In Production' ? 'active' : '' }}">
                                                    In Production
                                                    <span class="badge badge-info badge-pill">
                                                        {{ $customOrder->updated_at->setTimezone('Asia/Katmandu')->format('Y-m-d H:i') }}
                                                    </span>
                                                </li>
                                            @endif
                                            @if($customOrder->status == 'Ready' || $customOrder->status == 'Delivered')
                                                <li class="list-group-item d-flex justify-content-between align-items-center {{ $customOrder->status == 'Ready' ? 'active' : '' }}">
                                                    Ready
                                                    <span class="badge badge-success badge-pill">
                                                        {{ $customOrder->updated_at->setTimezone('Asia/Katmandu')->format('Y-m-d H:i') }}
                                                    </span>
                                                </li>
                                            @endif
                                            @if($customOrder->status == 'Delivered')
                                                <li class="list-group-item d-flex justify-content-between align-items-center active">
                                                    Delivered
                                                    <span class="badge badge-primary badge-pill">
                                                        {{ $customOrder->updated_at->setTimezone('Asia/Katmandu')->format('Y-m-d H:i') }}
                                                    </span>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="mt-4">
                        <!-- Calculate totals -->
                        @php
                            $totalItemsCount = 0;
                            $fabricItemsCount = 0;
                            $productItemsCount = 0;
                            
                            // Arrays to store quantities by product/fabric
                            $productQuantities = [];
                            $fabricQuantities = [];
                            
                            if($customOrder->flagDetails && $customOrder->flagDetails->count() > 0) {
                                $totalItemsCount = $customOrder->flagDetails->count();
                                foreach($customOrder->flagDetails as $flagDetail) {
                                    if($flagDetail->flag_type === 'flag') {
                                        $fabricItemsCount++;
                                        
                                        // Group fabric quantities by product ID
                                        $fabricKey = $flagDetail->product_id ?? 'custom_fabric';
                                        if (!isset($fabricQuantities[$fabricKey])) {
                                            $fabricQuantities[$fabricKey] = [
                                                'name' => $flagDetail->product ? $flagDetail->product->name : ($flagDetail->notes ?? 'Fabric Item'),
                                                'quantity' => 0
                                            ];
                                        }
                                        $fabricQuantities[$fabricKey]['quantity'] += $flagDetail->quantity;
                                    } else {
                                        $productItemsCount++;
                                        
                                        // Group product quantities by product ID
                                        $productKey = $flagDetail->product_id ?? 'custom_product';
                                        if (!isset($productQuantities[$productKey])) {
                                            $productQuantities[$productKey] = [
                                                'name' => $flagDetail->product ? $flagDetail->product->name : ($flagDetail->notes ?? 'Product Item'),
                                                'quantity' => 0
                                            ];
                                        }
                                        $productQuantities[$productKey]['quantity'] += $flagDetail->quantity;
                                    }
                                }
                            }
                        @endphp
                        
                        <div class="card mb-4">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0"><i class="fa fa-list"></i> Order Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> Order #{{ $customOrder->id }} was created on {{ $customOrder->created_at->setTimezone('Asia/Katmandu')->format('F j, Y') }} and is currently <strong>{{ $customOrder->status }}</strong>.
                                </div>
                                
                                <p>This order contains the following items:</p>
                                <p><strong>Total Items:</strong> {{ $totalItemsCount }} ({{ $fabricItemsCount }} fabric/flag items and {{ $productItemsCount }} product items)</p>
                                
                                @if(isset($totalFabricArea) && $totalFabricArea > 0)
                                <p>
                                    <strong>Total Fabric Area:</strong>
                                    <span class="badge badge-info">{{ number_format($totalFabricArea, 2) }} sq.ft</span>
                                </p>
                                @endif
                                
                                <p><strong>Total Order Value:</strong> <span class="text-success font-weight-bold">Rs. {{ number_format($customOrder->total_price, 2) }}</span></p>
                                
                                <!-- Product Items Section -->
                                @if($productItemsCount > 0)
                                <div class="mt-4">
                                    <h5 class="border-bottom pb-2"><i class="fa fa-shopping-cart"></i> Product Items</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Product Name</th>
                                                    <th>Quantity</th>
                                                    <th>Price</th>
                                                    <th>Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <div style="display: none">
                                                    {{$totalProductAmount = 0}}
                                                </div>
                                                @foreach($productQuantities as $productKey => $product)
                                                @php
                                                    // Find the product detail to get the price
                                                    $productDetail = null;
                                                    foreach($customOrder->flagDetails as $detail) {
                                                        if($detail->flag_type !== 'flag' && ($detail->product_id == $productKey || ($productKey == 'custom_product' && !$detail->product_id))) {
                                                            $productDetail = $detail;
                                                            break;
                                                        }
                                                    }
                                                    $price = $productDetail ? $productDetail->price_per_square_feet : 0;
                                                    $amount = $product['quantity'] * $price;
                                                    $totalProductAmount += $amount;
                                                @endphp
                                                <tr>
                                                    <td>{{ $product['name'] }}</td>
                                                    <td>{{ $product['quantity'] }}</td>
                                                    <td>Rs. {{ number_format($price, 2) }}</td>
                                                    <td>Rs. {{ number_format($amount, 2) }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td><b>Total</b></td>
                                                    <td><b class="total">Rs. {{ number_format($totalProductAmount, 2) }}</b></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                @endif
                                
                                <!-- Fabric Items Section -->
                                @if($fabricItemsCount > 0)
                                <div class="mt-4">
                                    <h5 class="border-bottom pb-2"><i class="fa fa-flag"></i> Fabric Items</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Product Name</th>
                                                    <th>Quantity</th>
                                                    <th>Price</th>
                                                    <th>Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <div style="display: none">
                                                    {{$totalFabricAmount = 0}}
                                                </div>
                                                @foreach($fabricQuantities as $fabricKey => $fabric)
                                                @php
                                                    // Find the fabric detail to get the price
                                                    $fabricDetail = null;
                                                    foreach($customOrder->flagDetails as $detail) {
                                                        if($detail->flag_type === 'flag' && ($detail->product_id == $fabricKey || ($fabricKey == 'custom_fabric' && !$detail->product_id))) {
                                                            $fabricDetail = $detail;
                                                            break;
                                                        }
                                                    }
                                                    $price = $fabricDetail ? $fabricDetail->price_per_square_feet : 0;
                                                    $amount = $fabric['quantity'] * $price;
                                                    $totalFabricAmount += $amount;
                                                @endphp
                                                <tr>
                                                    <td>{{ $fabric['name'] }}</td>
                                                    <td>{{ $fabric['quantity'] }}</td>
                                                    <td>Rs. {{ number_format($price, 2) }}</td>
                                                    <td>Rs. {{ number_format($amount, 2) }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td><b>Total</b></td>
                                                    <td><b class="total">Rs. {{ number_format($totalFabricAmount, 2) }}</b></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                @endif
                                
                                @if($customOrder->flagDetails && $customOrder->flagDetails->count() > 0)
                                    <div class="mt-3">
                                        <h6 class="font-weight-bold">Order Items:</h6>
                                        <ul class="list-group">
                                            @foreach($customOrder->flagDetails as $detail)
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span>
                                                        <i class="fa {{ $detail->flag_type === 'flag' ? 'fa-flag text-info' : 'fa-shopping-cart text-success' }}"></i>
                                                        @if($detail->product)
                                                            {{ $detail->product->name }}
                                                        @else
                                                            {{ $detail->notes ?? ($detail->flag_type === 'flag' ? 'Fabric Item' : 'Product Item') }}
                                                        @endif
                                                        ({{ $detail->quantity }} {{ $detail->quantity > 1 ? 'units' : 'unit' }})
                                                    </span>
                                                    <span class="badge badge-success badge-pill">Rs. {{ number_format($detail->total_price, 2) }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Order Description Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fa fa-file-text-o"></i> Order Description</h5>
                            </div>
                            <div class="card-body">
                                <div class="order-description">
                                        <h5 class="border-bottom pb-2">Order Generated Description</h5>
                                        <div class="p-3 bg-white border rounded">
                                            <p class="mb-1"><i class="fa fa-shopping-cart text-primary"></i> <strong>Order #{{ $customOrder->id }}</strong></p>
                                            <p class="mb-1">
                                                <strong>Customer:</strong>
                                                @if($customOrder->customer)
                                                    {{ $customOrder->customer->name }}
                                                @else
                                                    Not specified
                                                @endif
                                            </p>
                                            <p class="mb-1">
                                                <strong>Order Type:</strong>
                                                @if($customOrder->flagDetails->count() > 0)
                                                    @php
                                                        $types = [];
                                                        foreach($customOrder->flagDetails as $detail) {
                                                            if (!in_array($detail->flag_type, $types)) {
                                                                $types[] = $detail->flag_type;
                                                            }
                                                        }
                                                    @endphp
                                                    {{ implode(', ', $types) }}
                                                @else
                                                    {{ $customOrder->flag_type }}
                                                @endif
                                            </p>
                                            <p class="mb-1">
                                                <strong>Item:</strong>
                                                @if($customOrder->flagDetails->count() > 0)
                                                    @php
                                                        $quantities = [];
                                                        foreach($customOrder->flagDetails as $detail) {
                                                            $quantities[] = $detail->flag_type . ': ' . $detail->quantity;
                                                        }
                                                    @endphp
                                                    {{ implode(', ', $quantities) }}
                                                @else
                                                    {{ $customOrder->fabric_type ?? 'Fabric' }} -
                                                    {{ $customOrder->quantity }} {{ $customOrder->quantity > 1 ? 'pieces' : 'piece' }}
                                                @endif
                                            </p>
                                            <p class="mb-1">
                                                <strong>Dimensions:</strong>
                                                @if(isset($customOrder->height) && isset($customOrder->breadth))
                                                    {{ $customOrder->height }} × {{ $customOrder->breadth }} ft
                                                    (Total area: {{ number_format($customOrder->square_feet, 2) }} sq.ft)
                                                @else
                                                    Varies by item
                                                @endif
                                            </p>
                                            <p class="mb-1">
                                                <strong>Specifications:</strong>
                                                Stitching: {{ $customOrder->stitching_option ? 'Yes' : 'No' }}
                                            </p>
                                            <p class="mb-1">
                                                <strong>Status:</strong> <span class="badge badge-{{ $customOrder->status === 'Pending' ? 'warning' : ($customOrder->status === 'In Production' ? 'info' : ($customOrder->status === 'Ready' ? 'success' : 'primary')) }}">
                                                    {{ $customOrder->status }}
                                                </span>
                                            </p>
                                            <p class="mb-0">
                                                <strong>Total Value:</strong>
                                                <span class="text-success font-weight-bold">Rs. {{ number_format($customOrder->total_price, 2) }}</span>
                                            </p>
                                        </div>
                                    </div>
                                            
                                            @if($customOrder->flagDetails && $customOrder->flagDetails->count() > 0)
                                                @foreach($customOrder->flagDetails as $flagDetail)
                                                    @if($flagDetail->flag_type === 'flag')
                                                        <div class="card mb-3" id="flag-{{ $flagDetail->id }}" style="border-left: 4px solid #17a2b8;">
                                                            <div class="card-header bg-light">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <h5 class="mb-0">
                                                                        <i class="fa fa-flag text-info"></i>
                                                                        @if($flagDetail->product)
                                                                            {{ $flagDetail->product->name }}
                                                                        @else
                                                                            {{ $flagDetail->notes ?? 'Fabric Item' }}
                                                                        @endif
                                                                    </h5>
                                                                    <span class="badge badge-info">Flag #{{ $flagDetail->id }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <div class="col-md-8">
                                                                        
                                                                        @if($flagDetail->product)
                                                                            <div class="product-details mb-3">
                                                                                <table class="table table-sm table-bordered">
                                                                                    <tbody>
                                                                                        @if($flagDetail->product->code)
                                                                                        <tr>
                                                                                            <th width="30%">Product Code</th>
                                                                                            <td>{{ $flagDetail->product->code }}</td>
                                                                                        </tr>
                                                                                        @endif
                                                                                        
                                                                                        @if($flagDetail->product->model)
                                                                                        <tr>
                                                                                            <th>Model</th>
                                                                                            <td>{{ $flagDetail->product->model }}</td>
                                                                                        </tr>
                                                                                        @endif
                                                                                        
                                                                                        @if($flagDetail->product->serial_number)
                                                                                        <tr>
                                                                                            <th>Serial Number</th>
                                                                                            <td>{{ $flagDetail->product->serial_number }}</td>
                                                                                        </tr>
                                                                                        @endif
                                                                                        
                                                                                        @if($flagDetail->product->category)
                                                                                        <tr>
                                                                                            <th>Category</th>
                                                                                            <td>{{ $flagDetail->product->category->name }}</td>
                                                                                        </tr>
                                                                                        @endif
                                                                                        
                                                                                        @if($flagDetail->product->unit)
                                                                                        <tr>
                                                                                            <th>Unit</th>
                                                                                            <td>{{ $flagDetail->product->unit->name }}</td>
                                                                                        </tr>
                                                                                        @endif
                                                                                        
                                                                                        <tr>
                                                                                            <th>Dimensions</th>
                                                                                            <td>
                                                                                                <span class="badge badge-light">
                                                                                                    {{ $flagDetail->height }} × {{ $flagDetail->breadth }} ft
                                                                                                </span>
                                                                                                <span class="badge badge-info ml-2">
                                                                                                    {{ $flagDetail->height * 12 }} × {{ $flagDetail->breadth * 12 }} inches
                                                                                                </span>
                                                                                            </td>
                                                                                        </tr>
                                                                                        
                                                                                        <tr>
                                                                                            <th>Square Feet</th>
                                                                                            <td>
                                                                                                <span class="badge badge-secondary">
                                                                                                    {{ number_format($flagDetail->square_feet, 2) }} sq.ft per unit
                                                                                                </span>
                                                                                                <span class="badge badge-success ml-2">
                                                                                                    {{ number_format($flagDetail->square_feet * $flagDetail->quantity, 2) }} sq.ft total
                                                                                                </span>
                                                                                            </td>
                                                                                        </tr>
                                                                                        
                                                                                        <tr>
                                                                                            <th>Stitching</th>
                                                                                            <td>
                                                                                                @if($flagDetail->stitching)
                                                                                                    <span class="badge badge-success">Yes</span>
                                                                                                @else
                                                                                                    <span class="badge badge-secondary">No</span>
                                                                                                @endif
                                                                                            </td>
                                                                                        </tr>
                                                                                    </tbody>
                                                                                </table>
                                                                            </div>
                                                                        @else
                                                                            <div class="fabric-details mb-3">
                                                                                <table class="table table-sm table-bordered">
                                                                                    <tbody>
                                                                                        <tr>
                                                                                            <th width="30%">Dimensions</th>
                                                                                            <td>
                                                                                                <span class="badge badge-light">{{ $flagDetail->height }} × {{ $flagDetail->breadth }} ft</span>
                                                                                                <span class="badge badge-info ml-2">{{ $flagDetail->height * 12 }} × {{ $flagDetail->breadth * 12 }} inches</span>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <th>Square Feet</th>
                                                                                            <td>
                                                                                                <span class="badge badge-secondary">{{ number_format($flagDetail->square_feet, 2) }} sq.ft per unit</span>
                                                                                                <span class="badge badge-success ml-2">{{ number_format($flagDetail->square_feet * $flagDetail->quantity, 2) }} sq.ft total</span>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <th>Stitching</th>
                                                                                            <td>
                                                                                                @if($flagDetail->stitching)
                                                                                                    <span class="badge badge-success">Yes</span>
                                                                                                @else
                                                                                                    <span class="badge badge-secondary">No</span>
                                                                                                @endif
                                                                                            </td>
                                                                                        </tr>
                                                                                    </tbody>
                                                                                </table>
                                                                            </div>
                                                                        @endif
                                                                        
                                                                        <div class="description mb-3">
                                                                            <h6 class="font-weight-bold">Description:</h6>
                                                                            <p>
                                                                                @if($flagDetail->product)
                                                                                    {{ $flagDetail->product->name }}
                                                                                @else
                                                                                    {{ $flagDetail->notes ?? 'Standard fabric' }}
                                                                                @endif
                                                                            </p>
                                                                        </div>
                                                                        
                                                                        <div class="order-description mb-3">
                                                                            <h6 class="font-weight-bold">Order Generated Description:</h6>
                                                                            <div class="p-3 bg-white border rounded">
                                                                                <p class="mb-1"><i class="fa fa-shopping-cart text-primary"></i> <strong>Order #{{ $customOrder->id }}</strong></p>
                                                                                <p class="mb-1">
                                                                                    This order includes {{ $flagDetail->quantity }}
                                                                                    {{ $flagDetail->quantity > 1 ? 'pieces' : 'piece' }} of
                                                                                    @if($flagDetail->product)
                                                                                        <strong>{{ $flagDetail->product->name }}</strong>
                                                                                    @else
                                                                                        <strong>{{ $flagDetail->notes ?? 'fabric' }}</strong>
                                                                                    @endif
                                                                                </p>
                                                                                <p class="mb-1">
                                                                                    <strong>Dimensions:</strong> {{ $flagDetail->height }} × {{ $flagDetail->breadth }} ft
                                                                                    (Total area: {{ number_format($flagDetail->square_feet, 2) }} sq.ft)
                                                                                </p>
                                                                                <p class="mb-1">
                                                                                    <strong>Specifications:</strong>
                                                                                    @if($flagDetail->product && $flagDetail->product->model)
                                                                                        Model: {{ $flagDetail->product->model }},
                                                                                    @endif
                                                                                    @if($flagDetail->product && $flagDetail->product->serial_number)
                                                                                        Serial: {{ $flagDetail->product->serial_number }},
                                                                                    @endif
                                                                                    Stitching: {{ $flagDetail->stitching ? 'Yes' : 'No' }}
                                                                                </p>
                                                                                <p class="mb-1">
                                                                                    <strong>Pricing:</strong> Rs. {{ number_format($flagDetail->price_per_square_feet, 2) }} per sq.ft
                                                                                    × {{ number_format($flagDetail->square_feet, 2) }} sq.ft
                                                                                    × {{ $flagDetail->quantity }} {{ $flagDetail->quantity > 1 ? 'pieces' : 'piece' }}
                                                                                </p>
                                                                                <p class="mb-0">
                                                                                    <strong>Total Value:</strong>
                                                                                    <span class="text-success font-weight-bold">Rs. {{ number_format($flagDetail->total_price, 2) }}</span>
                                                                                </p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="col-md-4">
                                                                        @if($flagDetail->product && $flagDetail->product->image)
                                                                            <img src="{{ asset('storage/' . $flagDetail->product->image) }}"
                                                                                alt="{{ $flagDetail->product->name }}"
                                                                                class="img-fluid rounded mb-3">
                                                                        @else
                                                                            <img src="{{ asset('images/product/default.jpg') }}"
                                                                                alt="Default Fabric Image"
                                                                                class="img-fluid rounded mb-3">
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="order-details mt-3 p-3 bg-light border rounded">
                                                                    <h6 class="font-weight-bold">Order Information:</h6>
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <p><strong>Quantity:</strong> {{ $flagDetail->quantity }} {{ $flagDetail->quantity > 1 ? 'pieces' : 'piece' }}</p>
                                                                            <p><strong>Price per Sq.Ft:</strong> Rs. {{ number_format($flagDetail->price_per_square_feet, 2) }}</p>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <p><strong>Total Price:</strong>
                                                                                <span class="text-success font-weight-bold">
                                                                                    Rs. {{ number_format($flagDetail->total_price, 2) }}
                                                                                </span>
                                                                            </p>
                                                                            @if($flagDetail->product && $flagDetail->product->total_square_feet !== null)
                                                                                <p><strong>Available Fabric:</strong> {{ number_format($flagDetail->product->total_square_feet, 2) }} sq.ft</p>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @elseif($customOrder->flag_type)
                                                <div class="card bg-light mb-3">
                                                    <div class="card-body">
                                                        <h5 class="card-title">
                                                            <i class="fa fa-flag text-info"></i>
                                                            {{ $customOrder->fabric_type ?? 'Fabric Item' }}
                                                        </h5>
                                                        <p>
                                                            This order includes {{ $customOrder->quantity }}
                                                            {{ $customOrder->quantity > 1 ? 'pieces' : 'piece' }} of fabric
                                                            with dimensions {{ $customOrder->height }} × {{ $customOrder->breadth }}
                                                            (total area: {{ number_format($customOrder->square_feet, 2) }} sq.ft).
                                                        </p>
                                                        <p>
                                                            The fabric is priced at Rs. {{ number_format($customOrder->price_per_square_feet, 2) }}
                                                            per square foot and {{ $customOrder->stitching_option ? 'includes' : 'does not include' }} stitching.
                                                        </p>
                                                        <p class="mb-0">
                                                            <strong>Total cost for this fabric:</strong>
                                                            <span class="text-success font-weight-bold">
                                                                Rs. {{ number_format($customOrder->total_price, 2) }}
                                                            </span>
                                                        </p>
                                                        
                                                        <div class="order-description mt-4">
                                                            <h6 class="font-weight-bold border-top pt-3">Order Generated Description:</h6>
                                                            <div class="p-3 bg-white border rounded mt-2">
                                                                <p class="mb-1"><i class="fa fa-shopping-cart text-primary"></i> <strong>Order #{{ $customOrder->id }}</strong></p>
                                                                <p class="mb-1">
                                                                    <strong>Customer:</strong>
                                                                    @if($customOrder->customer)
                                                                        {{ $customOrder->customer->name }}
                                                                    @else
                                                                        Not specified
                                                                    @endif
                                                                </p>
                                                                <p class="mb-1">
                                                                    <strong>Order Type:</strong> {{ $customOrder->flag_type }}
                                                                </p>
                                                                <p class="mb-1">
                                                                    <strong>Item:</strong> {{ $customOrder->fabric_type ?? 'Fabric' }} -
                                                                    {{ $customOrder->quantity }} {{ $customOrder->quantity > 1 ? 'pieces' : 'piece' }}
                                                                </p>
                                                                <p class="mb-1">
                                                                    <strong>Dimensions:</strong> {{ $customOrder->height }} × {{ $customOrder->breadth }} ft
                                                                    (Total area: {{ number_format($customOrder->square_feet, 2) }} sq.ft)
                                                                </p>
                                                                <p class="mb-1">
                                                                    <strong>Specifications:</strong>
                                                                    Stitching: {{ $customOrder->stitching_option ? 'Yes' : 'No' }}
                                                                </p>
                                                                <p class="mb-1">
                                                                    <strong>Pricing:</strong> Rs. {{ number_format($customOrder->price_per_square_feet, 2) }} per sq.ft
                                                                    × {{ number_format($customOrder->square_feet, 2) }} sq.ft
                                                                    × {{ $customOrder->quantity }} {{ $customOrder->quantity > 1 ? 'pieces' : 'piece' }}
                                                                </p>
                                                                <p class="mb-1">
                                                                    <strong>Status:</strong> <span class="badge badge-{{ $customOrder->status === 'Pending' ? 'warning' : ($customOrder->status === 'In Production' ? 'info' : ($customOrder->status === 'Ready' ? 'success' : 'primary')) }}">
                                                                        {{ $customOrder->status }}
                                                                    </span>
                                                                </p>
                                                                <p class="mb-0">
                                                                    <strong>Total Value:</strong>
                                                                    <span class="text-success font-weight-bold">Rs. {{ number_format($customOrder->total_price, 2) }}</span>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Product Items Section -->
                        @php
                            $hasProductItems = false;
                            if($customOrder->flagDetails && $customOrder->flagDetails->count() > 0) {
                                foreach($customOrder->flagDetails as $flagDetail) {
                                    if($flagDetail->flag_type !== 'flag') {
                                        $hasProductItems = true;
                                        break;
                                    }
                                }
                            }
                        @endphp
                        
                        @if($hasProductItems && $customOrder->flagDetails->where('flag_type', '!=', 'flag')->count() > 0)
                            <div class="card mb-4">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="mb-0"><i class="fa fa-shopping-cart"></i> Product Items</h5>
                                </div>
                                
                                <!-- Product Quantity Summary -->
                                @if(count($productQuantities) > 0)
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Total Product Quantities</h6>
                                    <div class="table-responsive mt-2">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Product Name</th>
                                                    <th>Quantity</th>
                                                    <th>Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $productPrices = [];
                                                    
                                                    // Collect prices for each product
                                                    foreach($customOrder->flagDetails as $flagDetail) {
                                                        if($flagDetail->flag_type !== 'flag') {
                                                            $productKey = $flagDetail->product_id ?? 'custom_product';
                                                            
                                                            if (!isset($productPrices[$productKey])) {
                                                                $productPrices[$productKey] = $flagDetail->price_per_square_feet;
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                
                                                @foreach($productQuantities as $productKey => $product)
                                                <tr>
                                                    <td>{{ $product['name'] }}</td>
                                                    <td>{{ $product['quantity'] }} {{ $product['quantity'] > 1 ? 'units' : 'unit' }}</td>
                                                    <td>Rs. {{ number_format($productPrices[$productKey] ?? 0, 2) }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h5 class="border-bottom pb-2">Product Details</h5>
                                            
                                            @if($customOrder->flagDetails && $customOrder->flagDetails->count() > 0)
                                                @foreach($customOrder->flagDetails as $flagDetail)
                                                    @if($flagDetail->flag_type !== 'flag')
                                                        <div class="card bg-light mb-3" id="product-{{ $flagDetail->id }}">
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <div class="col-md-8">
                                                                        <h5 class="card-title">
                                                                            <i class="fa fa-shopping-cart text-secondary"></i>
                                                                            @if($flagDetail->product)
                                                                                {{ $flagDetail->product->name }}
                                                                            @else
                                                                                {{ $flagDetail->notes ?? 'Product Item' }}
                                                                            @endif
                                                                            <span class="badge badge-primary float-right">Product #{{ $flagDetail->id }}</span>
                                                                        </h5>
                                                                        
                                                                        @if($flagDetail->product)
                                                                            <div class="product-details mb-3">
                                                                                <table class="table table-sm table-bordered">
                                                                                    <tbody>
                                                                                        @if($flagDetail->product->code)
                                                                                        <tr>
                                                                                            <th width="30%">Product Code</th>
                                                                                            <td>{{ $flagDetail->product->code }}</td>
                                                                                        </tr>
                                                                                        @endif
                                                                                        
                                                                                        @if($flagDetail->product->model)
                                                                                        <tr>
                                                                                            <th>Model</th>
                                                                                            <td>{{ $flagDetail->product->model }}</td>
                                                                                        </tr>
                                                                                        @endif
                                                                                        
                                                                                        @if($flagDetail->product->serial_number)
                                                                                        <tr>
                                                                                            <th>Serial Number</th>
                                                                                            <td>{{ $flagDetail->product->serial_number }}</td>
                                                                                        </tr>
                                                                                        @endif
                                                                                        
                                                                                        @if($flagDetail->product->category)
                                                                                        <tr>
                                                                                            <th>Category</th>
                                                                                            <td>{{ $flagDetail->product->category->name }}</td>
                                                                                        </tr>
                                                                                        @endif
                                                                                        
                                                                                        @if($flagDetail->product->unit)
                                                                                        <tr>
                                                                                            <th>Unit</th>
                                                                                            <td>{{ $flagDetail->product->unit->name }}</td>
                                                                                        </tr>
                                                                                        @endif
                                                                                        
                                                                                        @if($flagDetail->product->barcode)
                                                                                        <tr>
                                                                                            <th>Barcode</th>
                                                                                            <td>{{ $flagDetail->product->barcode }}</td>
                                                                                        </tr>
                                                                                        @endif
                                                                                        
                                                                                        @if($flagDetail->product->location)
                                                                                        <tr>
                                                                                            <th>Location</th>
                                                                                            <td>{{ $flagDetail->product->location }}</td>
                                                                                        </tr>
                                                                                        @endif
                                                                                    </tbody>
                                                                                </table>
                                                                            </div>
                                                                        @endif
                                                                        
                                                                        <div class="description mb-3">
                                                                            <h6 class="font-weight-bold">Description:</h6>
                                                                            <p>
                                                                                @if($flagDetail->product)
                                                                                    {{ $flagDetail->product->name }}
                                                                                @else
                                                                                    {{ $flagDetail->notes ?? 'Standard product' }}
                                                                                @endif
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="col-md-4">
                                                                        @if($flagDetail->product && $flagDetail->product->image)
                                                                            <img src="{{ asset('storage/' . $flagDetail->product->image) }}"
                                                                                alt="{{ $flagDetail->product->name }}"
                                                                                class="img-fluid rounded mb-3">
                                                                        @else
                                                                            <img src="{{ asset('images/product/default.jpg') }}"
                                                                                alt="Default Product Image"
                                                                                class="img-fluid rounded mb-3">
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="order-details mt-3 p-3 bg-light border rounded">
                                                                    <h6 class="font-weight-bold">Order Information:</h6>
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <p><strong>Quantity:</strong> {{ $flagDetail->quantity }} {{ $flagDetail->quantity > 1 ? 'units' : 'unit' }}</p>
                                                                            <p><strong>Unit Price:</strong> Rs. {{ number_format($flagDetail->price_per_square_feet, 2) }}</p>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <p><strong>Total Price:</strong>
                                                                                <span class="text-success font-weight-bold">
                                                                                    Rs. {{ number_format($flagDetail->total_price, 2) }}
                                                                                </span>
                                                                            </p>
                                                                            @if($flagDetail->product && $flagDetail->product->current_stock !== null)
                                                                                <p><strong>Current Stock:</strong> {{ $flagDetail->product->current_stock }} units</p>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Order Total Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fa fa-money"></i> Order Total</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <h3>Total Order Value: Rs. {{ number_format($customOrder->total_price, 2) }}</h3>
                                        <p class="text-muted">Thank you for your order!</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(isset($customOrder->invoiceItems) && $customOrder->invoiceItems->count() > 0)
                    <div class="mt-4">
                        <h6 class="border-bottom pb-2">Invoice Items</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Quantity</th>
                                        <th>Unit Price (Rs)</th>
                                        <th>Discount (%)</th>
                                        <th>Amount (Rs)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customOrder->invoiceItems as $item)
                                        <tr>
                                            <td>{{ $item->product->name }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ number_format($item->unit_price, 2) }}</td>
                                            <td>{{ number_format($item->discount, 2) }}</td>
                                            <td>{{ number_format($item->quantity * $item->unit_price * (1 - $item->discount/100), 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-right"><strong>Subtotal:</strong></td>
                                        <td>Rs. {{ number_format($customOrder->total_price, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-right"><strong>Tax (13%):</strong></td>
                                        <td>Rs. {{ number_format($customOrder->total_price * 0.13, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-right"><strong>Total:</strong></td>
                                        <td>Rs. {{ number_format($customOrder->total_price * 1.13, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    @endif

                    @if($customOrder->special_instructions)
                        <div class="mt-4">
                            <h6 class="border-bottom pb-2">Special Instructions</h6>
                            <p class="text-muted">{{ $customOrder->special_instructions }}</p>
                        </div>
                    @endif

                    @if($customOrder->design_file)
                        <div class="mt-4">
                            <h6 class="border-bottom pb-2">Design File</h6>
                            <div class="mt-2">
                                @php
                                    $extension = pathinfo($customOrder->design_file, PATHINFO_EXTENSION);
                                @endphp

                                @if(in_array($extension, ['jpg', 'jpeg', 'png']))
                                    <img src="{{ Storage::url($customOrder->design_file) }}"
                                         alt="Design Preview"
                                         class="img-fluid mb-2"
                                         style="max-height: 200px;">
                                @endif

                                <div>
                                    <a href="{{ Storage::url($customOrder->design_file) }}"
                                       class="btn btn-sm btn-info"
                                       target="_blank">
                                        View Full Design
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
    </main>
@endsection