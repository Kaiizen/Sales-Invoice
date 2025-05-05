@extends('layouts.master')
@section('title', 'Edit Custom Order')

@push('css')
<style>
    /* Highlight effect for pre-populated fields */
    .bg-light-yellow {
        background-color: #ffffcc !important;
        transition: background-color 0.5s ease;
    }
    
    /* Make the form fields more prominent */
    .form-control {
        border: 1px solid #ced4da;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    /* Style for the edit mode notification */
    .edit-mode-notification {
        background-color: #e3f2fd;
        border-left: 4px solid #2196F3;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }
    
    /* Make readonly fields visually distinct */
    input[readonly] {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }
    
    /* Add a subtle animation to highlight changes */
    @keyframes highlight {
        0% { background-color: #ffffff; }
        50% { background-color: #ffffcc; }
        100% { background-color: #ffffff; }
    }
    
    .highlight-animation {
        animation: highlight 1s ease;
    }
</style>
@endpush

@section('content')
    @include('partials.header')
    @include('partials.sidebar')
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-edit"></i> Edit Order #{{ $customOrder->id }}</h1>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item"><a href="{{ route('custom-orders.index') }}">Custom Orders</a></li>
                <li class="breadcrumb-item">Edit Order</li>
            </ul>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-title-w-btn">
                        <h3 class="title">Edit Order #{{ $customOrder->id }}</h3>
                        <div class="btn-group">
                            <a class="btn btn-secondary" href="{{ route('custom-orders.index') }}">
                                <i class="fa fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                    
                    <div class="tile-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <form action="{{ route('custom-orders.update', $customOrder) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="customer_id">Customer</label>
                                        <select name="customer_id" id="customer_id" class="form-control" required>
                                            <option value="">Select Customer</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}" {{ $customOrder->customer_id == $customer->id ? 'selected' : '' }}>
                                                    {{ $customer->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <div class="input-group">
                                            <select name="status" id="status" class="form-control" required>
                                                @foreach(App\CustomOrder::STATUSES as $status)
                                                    <option value="{{ $status }}" {{ $customOrder->status === $status ? 'selected' : '' }}>
                                                        {{ $status }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-primary" id="updateStatusBtn">
                                                    <i class="fa fa-refresh"></i> Update Status
                                                </button>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">
                                            <i class="fa fa-info-circle"></i> You can change the status without updating the entire order.
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="total_price">Total Price</label>
                                        <input type="number" name="total_price" id="total_price" class="form-control" value="{{ $customOrder->total_price }}" step="0.01" readonly>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="flag_type">Main Product Type</label>
                                        <select name="flag_type" id="flag_type" class="form-control">
                                            @foreach(App\CustomOrder::PRODUCT_TYPES as $type)
                                                <option value="{{ $type }}" {{ $customOrder->flag_type === $type ? 'selected' : '' }}>
                                                    {{ $type }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Order Items Section -->
                            <div class="card mt-4 mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Order Items</h5>
                                </div>
                                <div class="card-body">
                                    @if($customOrder->flagDetails->count() > 0)
                                        <h6>Edit Order Items</h6>
                                        
                                        <!-- Fabric/Flag Items -->
                                        @php
                                            $hasFabricItems = false;
                                            foreach($customOrder->flagDetails as $flagDetail) {
                                                if($flagDetail->flag_type === 'flag') {
                                                    $hasFabricItems = true;
                                                    break;
                                                }
                                            }
                                        @endphp
                                        
                                        @if($hasFabricItems)
                                            <div class="card mb-4">
                                                <div class="card-header bg-info text-white">
                                                    <h6 class="mb-0">Fabric/Flag Items</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>Item</th>
                                                                    <th>Height</th>
                                                                    <th>Breadth</th>
                                                                    <th>Square Feet</th>
                                                                    <th>Price/Sq.Ft</th>
                                                                    <th>Quantity</th>
                                                                    <th>Stitching</th>
                                                                    <th>Total</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($customOrder->flagDetails as $index => $flagDetail)
                                                                    @if($flagDetail->flag_type === 'flag')
                                                                        <tr>
                                                                            <td>
                                                                                <input type="hidden" name="flag_details[{{ $index }}][id]" value="{{ $flagDetail->id }}">
                                                                                <input type="hidden" name="flag_details[{{ $index }}][flag_type]" value="{{ $flagDetail->flag_type }}">
                                                                                @if($flagDetail->product)
                                                                                    {{ $flagDetail->product->name }}
                                                                                    <input type="hidden" name="flag_details[{{ $index }}][product_id]" value="{{ $flagDetail->product_id }}">
                                                                                @else
                                                                                    {{ $flagDetail->notes ?? 'Fabric Item' }}
                                                                                @endif
                                                                            </td>
                                                                            <td>
                                                                                <input type="number" name="flag_details[{{ $index }}][height]" class="form-control form-control-sm" value="{{ $flagDetail->height }}" step="0.01">
                                                                            </td>
                                                                            <td>
                                                                                <input type="number" name="flag_details[{{ $index }}][breadth]" class="form-control form-control-sm" value="{{ $flagDetail->breadth }}" step="0.01">
                                                                            </td>
                                                                            <td>
                                                                                <input type="number" name="flag_details[{{ $index }}][square_feet]" class="form-control form-control-sm" value="{{ $flagDetail->square_feet }}" step="0.01" readonly>
                                                                            </td>
                                                                            <td>
                                                                                <input type="number" name="flag_details[{{ $index }}][price_per_square_feet]" class="form-control form-control-sm" value="{{ $flagDetail->price_per_square_feet }}" step="0.01">
                                                                            </td>
                                                                            <td>
                                                                                <input type="number" name="flag_details[{{ $index }}][quantity]" class="form-control form-control-sm" value="{{ $flagDetail->quantity }}" min="1">
                                                                            </td>
                                                                            <td>
                                                                                <select name="flag_details[{{ $index }}][stitching]" class="form-control form-control-sm">
                                                                                    <option value="1" {{ $flagDetail->stitching ? 'selected' : '' }}>Yes</option>
                                                                                    <option value="0" {{ !$flagDetail->stitching ? 'selected' : '' }}>No</option>
                                                                                </select>
                                                                            </td>
                                                                            <td>
                                                                                <input type="number" name="flag_details[{{ $index }}][total_price]" class="form-control form-control-sm" value="{{ $flagDetail->total_price }}" step="0.01" readonly>
                                                                            </td>
                                                                        </tr>
                                                                    @endif
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    
                                                    <!-- Fabric Specifications Section -->
                                                    <div class="mt-4">
                                                        <h6 class="border-bottom pb-2">Fabric Specifications</h6>
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label>Fabric Type:</label>
                                                                    <select name="fabric_type" class="form-control">
                                                                        <option value="">Select Fabric Type</option>
                                                                        <option value="Satin Silk Thick" {{ $customOrder->fabric_type == 'Satin Silk Thick' ? 'selected' : '' }}>Satin Silk Thick</option>
                                                                        <option value="Knitted Polyester" {{ $customOrder->fabric_type == 'Knitted Polyester' ? 'selected' : '' }}>Knitted Polyester</option>
                                                                        <option value="Cotton" {{ $customOrder->fabric_type == 'Cotton' ? 'selected' : '' }}>Cotton</option>
                                                                        <option value="Polyester" {{ $customOrder->fabric_type == 'Polyester' ? 'selected' : '' }}>Polyester</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Fabric Composition:</label>
                                                                    <select name="fabric_composition" class="form-control">
                                                                        <option value="100% Polyester" {{ $customOrder->fabric_composition == '100% Polyester' ? 'selected' : '' }}>100% Polyester</option>
                                                                        <option value="100% Cotton" {{ $customOrder->fabric_composition == '100% Cotton' ? 'selected' : '' }}>100% Cotton</option>
                                                                        <option value="Polyester Blend" {{ $customOrder->fabric_composition == 'Polyester Blend' ? 'selected' : '' }}>Polyester Blend</option>
                                                                        <option value="Satin Silk" {{ $customOrder->fabric_composition == 'Satin Silk' ? 'selected' : '' }}>Satin Silk</option>
                                                                        <option value="Knitted Polyester" {{ $customOrder->fabric_composition == 'Knitted Polyester' ? 'selected' : '' }}>Knitted Polyester</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label>Fabric Weight:</label>
                                                                    <select name="fabric_weight" class="form-control">
                                                                        <option value="110 gsm" {{ $customOrder->fabric_weight == '110 gsm' ? 'selected' : '' }}>110 gsm (Lightweight)</option>
                                                                        <option value="130 gsm" {{ $customOrder->fabric_weight == '130 gsm' ? 'selected' : '' }}>130 gsm (Standard)</option>
                                                                        <option value="160 gsm" {{ $customOrder->fabric_weight == '160 gsm' ? 'selected' : '' }}>160 gsm (Medium)</option>
                                                                        <option value="200 gsm" {{ $customOrder->fabric_weight == '200 gsm' ? 'selected' : '' }}>200 gsm (Heavy)</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Fabric Origin:</label>
                                                                    <select name="fabric_origin" class="form-control">
                                                                        <option value="Imported" {{ $customOrder->fabric_origin == 'Imported' ? 'selected' : '' }}>Imported</option>
                                                                        <option value="Local" {{ $customOrder->fabric_origin == 'Local' ? 'selected' : '' }}>Local</option>
                                                                        <option value="China" {{ $customOrder->fabric_origin == 'China' ? 'selected' : '' }}>China</option>
                                                                        <option value="India" {{ $customOrder->fabric_origin == 'India' ? 'selected' : '' }}>India</option>
                                                                        <option value="Thailand" {{ $customOrder->fabric_origin == 'Thailand' ? 'selected' : '' }}>Thailand</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <!-- Product Items -->
                                        @php
                                            $hasProductItems = false;
                                            foreach($customOrder->flagDetails as $flagDetail) {
                                                if($flagDetail->flag_type !== 'flag') {
                                                    $hasProductItems = true;
                                                    break;
                                                }
                                            }
                                        @endphp
                                        
                                        @if($hasProductItems)
                                            <div class="card mb-4">
                                                <div class="card-header bg-secondary text-white">
                                                    <h6 class="mb-0">Product Items</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>Product</th>
                                                                    <th>Price/Unit</th>
                                                                    <th>Quantity</th>
                                                                    <th>Total</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($customOrder->flagDetails as $index => $flagDetail)
                                                                    @if($flagDetail->flag_type !== 'flag')
                                                                        <tr>
                                                                            <td>
                                                                                <input type="hidden" name="flag_details[{{ $index }}][id]" value="{{ $flagDetail->id }}">
                                                                                <input type="hidden" name="flag_details[{{ $index }}][flag_type]" value="{{ $flagDetail->flag_type }}">
                                                                                @if($flagDetail->product)
                                                                                    {{ $flagDetail->product->name }}
                                                                                    <input type="hidden" name="flag_details[{{ $index }}][product_id]" value="{{ $flagDetail->product_id }}">
                                                                                @else
                                                                                    {{ $flagDetail->notes ?? 'Product Item' }}
                                                                                @endif
                                                                            </td>
                                                                            <td>
                                                                                <input type="number" name="flag_details[{{ $index }}][price_per_square_feet]" class="form-control form-control-sm" value="{{ $flagDetail->price_per_square_feet }}" step="0.01">
                                                                            </td>
                                                                            <td>
                                                                                <input type="number" name="flag_details[{{ $index }}][quantity]" class="form-control form-control-sm" value="{{ $flagDetail->quantity }}" min="1">
                                                                            </td>
                                                                            <td>
                                                                                <input type="number" name="flag_details[{{ $index }}][total_price]" class="form-control form-control-sm" value="{{ $flagDetail->total_price }}" step="0.01" readonly>
                                                                            </td>
                                                                        </tr>
                                                                    @endif
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @else
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="quantity">Quantity</label>
                                                    <input type="number" name="quantity" id="quantity" class="form-control" value="{{ $customOrder->quantity }}" min="1" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="price_per_square_feet">Price per Square Feet</label>
                                                    <input type="number" name="price_per_square_feet" id="price_per_square_feet" class="form-control" value="{{ $customOrder->price_per_square_feet }}" step="0.01">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="height">Height</label>
                                                    <input type="number" name="height" id="height" class="form-control" value="{{ $customOrder->height }}" step="0.01">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="breadth">Breadth</label>
                                                    <input type="number" name="breadth" id="breadth" class="form-control" value="{{ $customOrder->breadth }}" step="0.01">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="square_feet">Square Feet</label>
                                                    <input type="number" name="square_feet" id="square_feet" class="form-control" value="{{ $customOrder->square_feet }}" step="0.01" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="stitching">Stitching</label>
                                                    <select name="stitching" id="stitching" class="form-control">
                                                        <option value="1" {{ $customOrder->stitching ? 'selected' : '' }}>Yes</option>
                                                        <option value="0" {{ !$customOrder->stitching ? 'selected' : '' }}>No</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="special_instructions">Special Instructions</label>
                                        <textarea name="special_instructions" id="special_instructions" class="form-control" rows="3">{{ $customOrder->special_instructions }}</textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12 text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-save"></i> Update Order
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
<!-- Include calculation scripts first -->
<script src="{{ asset('js/fabric-calculations.js') }}"></script>
<script src="{{ asset('js/invoice-calculations.js') }}"></script>

<!-- Include the edit custom order script -->
<script src="{{ asset('js/edit-custom-order.js') }}"></script>

<!-- Add immediate calculation trigger -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Triggering initial calculations');
        
        // Trigger calculations for all rows
        setTimeout(function() {
            // Trigger change events on all dimension fields
            document.querySelectorAll('input[name^="flag_details"][name$="[height]"], input[name^="flag_details"][name$="[breadth]"]').forEach(function(field) {
                var event = new Event('change', { bubbles: true });
                field.dispatchEvent(event);
            });
            
            // Trigger change events on all price and quantity fields
            document.querySelectorAll('input[name^="flag_details"][name$="[price_per_square_feet]"], input[name^="flag_details"][name$="[quantity]"]').forEach(function(field) {
                var event = new Event('change', { bubbles: true });
                field.dispatchEvent(event);
            });
            
            console.log('Initial calculations triggered');
        }, 500);
    });
</script>
<script>
    $(document).ready(function() {
        console.log('Edit form initialized');
        
        // Calculate square feet for fabric items
        function calculateSquareFeet(row) {
            const height = parseFloat($(row).find('input[name^="flag_details"][name$="[height]"]').val()) || 0;
            const breadth = parseFloat($(row).find('input[name^="flag_details"][name$="[breadth]"]').val()) || 0;
            const squareFeet = height * breadth;
            $(row).find('input[name^="flag_details"][name$="[square_feet]"]').val(squareFeet.toFixed(2));
            return squareFeet;
        }
        
        // Calculate total price for fabric items
        function calculateFabricTotalPrice(row) {
            const squareFeet = parseFloat($(row).find('input[name^="flag_details"][name$="[square_feet]"]').val()) || 0;
            const pricePerSqFt = parseFloat($(row).find('input[name^="flag_details"][name$="[price_per_square_feet]"]').val()) || 0;
            const quantity = parseInt($(row).find('input[name^="flag_details"][name$="[quantity]"]').val()) || 0;
            const totalPrice = squareFeet * pricePerSqFt * quantity;
            $(row).find('input[name^="flag_details"][name$="[total_price]"]').val(totalPrice.toFixed(2));
            return totalPrice;
        }
        
        // Calculate total price for product items
        function calculateProductTotalPrice(row) {
            const pricePerUnit = parseFloat($(row).find('input[name^="flag_details"][name$="[price_per_square_feet]"]').val()) || 0;
            const quantity = parseInt($(row).find('input[name^="flag_details"][name$="[quantity]"]').val()) || 0;
            const totalPrice = pricePerUnit * quantity;
            $(row).find('input[name^="flag_details"][name$="[total_price]"]').val(totalPrice.toFixed(2));
            return totalPrice;
        }
        
        // Calculate total order price
        function calculateOrderTotal() {
            let total = 0;
            
            // Add up fabric items
            $('table tbody tr').each(function() {
                const totalPriceInput = $(this).find('input[name^="flag_details"][name$="[total_price]"]');
                if (totalPriceInput.length) {
                    total += parseFloat(totalPriceInput.val()) || 0;
                }
            });
            
            // Update the total price field
            $('#total_price').val(total.toFixed(2));
        }
        
        // Event handlers for fabric items
        $('input[name^="flag_details"][name$="[height]"], input[name^="flag_details"][name$="[breadth]"]').on('change', function() {
            const row = $(this).closest('tr');
            calculateSquareFeet(row);
            calculateFabricTotalPrice(row);
            calculateOrderTotal();
        });
        
        $('input[name^="flag_details"][name$="[price_per_square_feet]"], input[name^="flag_details"][name$="[quantity]"]').on('change', function() {
            const row = $(this).closest('tr');
            const flagType = $(row).find('input[name^="flag_details"][name$="[flag_type]"]').val();
            
            if (flagType === 'flag') {
                calculateFabricTotalPrice(row);
            } else {
                calculateProductTotalPrice(row);
            }
            
            calculateOrderTotal();
        });
        
        // For simple orders without flag details
        $('#height, #breadth').on('change', function() {
            const height = parseFloat($('#height').val()) || 0;
            const breadth = parseFloat($('#breadth').val()) || 0;
            const squareFeet = height * breadth;
            $('#square_feet').val(squareFeet.toFixed(2));
            
            const pricePerSqFt = parseFloat($('#price_per_square_feet').val()) || 0;
            const quantity = parseInt($('#quantity').val()) || 0;
            const totalPrice = squareFeet * pricePerSqFt * quantity;
            $('#total_price').val(totalPrice.toFixed(2));
        });
        
        $('#price_per_square_feet, #quantity').on('change', function() {
            const squareFeet = parseFloat($('#square_feet').val()) || 0;
            const pricePerSqFt = parseFloat($('#price_per_square_feet').val()) || 0;
            const quantity = parseInt($('#quantity').val()) || 0;
            const totalPrice = squareFeet * pricePerSqFt * quantity;
            $('#total_price').val(totalPrice.toFixed(2));
        });
        
        // Initialize calculations
        $('table tbody tr').each(function() {
            const flagType = $(this).find('input[name^="flag_details"][name$="[flag_type]"]').val();
            
            if (flagType === 'flag') {
                calculateSquareFeet(this);
                calculateFabricTotalPrice(this);
            } else {
                calculateProductTotalPrice(this);
            }
        });
        
        calculateOrderTotal();
        
        // Display a notification that we're in edit mode
        const notification = document.createElement('div');
        notification.className = 'alert alert-info alert-dismissible fade show';
        notification.innerHTML = `
            <strong><i class="fa fa-pencil"></i> Edit Mode:</strong>
            You are editing order #{{ $customOrder->id }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        `;
        
        // Insert at the top of the form
        const form = document.querySelector('form');
        if (form) {
            form.insertBefore(notification, form.firstChild);
        }
    });
</script>
@endpush