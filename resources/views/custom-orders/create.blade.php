@extends('layouts.master')
@section('title', isset($isEditing) && $isEditing ? 'Edit Custom Order #' . $customOrder->id : 'Create Custom Order')

<script>
// This script has been modified to redirect to the orders index page after submission
// The success message will be shown on the index page
</script>

<script>
// Direct script to fix fabric price issue
window.addEventListener('DOMContentLoaded', function() {
    console.log('Direct script loaded');
    
    // Function to set fabric prices
    function fixFabricPrices() {
        console.log('Fixing fabric prices');
        var fabricSelects = document.querySelectorAll('select[name="fabric_id[]"]');
        console.log('Found ' + fabricSelects.length + ' fabric selects');
        
        fabricSelects.forEach(function(select) {
            var selectedOption = select.options[select.selectedIndex];
            if (selectedOption && selectedOption.value) {
                console.log('Selected fabric:', selectedOption.text);
                var price = selectedOption.getAttribute('data-price');
                console.log('Fabric price from attribute:', price);
                
                // Find the price field
                var row = select.closest('tr');
                var priceField = row.querySelector('input[name="fabric_price_per_square_feet[]"]');
                
                if (priceField) {
                    console.log('Setting fabric price to:', price);
                    priceField.value = price;
                    priceField.style.backgroundColor = '#ffffcc';
                    setTimeout(function() {
                        priceField.style.backgroundColor = '';
                    }, 2000);
                }
            }
        });
    }
    
    // Function to calculate square feet and total price
    function calculateFabricSquareFeetAndTotal() {
        console.log('Calculating fabric square feet and total price');
        var fabricRows = document.querySelectorAll('#fabric-table tbody tr');
        console.log('Found ' + fabricRows.length + ' fabric rows');
        
        fabricRows.forEach(function(row) {
            // Get the height, breadth, price, and quantity
            var heightInput = row.querySelector('input[name="fabric_height[]"]');
            var breadthInput = row.querySelector('input[name="fabric_breadth[]"]');
            var priceInput = row.querySelector('input[name="fabric_price_per_square_feet[]"]');
            var quantityInput = row.querySelector('input[name="fabric_quantity[]"]');
            var sqftDisplay = row.querySelector('.fabric-sqft-display');
            var totalPriceInput = row.querySelector('input[name="fabric_total_price[]"]');
            var totalPriceDisplay = row.querySelector('.fabric-total-display');
            
            if (heightInput && breadthInput && priceInput && quantityInput) {
                var height = parseFloat(heightInput.value) || 0;
                var breadth = parseFloat(breadthInput.value) || 0;
                var price = parseFloat(priceInput.value) || 0;
                var quantity = parseInt(quantityInput.value) || 1;
                
                // Use default values if height or breadth is 0
                if (height <= 0) height = 1;
                if (breadth <= 0) breadth = 1;
                
                // Update the input fields with default values if they were 0
                if (parseFloat(heightInput.value) <= 0) heightInput.value = height;
                if (parseFloat(breadthInput.value) <= 0) breadthInput.value = breadth;
                
                console.log('Height:', height, 'Breadth:', breadth, 'Price:', price, 'Quantity:', quantity);
                
                // Calculate square feet
                var squareFeet = (height * breadth).toFixed(2);
                console.log('Square feet:', squareFeet);
                
                // Calculate total price
                var totalPrice = (squareFeet * price * quantity).toFixed(2);
                console.log('Total price:', totalPrice);
                
                // Get the square feet input field
                var sqftInput = row.querySelector('input[name="fabric_square_feet[]"]');
                
                // Update the square feet input field with just the final value
                if (sqftInput) {
                    sqftInput.value = squareFeet + ' sqft';
                }
                
                // Update the square feet display (empty it since we're showing in input)
                if (sqftDisplay) {
                    sqftDisplay.innerHTML = '';
                }
                
                // Get the total price input field
                var totalPriceField = row.querySelector('input[name="fabric_total_price[]"]');
                
                // Update the total price input field with just the final value
                if (totalPriceField) {
                    totalPriceField.value = 'Rs. ' + totalPrice;
                }
                
                // Update the total price display (empty it since we're showing in input)
                if (totalPriceDisplay) {
                    totalPriceDisplay.innerHTML = '';
                }
            }
        });
        
        // Calculate the grand total
        var grandTotal = 0;
        document.querySelectorAll('input[name="fabric_total_price[]"]').forEach(function(input) {
            var value = input.value.replace('Rs. ', '');
            grandTotal += parseFloat(value) || 0;
        });
        
        // Update the grand total display
        var grandTotalDisplay = document.querySelector('.fabric-grand-total');
        if (grandTotalDisplay) {
            grandTotalDisplay.innerHTML = 'Rs. ' + grandTotal.toFixed(2);
        }
        
        // Update the combined total
        updateCombinedTotal();
    }
    
    // Function to update the combined total
    function updateCombinedTotal() {
        var productTotal = parseFloat(document.querySelector('.product-grand-total').innerHTML.replace('Rs. ', '')) || 0;
        var fabricTotal = parseFloat(document.querySelector('.fabric-grand-total').innerHTML.replace('Rs. ', '')) || 0;
        var combinedTotal = productTotal + fabricTotal;
        
        var combinedTotalDisplay = document.querySelector('.combined-grand-total');
        if (combinedTotalDisplay) {
            combinedTotalDisplay.innerHTML = 'Rs. ' + combinedTotal.toFixed(2);
        }
    }
    
    // Add event listeners for height, breadth, and quantity inputs
    document.addEventListener('input', function(e) {
        if (e.target.matches('input[name="fabric_height[]"]') ||
            e.target.matches('input[name="fabric_breadth[]"]') ||
            e.target.matches('input[name="fabric_quantity[]"]')) {
            console.log('Height, breadth, or quantity changed');
            calculateFabricSquareFeetAndTotal();
        }
    });
    
    // Function to fix all fabric prices
    function fixAllFabricPrices() {
        console.log('Fixing all fabric prices');
        var fabricSelects = document.querySelectorAll('select[name="fabric_id[]"]');
        console.log('Found ' + fabricSelects.length + ' fabric selects');
        
        fabricSelects.forEach(function(select) {
            var selectedOption = select.options[select.selectedIndex];
            if (selectedOption && selectedOption.value) {
                console.log('Selected fabric:', selectedOption.text);
                var price = selectedOption.getAttribute('data-price');
                console.log('Fabric price from attribute:', price);
                
                // Find the price field
                var row = select.closest('tr');
                var priceField = row.querySelector('input[name="fabric_price_per_square_feet[]"]');
                
                if (priceField) {
                    console.log('Setting fabric price to:', price);
                    priceField.value = price;
                    priceField.style.backgroundColor = '#ffffcc';
                    setTimeout(function() {
                        priceField.style.backgroundColor = '';
                    }, 2000);
                }
            }
        });
        
        // Calculate square feet and total price
        calculateFabricSquareFeetAndTotal();
        
        alert('Fabric prices have been updated and calculations completed!');
    }
    
    // Run immediately and also after a short delay to ensure DOM is fully loaded
    fixFabricPrices();
    setTimeout(fixFabricPrices, 1000);
    
    // Also calculate square feet and total price
    calculateFabricSquareFeetAndTotal();
    setTimeout(calculateFabricSquareFeetAndTotal, 1500);
    
    // Also run when any fabric select changes
    document.addEventListener('change', function(e) {
        if (e.target.matches('select[name="fabric_id[]"]')) {
            console.log('Fabric select changed');
            var selectedOption = e.target.options[e.target.selectedIndex];
            if (selectedOption && selectedOption.value) {
                var price = selectedOption.getAttribute('data-price');
                var row = e.target.closest('tr');
                var priceField = row.querySelector('input[name="fabric_price_per_square_feet[]"]');
                
                if (priceField) {
                    console.log('Setting fabric price to:', price);
                    priceField.value = price;
                    priceField.style.backgroundColor = '#ffffcc';
                    setTimeout(function() {
                        priceField.style.backgroundColor = '';
                    }, 2000);
                }
            }
        }
    });
});
</script>

@section('content')
    @include('partials.header')
    @include('partials.sidebar')
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-shopping-cart"></i> {{ isset($isEditing) && $isEditing ? 'Edit Custom Order #' . $customOrder->id : 'Create Custom Order' }}</h1>
                <p>{{ isset($isEditing) && $isEditing ? 'Edit existing custom order for products or fabric' : 'Create a new custom order for products or fabric' }}</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item"><a href="{{ route('custom-orders.index') }}">Custom Orders</a></li>
                <li class="breadcrumb-item">{{ isset($isEditing) && $isEditing ? 'Edit Order' : 'Create Order' }}</li>
            </ul>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fa fa-tag"></i> {{ isset($isEditing) && $isEditing ? 'Edit Order #' . $customOrder->id : 'Unified Custom Order Form' }}</h4>
                    </div>
                    <div class="card-body py-4">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i>
                            @if(isset($isEditing) && $isEditing)
                                You are editing an existing order. Make your changes and click "Update Order" to save.
                                <div class="mt-2">
                                    <strong>Debug Info:</strong> Order ID: {{ $customOrder->id }},
                                    Flag Details Count: {{ $customOrder->flagDetails->count() }}
                                </div>
                            @else
                                This form allows you to create a custom order that can include both products and flags. Add as many items as needed using the "Add" button in the product details section.
                            @endif
                        </div>
                        
                        @if (session('success'))
                            <div class="alert alert-success" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-0">
                                    <div class="card-body">
                                        <h5><i class="fa fa-shopping-cart text-success"></i> Products</h5>
                                        <p>General products and services without fabric dimensions</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-0">
                                    <div class="card-body">
                                        <h5><i class="fa fa-flag text-info"></i> Flags/Fabric</h5>
                                        <p>Fabric and flag orders with width × height dimensions</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-body">
                        <form method="POST" action="{{ isset($isEditing) && $isEditing ? route('custom-orders.update', $customOrder->id) : route('custom-orders.store') }}" enctype="multipart/form-data" id="custom-order-form">
                            @csrf
                            @if(isset($isEditing) && $isEditing)
                                @method('PUT')
                            @endif
                            
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h4 class="mb-0">
                                        <i class="fa fa-file-text"></i>
                                        <span class="order-type-title">Job Order Form</span>
                                        <span class="float-right">
                                            <span class="badge badge-light order-type-badge">
                                                <i class="fa fa-shopping-cart"></i> <i class="fa fa-flag"></i> Combined Order
                                            </span>
                                        </span>
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="d-flex align-items-center mb-3">
                                                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="mr-3" style="max-height: 80px; max-width: 80px;">
                                                <h3 class="mb-0">Job Order Form</h3>
                                            </div>
                                            <h5 class="border-bottom pb-2 mb-3">CUSTOMER INFORMATION</h5>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Order No.:</label>
                                                <div class="border p-2 text-center">
                                                    <h4 class="text-danger mb-0">{{ 'CO' . date('ymd') . rand(1000, 9999) }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Name:</label>
                                                <select name="customer_id" id="customer_id" class="form-control" required>
                                                    <option value="">Select Customer</option>
                                                    @foreach($customers as $customer)
                                                        <option value="{{ $customer->id }}" {{ (isset($isEditing) && $isEditing && $customOrder->customer_id == $customer->id) ? 'selected' : '' }}>
                                                            {{ $customer->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Address:</label>
                                                <input type="text" class="form-control" id="customer_address" readonly>
                                            </div>
                                            <div class="form-group">
                                                <label>Contact:</label>
                                                <input type="text" class="form-control" id="customer_mobile" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Order Date:</label>
                                                <input type="date" name="order_date" class="form-control" value="{{ isset($isEditing) && $isEditing && $customOrder->created_at ? $customOrder->created_at->format('Y-m-d') : date('Y-m-d') }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Delivery Date:</label>
                                                <input type="date" name="delivery_date" class="form-control" value="{{ isset($isEditing) && $isEditing && $customOrder->delivery_date ? $customOrder->delivery_date : '' }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Email:</label>
                                                <input type="email" class="form-control" id="customer_email" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="border-bottom pb-2 mb-3">JOB DETAILS:</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Contact Through:</label>
                                                <select name="contact_through" class="form-control">
                                                    <option value="">Select Contact Method</option>
                                                    <option value="whatsapp" {{ isset($isEditing) && $isEditing && $customOrder->contact_through == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                                                    <option value="instagram" {{ isset($isEditing) && $isEditing && $customOrder->contact_through == 'instagram' ? 'selected' : '' }}>Instagram</option>
                                                    <option value="facebook" {{ isset($isEditing) && $isEditing && $customOrder->contact_through == 'facebook' ? 'selected' : '' }}>Facebook</option>
                                                    <option value="linkedin" {{ isset($isEditing) && $isEditing && $customOrder->contact_through == 'linkedin' ? 'selected' : '' }}>LinkedIn</option>
                                                    <option value="website" {{ isset($isEditing) && $isEditing && $customOrder->contact_through == 'website' ? 'selected' : '' }}>Website</option>
                                                    <option value="friends" {{ isset($isEditing) && $isEditing && $customOrder->contact_through == 'friends' ? 'selected' : '' }}>Friends</option>
                                                    <option value="referral" {{ isset($isEditing) && $isEditing && $customOrder->contact_through == 'referral' ? 'selected' : '' }}>Referral</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Design Provided:</label>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="design_provided" id="design_yes" value="Yes"
                                                        {{ isset($isEditing) && $isEditing && $customOrder->design_file ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="design_yes">Yes</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="design_provided" id="design_no" value="No"
                                                        {{ isset($isEditing) && $isEditing && !$customOrder->design_file ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="design_no">No</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Received by:</label>
                                                <input type="text" name="received_by" class="form-control" value="{{ auth()->user()->name }}" readonly>
                                            </div>
                                            <div class="form-group">
                                                <label>Payment Method:</label>
                                                <select name="payment_method" class="form-control">
                                                    <option value="Online" {{ isset($isEditing) && $isEditing && $customOrder->payment_method == 'Online' ? 'selected' : '' }}>Online</option>
                                                    <option value="Cash" {{ isset($isEditing) && $isEditing && $customOrder->payment_method == 'Cash' ? 'selected' : '' }}>Cash</option>
                                                    <option value="Cheque" {{ isset($isEditing) && $isEditing && $customOrder->payment_method == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Order Details Section -->
                                    <h5 class="border-bottom pb-2 mt-4 mb-3">ORDER DETAILS:</h5>
                                    <div class="alert alert-info mb-3">
                                        <i class="fa fa-info-circle"></i> You can add multiple products and fabric items to this order using the respective tables below.
                                    </div>

                                    <!-- Product Table Section -->
                                    <h6 class="mt-4 mb-2"><i class="fa fa-shopping-cart text-success"></i> PRODUCT ITEMS:</h6>
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.25rem; box-shadow: 0 2px 5px rgba(0,0,0,0.05); width: 100%; max-width: 1200px; margin: 0 auto;">
                                        <table id="product-table" class="table table-bordered table-hover" style="width: 100%;">
                                            <thead class="bg-success text-white">
                                                <tr>
                                                    <th width="40%">Product</th>
                                                    <th width="15%">Price/Unit</th>
                                                    <th width="10%">Quantity</th>
                                                    <th width="35%">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="job_type[]" value="product">
                                                        <select name="product_id[]" class="form-control product-select" onchange="updateProductPrice(this)">
                                                            <option value="">Select Product</option>
                                                            @foreach($products as $product)
                                                                <option value="{{ $product->id }}" data-type="product" data-price="{{ $product->sales_price }}">
                                                                    {{ $product->name }} (Rs. {{ $product->sales_price }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td><input type="number" step="0.01" name="product_price[]" class="form-control product-price-input" readonly></td>
                                                    <td><input type="number" name="product_quantity[]" class="form-control product-quantity" min="1" required value="1" oninput="calculateProductRow($(this).closest('tr'))"></td>
                                                    <td>
                                                        <div class="d-flex">
                                                            <input type="text" name="product_total_price[]" class="form-control bg-light font-weight-bold" style="width: 100%; min-width: 250px; background-color: #f8fff8 !important; color: #28a745; font-weight: bold; font-size: 16px; border: 1px solid #28a745;" readonly>
                                                            <a class="btn btn-danger remove-product ml-2"><i class="fa fa-remove"></i></a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr class="add-row-container">
                                                    <td colspan="4" class="p-0">
                                                        <button type="button" id="addProductRowBtn" class="btn btn-success addProductRow w-100 py-3" style="border-radius: 0; margin: 0; width: 100%; display: block; border: none;" onclick="addProductRow(); return false;">
                                                            <i class="fa fa-plus"></i> Add Row
                                                        </button>
                                                        <script>
                                                            function addProductRow() {
                                                                // Get the first row
                                                                var firstRow = document.querySelector('#product-table tbody tr:first-child');
                                                                if (firstRow) {
                                                                    // Clone it
                                                                    var newRow = firstRow.cloneNode(true);
                                                                    
                                                                    // Clear inputs
                                                                    newRow.querySelectorAll('input').forEach(function(input) {
                                                                        // Don't clear the job_type hidden input
                                                                        if (input.name !== 'job_type[]') {
                                                                            input.value = '';
                                                                        }
                                                                    });
                                                                    
                                                                    newRow.querySelectorAll('select').forEach(function(select) {
                                                                        select.selectedIndex = 0;
                                                                    });
                                                                    
                                                                    // Set quantity to 1
                                                                    var quantityInput = newRow.querySelector('input[name="product_quantity[]"]');
                                                                    if (quantityInput) {
                                                                        quantityInput.value = '1';
                                                                    }
                                                                    
                                                                    // Insert before the button row
                                                                    var buttonRow = document.querySelector('#product-table .add-row-container');
                                                                    buttonRow.parentNode.insertBefore(newRow, buttonRow);
                                                                    
                                                                    alert('New product row added successfully!');
                                                                }
                                                            }
                                                        </script>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            </table>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-8 text-right pt-2">
                                                <h5 class="font-weight-bold">Sub Total</h5>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="p-2 bg-success text-white rounded text-center">
                                                    <span class="product-grand-total" style="font-size: 18px;">Rs. 0.00</span>
                                                </div>
                                            </div>
                                        </div>
                                        </table>
                                    </div>

                                    <!-- Fabric/Flag Table Section -->
                                    <h6 class="mt-4 mb-2"><i class="fa fa-flag text-info"></i> FABRIC/FLAG ITEMS:</h6>
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.25rem; box-shadow: 0 2px 5px rgba(0,0,0,0.05); width: 100%; max-width: 1200px; margin: 0 auto;">
                                        <table id="fabric-table" class="table table-bordered table-hover" style="width: 100%;">
                                            <thead class="bg-info text-white">
                                                <tr>
                                                    <th width="20%">Fabric Type</th>
                                                    <th width="10%">Height</th>
                                                    <th width="10%">Breadth</th>
                                                    <th width="10%">SqFt</th>
                                                    <th width="10%">Price/SqFt</th>
                                                    <th width="12%">Qty</th>
                                                    <th width="25%">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="job_type[]" value="flag">
                                                        <select name="fabric_id[]" class="form-control custom-fabric-select"
                                                            onchange="
                                                                updateFabricPrice(this);
                                                                var price = this.options[this.selectedIndex].getAttribute('data-price');
                                                                var priceField = this.closest('tr').querySelector('input[name=\'fabric_price_per_square_feet[]\']');
                                                                if(price && priceField) {
                                                                    priceField.value = price;
                                                                    priceField.style.backgroundColor = '#ffffcc';
                                                                    setTimeout(function() { priceField.style.backgroundColor = ''; }, 2000);
                                                                    
                                                                    // Calculate square feet and total price
                                                                    calculateFabricSquareFeetAndTotal();
                                                                }
                                                                console.log('Direct inline handler: Setting price to ' + price);
                                                            ">
                                                            <option value="">Select Fabric</option>
                                                            @foreach($fabricProducts as $product)
                                                                <option value="{{ $product->id }}" data-square-feet="{{ $product->total_square_feet }}" data-type="fabric" data-price="{{ $product->sales_price }}">
                                                                    {{ $product->name }} ({{ $product->total_square_feet }} sqft) (Rs. {{ $product->sales_price }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td><input type="number" step="0.01" name="fabric_height[]" class="form-control fabric-dimension-input" placeholder="Feet" oninput="calculateFabricSquareFeetAndTotal()"></td>
                                                    <td><input type="number" step="0.01" name="fabric_breadth[]" class="form-control fabric-dimension-input" placeholder="Feet" oninput="calculateFabricSquareFeetAndTotal()"></td>
                                                    <td>
                                                        <input type="text" name="fabric_square_feet[]" class="form-control bg-light" style="width: 100%; min-width: 200px;" readonly>
                                                        <div class="fabric-sqft-display"></div>
                                                    </td>
                                                    <td><input type="number" step="0.01" name="fabric_price_per_square_feet[]" class="form-control fabric-price-input" readonly></td>
                                                    <td><input type="number" name="fabric_quantity[]" class="form-control" min="1" required value="1" oninput="calculateFabricSquareFeetAndTotal()"></td>
                                                    <td>
                                                        <div class="d-flex">
                                                            <input type="text" name="fabric_total_price[]" class="form-control bg-light font-weight-bold" style="width: 100%; min-width: 250px;" readonly>
                                                            <a class="btn btn-danger remove-fabric ml-2"><i class="fa fa-remove"></i></a>
                                                        </div>
                                                        <div class="fabric-total-display"></div>
                                                    </td>
                                                </tr>
                                                <tr class="add-row-container">
                                                    <td colspan="7" class="p-0">
                                                        <button type="button" id="addFabricRowBtn" class="btn btn-info addFabricRow w-100 py-3" style="border-radius: 0; margin: 0; width: 100%; display: block; border: none;" onclick="addFabricRow(); return false;">
                                                            <i class="fa fa-plus"></i> Add Row
                                                        </button>
                                                        <script>
                                                            function addFabricRow() {
                                                                // Get the first row
                                                                var firstRow = document.querySelector('#fabric-table tbody tr:first-child');
                                                                if (firstRow) {
                                                                    // Clone it
                                                                    var newRow = firstRow.cloneNode(true);
                                                                    
                                                                    // Clear inputs
                                                                    newRow.querySelectorAll('input').forEach(function(input) {
                                                                        // Don't clear the job_type hidden input
                                                                        if (input.name !== 'job_type[]') {
                                                                            input.value = '';
                                                                        }
                                                                    });
                                                                    
                                                                    newRow.querySelectorAll('select').forEach(function(select) {
                                                                        select.selectedIndex = 0;
                                                                    });
                                                                    
                                                                    // Set quantity to 1
                                                                    var quantityInput = newRow.querySelector('input[name="fabric_quantity[]"]');
                                                                    if (quantityInput) {
                                                                        quantityInput.value = '1';
                                                                    }
                                                                    
                                                                    // Insert before the button row
                                                                    var buttonRow = document.querySelector('#fabric-table .add-row-container');
                                                                    buttonRow.parentNode.insertBefore(newRow, buttonRow);
                                                                    
                                                                    alert('New fabric row added successfully!');
                                                                }
                                                            }
                                                        </script>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            </table>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-8 text-right pt-2">
                                                <h5 class="font-weight-bold">Sub Total</h5>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="p-2 bg-info text-white rounded text-center">
                                                    <span class="fabric-grand-total" style="font-size: 18px;">Rs. 0.00</span>
                                                </div>
                                            </div>
                                        </div>
                                        </table>
                                    </div>

                                    <!-- Combined Total -->
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <div class="card bg-dark text-white">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <h5 class="mb-0">COMBINED ORDER TOTAL:</h5>
                                                        <h4 class="mb-0 combined-grand-total">Rs. 0.00</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <div class="card-body">
                                    <div class="form-group mt-4">
                                        <label>Special Instructions:</label>
                                        <textarea name="special_instructions" class="form-control" rows="3"></textarea>
                                    </div>

                                    <div class="mt-3">
                                        <p class="small">
                                            I agree to pay the total amount when payment is due for service requested and product/materials used.<br>
                                            I acknowledge that all changes have been made as per the order specifications and agree not to dispute the product after receiving it.
                                        </p>
                                    </div>

                                    <!-- Client agreement section -->
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <div class="alert alert-info">
                                                <p class="mb-0">
                                                    <i class="fa fa-info-circle"></i> By submitting this form, you agree to the terms and conditions for service requested and product/materials used.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <div class="row">
                                    <!-- Hidden fields to map product and fabric values to what the controller expects -->
                                    <div id="hidden-fields-container"></div>
                                    
                                    <div class="col-md-6">
                                        <button type="submit" id="createOrderBtn" class="btn btn-primary btn-lg btn-block">
                                            <i class="fa fa-save"></i> {{ isset($isEditing) && $isEditing ? 'Update Order' : 'Create Order' }}
                                        </button>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="{{ route('custom-orders.index') }}" class="btn btn-secondary btn-lg btn-block">
                                            <i class="fa fa-times"></i> Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <script>
                                // Add event listener for the Create Order button
                                document.addEventListener('DOMContentLoaded', function() {
                                    var createOrderBtn = document.getElementById('createOrderBtn');
                                    if (createOrderBtn) {
                                        createOrderBtn.addEventListener('click', function(event) {
                                            // Form validation is handled by prepareFormData()
                                            // The success message will be shown after successful form submission
                                        });
                                    }
                                });
                            </script>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
<style>
    .bg-light-yellow {
        background-color: #ffffcc !important;
        transition: background-color 0.3s ease;
    }
    
    .bg-light-blue {
        background-color: #e6f2ff !important;
        transition: background-color 0.3s ease;
    }
    
    /* Make readonly price fields visually distinct */
    input[readonly].product-price-input,
    input[readonly].fabric-price-input {
        background-color: #f8fff8 !important;
        border: 1px solid #28a745;
        color: #28a745;
        font-weight: bold;
        cursor: not-allowed;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
    }
    
    /* Order type badge styling */
    .order-type-badge {
        font-size: 14px;
        padding: 8px 12px;
        border-radius: 20px;
    }
    
    /* Order type card styling */
    .order-type-card {
        border-width: 2px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        cursor: pointer;
    }
    
    .order-type-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    }
    
    .order-type-card.product-card {
        border-color: #28a745;
    }
    
    .order-type-card.fabric-card {
        border-color: #17a2b8;
    }
    
    .order-type-card.active {
        box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        transform: translateY(-5px);
    }
    
    .order-type-card .icon-container {
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .order-type-card.product-card.active {
        background-color: #f8fff8;
    }
    
    .order-type-card.fabric-card.active {
        background-color: #f0f8ff;
    }
    
    /* Make the square feet and total price fields more prominent */
    input[name="square_feet[]"], input[name="total_price[]"] {
        font-weight: bold;
        color: #28a745;
        font-size: 16px;
    }
    
    /* Improve table layout */
    .table td {
        vertical-align: middle;
        padding: 1rem 0.5rem;
        height: 80px;
    }
    
    .table th {
        padding: 1rem 0.5rem;
        background-color: #f8f9fa;
        font-size: 1.1rem;
        font-weight: bold;
    }
    
    .table input, .table select {
        padding: 0.5rem 0.75rem;
        font-size: 1rem;
        height: 45px;
    }
    
    /* Add zebra striping for better readability */
    .table tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    /* Add hover effect */
    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    /* Add word-wrap for product names */
    select.fabric-type-select {
        word-wrap: break-word;
        white-space: normal;
        height: auto;
    }
    
    /* Make square feet more visible */
    .sqft-display {
        display: block;
        font-size: 22px;
        font-weight: bold;
        color: #dc3545;
        margin-top: 10px;
        text-align: center;
        padding: 8px;
        background-color: #f8f9fa;
        border-radius: 5px;
        border: 3px solid #28a745;
        box-shadow: 0 3px 6px rgba(0,0,0,0.15);
        min-height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Improve layout for long product names */
    .fabric-type-select option {
        white-space: normal;
        padding: 5px;
    }
    
    /* Calculation display styling */
    .calculation-display {
        padding: 8px;
        line-height: 1.8;
        width: 100%;
    }
    
    .calc-number {
        font-size: 24px;
        color: #007bff;
        font-weight: bold;
    }
    
    .calc-result {
        font-size: 28px;
        color: #dc3545;
        font-weight: bold;
        text-decoration: underline;
        text-shadow: 0 1px 1px rgba(0,0,0,0.1);
    }
    
    /* Hide the original square feet input */
    input[name="square_feet[]"], input[name="total_price[]"] {
        height: 0;
        padding: 0;
        border: none;
        margin: 0;
        opacity: 0;
    }
    
    /* Price display styling */
    .price-display {
        padding: 8px;
        background-color: #f8f9fa;
        border-radius: 5px;
        border: 3px solid #28a745;
        text-align: center;
        font-size: 22px;
        font-weight: bold;
        margin-top: 10px;
        box-shadow: 0 3px 6px rgba(0,0,0,0.15);
        min-height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .price-amount {
        color: #28a745;
        font-size: 28px;
        font-weight: bold;
        text-shadow: 0 1px 1px rgba(0,0,0,0.1);
    }
    
    /* Pulse animation for grand total */
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    .pulse-animation {
        animation: pulse 0.5s ease-in-out;
    }
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // File input handler
    $('.custom-file-input').on('change', function(e) {
        var fileName = e.target.files[0].name;
        $(this).next('.custom-file-label').html(fileName);
    });
    
    // Initialize calculations for any pre-selected products
    $('#product-table tbody tr').each(function() {
        const productSelect = $(this).find('select[name="product_id[]"]');
        if (productSelect.val()) {
            updateProductPrice(productSelect[0]);
        } else {
            // Even if no product is selected, calculate the row to ensure subtotal is updated
            calculateProductRow($(this));
        }
    });
    
    // Explicitly calculate the product grand total on page load
    calculateProductGrandTotal();
    
    // Initialize calculations for any pre-selected fabrics
    $('#fabric-table tbody tr').each(function() {
        const fabricSelect = $(this).find('select[name="fabric_id[]"]');
        // Ensure it uses the custom-fabric-select class
        fabricSelect.removeClass('fabric-select').addClass('custom-fabric-select');
        if (fabricSelect.val()) {
            console.log('Pre-selected fabric found, updating price');
            updateFabricPrice(fabricSelect[0]);
        }
    });
    
    // Calculate product row total when needed
    
    // Calculate product row total
    function calculateProductRow(row) {
        console.log('Calculating product row - UPDATED FUNCTION');
        
        // Get the quantity and price values
        const quantity = parseInt(row.find('input[name="product_quantity[]"]').val()) || 1;
        const priceField = row.find('input[name="product_price[]"]');
        const totalPriceField = row.find('input[name="product_total_price[]"]');
        
        console.log('Price field found:', priceField.length > 0, 'Value:', priceField.val());
        console.log('Total price field found:', totalPriceField.length > 0);
        console.log('Quantity:', quantity);
        
        // Skip calculation if price is still loading
        if (priceField.val() === 'Loading...') {
            console.log('Price is still loading, skipping calculation');
            return;
        }
        
        // For product type, use a simple price * quantity calculation
        const productPrice = parseFloat(priceField.val()) || 0;
        const totalPrice = (productPrice * quantity).toFixed(2);
        
        console.log('Calculating product row:', productPrice, 'x', quantity, '=', totalPrice);
        
        // Update the total price field with the final value
        totalPriceField.val('Rs. ' + totalPrice);
        
        // Make sure the total is visible by adding styling
        totalPriceField.css({
            'background-color': '#f8fff8',
            'color': '#28a745',
            'font-weight': 'bold',
            'font-size': '16px',
            'border': '1px solid #28a745'
        });
        
        // Add highlight effect
        totalPriceField.addClass('bg-light-yellow');
        setTimeout(() => {
            totalPriceField.removeClass('bg-light-yellow');
            // Keep the styling after highlight effect
            totalPriceField.css({
                'background-color': '#f8fff8',
                'color': '#28a745',
                'font-weight': 'bold',
                'font-size': '16px',
                'border': '1px solid #28a745'
            });
        }, 500);
        
        // Always calculate the grand total after updating a row
        calculateProductGrandTotal();
    }
    
    // Calculate the product grand total
    function calculateProductGrandTotal() {
        console.log('Calculating product grand total');
        let grandTotal = 0;
        
        // Sum up all product total prices
        $('input[name="product_total_price[]"]').each(function() {
            const val = $(this).val().replace('Rs. ', '');
            const amount = parseFloat(val) || 0;
            grandTotal += amount;
            console.log('Adding to product total:', val, '=', amount, 'Running total:', grandTotal);
        });
        
        console.log('Final product grand total:', grandTotal);
        
        // Update the product grand total display with animation
        $('.product-grand-total').html('Rs. ' + grandTotal.toFixed(2));
        
        // Add highlight effect to make the change visible
        $('.product-grand-total').parent().addClass('bg-light-yellow');
        setTimeout(() => {
            $('.product-grand-total').parent().removeClass('bg-light-yellow');
        }, 500);
        
        calculateCombinedTotal();
    }
    
    // Calculate combined total
    function calculateCombinedTotal() {
        const productTotal = parseFloat($('.product-grand-total').text().replace('Rs. ', '')) || 0;
        const fabricTotal = parseFloat($('.fabric-grand-total').text().replace('Rs. ', '')) || 0;
        const combinedTotal = productTotal + fabricTotal;
        
        // Update combined total
        $('.combined-grand-total').text('Rs. ' + combinedTotal.toFixed(2));
    }
    
    // Product quantity change handler
    $('#product-table').on('input', 'input[name="product_quantity[]"], input[name="product_price[]"]', function() {
        console.log('Product quantity or price changed');
        calculateProductRow($(this).closest('tr'));
    });
    
    // Fabric selection handler
    $('#fabric-table').on('change', 'select[name="fabric_id[]"]', function() {
        console.log('Fabric selected');
        const row = $(this).closest('tr');
        const selectedOption = $(this).find('option:selected');
        const fabricId = $(this).val();
        const priceField = row.find('input[name="fabric_price_per_square_feet[]"]');
        
        if (!fabricId) {
            console.log('No fabric selected, clearing price');
            priceField.val('');
            calculateFabricRow(row);
            return;
        }
        
        // Get price from data attribute
        console.log('Selected fabric option data attributes:', selectedOption.data());
        console.log('Selected fabric option HTML:', selectedOption.prop('outerHTML'));
        
        // Try different ways to access the data-price attribute
        const attrPrice = selectedOption.attr('data-price');
        const dataPrice = parseFloat(attrPrice) || 0;
        
        console.log('Fabric data price attribute value:', attrPrice);
        console.log('Parsed fabric data price:', dataPrice);
        
        // Show loading indicator
        priceField.val('Loading...');
        
        // Directly set the price from the data attribute if available
        if (dataPrice > 0) {
            // Add highlight effect to price field
            priceField.addClass('bg-light-yellow');
            priceField.val(dataPrice);
            setTimeout(() => {
                priceField.removeClass('bg-light-yellow');
            }, 1000);
            calculateFabricRow(row);
            return;
        }
        
        // If we get here, we need to fetch the price from the server
        console.log('Fetching price from server for fabric ID:', fabricId);
        
        // Simple direct AJAX request to get fabric details
        $.ajax({
            url: '/api/products/' + fabricId,
            type: 'GET',
            success: function(response) {
                console.log('Fabric API response:', response);
                
                let price = 0;
                
                // Try to get the price from the response
                if (response.success && response.data && response.data.sales_price) {
                    price = response.data.sales_price;
                    console.log('Found price in API response:', price);
                }
                
                // Set the price in the field
                priceField.addClass('bg-light-yellow');
                priceField.val(price);
                setTimeout(() => {
                    priceField.removeClass('bg-light-yellow');
                }, 1000);
                
                // Calculate the row total
                calculateFabricRow(row);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching fabric details:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                
                // Set price to 0 on error
                priceField.val(0);
                calculateFabricRow(row);
            }
        });
    });
    
    // Calculate fabric row total
    function calculateFabricRow(row) {
        const quantity = parseInt(row.find('input[name="fabric_quantity[]"]').val()) || 1;
        const priceField = row.find('input[name="fabric_price_per_square_feet[]"]');
        const totalPriceField = row.find('input[name="fabric_total_price[]"]');
        const totalPriceDisplay = row.find('.fabric-total-display');
        const heightField = row.find('input[name="fabric_height[]"]');
        const breadthField = row.find('input[name="fabric_breadth[]"]');
        const sqFtField = row.find('input[name="fabric_square_feet[]"]');
        const sqFtDisplay = row.find('.fabric-sqft-display');
        
        // Skip calculation if price is still loading
        if (priceField.val() === 'Loading...') {
            return;
        }
        
        // For fabric/flag type, use the square feet calculation
        const height = parseFloat(heightField.val()) || 0;
        const breadth = parseFloat(breadthField.val()) || 0;
        const pricePerSqFt = parseFloat(priceField.val()) || 0;

        // Calculate square feet (height and breadth are in feet)
        const squareFeet = (height * breadth).toFixed(2);
        const totalSquareFeet = (squareFeet * quantity).toFixed(2);
        const totalPrice = (squareFeet * pricePerSqFt * quantity).toFixed(2);
        
        console.log('Calculating fabric row:', height, 'x', breadth, '=', squareFeet, 'sqft x', pricePerSqFt, 'x', quantity, '=', totalPrice);
        
        // Update values with just the final values
        sqFtField.val(squareFeet + ' sqft');
        
        // Empty the square feet display since we're showing in input
        sqFtDisplay.empty();
        
        // Update total price field with just the final value
        totalPriceField.val('Rs. ' + totalPrice);
        
        // Empty the total price display since we're showing in input
        totalPriceDisplay.empty();
        
        calculateFabricGrandTotal();
    }
    
    // Calculate the fabric grand total
    function calculateFabricGrandTotal() {
        let grandTotal = 0;
        $('input[name="fabric_total_price[]"]').each(function() {
            const val = $(this).val().replace('Rs. ', '');
            grandTotal += parseFloat(val) || 0;
        });
        
        // Update grand total
        $('.fabric-grand-total').html('Rs. ' + grandTotal.toFixed(2));
        calculateCombinedTotal();
    }
    
    // Fabric dimension/price/quantity change handler
    $('#fabric-table').on('input', 'input[name="fabric_height[]"], input[name="fabric_breadth[]"], input[name="fabric_price_per_square_feet[]"], input[name="fabric_quantity[]"]', function() {
        calculateFabricRow($(this).closest('tr'));
    });
    
    // Initialize calculations
    console.log('Initializing calculations');
    
    // Add row buttons
    
    // Add row buttons
    // Add Product Row button implementation
    $(document).on('click', '.addProductRow, #addProductRowBtn', function(e) {
        e.preventDefault(); // Prevent any default action
        console.log('Add Product Row button clicked');
        
        try {
            // Get the first row (data row)
            var firstRow = $('table:has(.addProductRow) tbody tr:first');
            if (firstRow.length === 0) {
                firstRow = $('table:has(#addProductRowBtn) tbody tr:first');
            }
            console.log('First product row found:', firstRow.length > 0);
            
            // Clone it with events
            var newRow = firstRow.clone(true);
            console.log('Product row cloned');
            
            // Clear the inputs
            // Clear inputs except job_type
            newRow.find('input').not('[name="job_type[]"]').val('');
            newRow.find('select').val('');
            
            // Set default quantity to 1
            newRow.find('input[name="product_quantity[]"]').val(1);
            
            // Add the new row to the table before the button row
            var buttonRow = $(this).closest('tr');
            if (buttonRow.length === 0) {
                buttonRow = $('.addProductRow').closest('tr');
            }
            buttonRow.before(newRow);
            console.log('New product row added to table');
            
            // Make sure event handlers are attached
            newRow.find('select[name="product_id[]"]').off('change').on('change', function() {
                updateProductPrice(this);
            });
            
            newRow.find('input[name="product_quantity[]"]').off('input').on('input', function() {
                calculateProductRow($(this).closest('tr'));
            });
            
            // Calculate the row total for the new row
            calculateProductRow(newRow);
            
            console.log('Product row added successfully');
            
            // Alert to confirm row was added
            alert('New product row added successfully!');
        } catch (error) {
            console.error('Error adding product row:', error);
            alert('Error adding product row: ' + error.message);
        }
        
        return false; // Prevent default action
    });
    
    // Add event handler for custom-fabric-select
    $(document).on('change', '.custom-fabric-select', function() {
        console.log('Custom fabric select changed');
        updateFabricPrice(this);
    });
    
    // Add Fabric Row button implementation
    $(document).on('click', '.addFabricRow, #addFabricRowBtn', function(e) {
        e.preventDefault(); // Prevent any default action
        console.log('Add Fabric Row button clicked');
        
        try {
            // Get the first row (data row)
            var firstRow = $('table:has(.addFabricRow) tbody tr:first');
            if (firstRow.length === 0) {
                firstRow = $('table:has(#addFabricRowBtn) tbody tr:first');
            }
            console.log('First fabric row found:', firstRow.length > 0);
            
            // Clone it with events
            var newRow = firstRow.clone(true);
            console.log('Fabric row cloned');
            
            // Clear the inputs
            // Clear inputs except job_type
            newRow.find('input').not('[name="job_type[]"]').val('');
            newRow.find('select').val('');
            
            // Set default quantity to 1
            newRow.find('input[name="fabric_quantity[]"]').val(1);
            
            // Ensure the new row uses the custom-fabric-select class
            newRow.find('select[name="fabric_id[]"]').removeClass('fabric-select').addClass('custom-fabric-select');
            
            // Add the new row to the table before the button row
            var buttonRow = $(this).closest('tr');
            if (buttonRow.length === 0) {
                buttonRow = $('.addFabricRow').closest('tr');
            }
            buttonRow.before(newRow);
            console.log('New fabric row added to table');
            
            // Add event handlers to the new row
            newRow.find('select[name="fabric_id[]"]').off('change').on('change', function() {
                updateFabricPrice(this);
            });
            
            newRow.find('input[name="fabric_height[]"], input[name="fabric_breadth[]"], input[name="fabric_price_per_square_feet[]"], input[name="fabric_quantity[]"]').off('input').on('input', function() {
                calculateFabricRow($(this).closest('tr'));
            });
            
            // Calculate the row total for the new row
            calculateFabricRow(newRow);
            
            // Also calculate square feet and total price
            calculateFabricSquareFeetAndTotal();
            
            console.log('Fabric row added successfully');
            
            // Alert to confirm row was added
            alert('New fabric row added successfully!');
        } catch (error) {
            console.error('Error adding fabric row:', error);
            alert('Error adding fabric row: ' + error.message);
        }
        
        return false; // Prevent default action
    });
    
    // Trigger product calculations
    $('#product-table tbody tr').each(function() {
        console.log('Initializing product row');
        const productSelect = $(this).find('select[name="product_id[]"]');
        console.log('Product select found:', productSelect.length > 0, 'Value:', productSelect.val());
        
        if (productSelect.val()) {
            console.log('Product already selected, triggering change event');
            productSelect.trigger('change');
        } else {
            console.log('No product selected, calculating row with default values');
            calculateProductRow($(this));
        }
    });
    
    // Trigger fabric calculations
    $('#fabric-table tbody tr').each(function() {
        console.log('Initializing fabric row');
        const fabricSelect = $(this).find('select[name="fabric_id[]"]');
        console.log('Fabric select found:', fabricSelect.length > 0, 'Value:', fabricSelect.val());
        
        // Ensure it uses the custom-fabric-select class
        fabricSelect.removeClass('fabric-select').addClass('custom-fabric-select');
        
        if (fabricSelect.val()) {
            console.log('Fabric already selected, updating price directly');
            // Call updateFabricPrice directly instead of triggering change event
            // to avoid conflicts with fabric-calculations.js
            updateFabricPrice(fabricSelect[0]);
        } else {
            console.log('No fabric selected, calculating row with default values');
            calculateFabricRow($(this));
        }
    });
    
    // Remove row buttons
    $(document).on('click', '.remove-product', function() {
        if ($('#product-table tbody tr').length > 1) {
            $(this).closest('tr').remove();
            calculateProductGrandTotal();
        } else {
            // If it's the last row, just clear the values
            const row = $(this).closest('tr');
            row.find('input').val('');
            row.find('select').val('');
            row.find('.product-total-display').empty();
            calculateProductRow(row);
        }
    });
    
    $(document).on('click', '.remove-fabric', function() {
        if ($('#fabric-table tbody tr').length > 1) {
            $(this).closest('tr').remove();
            calculateFabricGrandTotal();
        } else {
            // If it's the last row, just clear the values
            const row = $(this).closest('tr');
            row.find('input').val('');
            row.find('select').val('');
            row.find('.fabric-sqft-display').empty();
            row.find('.fabric-total-display').empty();
            calculateFabricRow(row);
        }
    });
// Direct inline functions for price updates
    function updateProductPrice(selectElement) {
        console.log('updateProductPrice called');
        
        // Get the selected option and its price
        const selectedOption = $(selectElement).find('option:selected');
        const price = selectedOption.attr('data-price');
        
        console.log('Selected option:', selectedOption.text());
        console.log('Price from data-price:', price);
        
        // Get the row and price field
        const row = $(selectElement).closest('tr');
        const priceField = row.find('input[name="product_price[]"]');
        const quantityField = row.find('input[name="product_quantity[]"]');
        const totalPriceField = row.find('input[name="product_total_price[]"]');
        
        console.log('Price field found:', priceField.length > 0);
        
        // Try to extract price from option text if data-price is not available
        if (!price && selectedOption.text().includes('(Rs.')) {
            const priceMatch = selectedOption.text().match(/\(Rs\.\s*([\d.]+)/);
            if (priceMatch && priceMatch[1]) {
                console.log('Extracted price from text:', priceMatch[1]);
                priceField.val(priceMatch[1]);
                priceField.addClass('bg-light-yellow');
                setTimeout(() => {
                    priceField.removeClass('bg-light-yellow');
                }, 1000);
            }
        } else if (price) {
            // Set the price in the price field
            console.log('Setting price to:', price);
            priceField.val(price);
            priceField.addClass('bg-light-yellow');
            setTimeout(() => {
                priceField.removeClass('bg-light-yellow');
            }, 1000);
        } else {
            console.log('No price found');
            priceField.val('');
        }
        
        // Calculate the row total immediately
        console.log('Calling calculateProductRow from updateProductPrice - UPDATED');
        calculateProductRow(row);
    }
    
    function updateFabricPrice(selectElement) {
        console.log('updateFabricPrice called');
        
        // Get the selected option and its price
        const selectedOption = $(selectElement).find('option:selected');
        const price = selectedOption.attr('data-price');
        
        console.log('Selected fabric option:', selectedOption.text());
        console.log('Fabric price from data-price:', price);
        
        // Get the row and price field
        const row = $(selectElement).closest('tr');
        const priceField = row.find('input[name="fabric_price_per_square_feet[]"]');
        
        console.log('Fabric price field found:', priceField.length > 0);
        
        // Try to extract price from option text if data-price is not available
        if (!price && selectedOption.text().includes('(Rs.')) {
            const priceMatch = selectedOption.text().match(/\(Rs\.\s*([\d.]+)/);
            if (priceMatch && priceMatch[1]) {
                console.log('Extracted fabric price from text:', priceMatch[1]);
                priceField.val(priceMatch[1]);
                priceField.addClass('bg-light-yellow');
                setTimeout(() => {
                    priceField.removeClass('bg-light-yellow');
                }, 1000);
            }
        } else if (price) {
            // Set the price in the price field
            console.log('Setting fabric price to:', price);
            priceField.val(price);
            priceField.addClass('bg-light-yellow');
            setTimeout(() => {
                priceField.removeClass('bg-light-yellow');
            }, 1000);
        } else {
            console.log('No fabric price found');
            priceField.val('');
        }
        
        // Calculate the row total
        calculateFabricRow(row);
        
        // Also calculate square feet and total price
        calculateFabricSquareFeetAndTotal();
    }
    
    // Log initialization complete
    console.log('Initialization complete');
    
    // Add a direct event listener to the document for debugging
    $(document).ready(function() {
        console.log('Document ready event fired');
        
        // DIRECT BUTTON TARGETING
        // Target the specific button IDs
        
        // Product Add Row button
        $('#addProductRowBtn').on('click', function(e) {
            e.preventDefault();
            console.log('Product Add Row button clicked');
            
            // Get the first product row
            var firstRow = $('#product-table tbody tr:first');
            console.log('First product row found:', firstRow.length > 0);
            
            if (firstRow.length) {
                // Clone the row
                var newRow = firstRow.clone();
                
                // Clear inputs
                newRow.find('input').val('');
                newRow.find('select').val('');
                
                // Set quantity to 1
                newRow.find('input[name="product_quantity[]"]').val(1);
                
                // Insert before the button row
                $('#product-table .add-row-container').before(newRow);
                
                // Set up event handlers for the new row
                newRow.find('select[name="product_id[]"]').on('change', function() {
                    updateProductPrice(this);
                });
                
                newRow.find('input[name="product_quantity[]"]').on('input', function() {
                    calculateProductRow($(this).closest('tr'));
                });
                
                // Calculate the grand total after adding a new row
                calculateProductGrandTotal();
                
                console.log('New product row added');
                alert('New product row added successfully!');
            }
            
            return false;
        });
        
        // Fabric Add Row button
        $('#addFabricRowBtn').on('click', function(e) {
            e.preventDefault();
            console.log('Fabric Add Row button clicked');
            
            // Get the first fabric row
            var firstRow = $('#fabric-table tbody tr:first');
            console.log('First fabric row found:', firstRow.length > 0);
            
            if (firstRow.length) {
                // Clone the row
                var newRow = firstRow.clone();
                
                // Clear inputs except job_type
                newRow.find('input').not('[name="job_type[]"]').val('');
                newRow.find('select').val('');
                
                // Set quantity to 1
                newRow.find('input[name="fabric_quantity[]"]').val(1);
                
                // Insert before the button row
                $('#fabric-table .add-row-container').before(newRow);
                
                // Calculate the grand total after adding a new row
                calculateFabricGrandTotal();
                
                console.log('New fabric row added');
                alert('New fabric row added successfully!');
            }
            
            return false;
        });
    });
    
    // Function to prepare form data before submission
    function prepareFormData() {
        console.log('Preparing form data for submission');
        
        // Check if customer is selected
        const customerId = $('#customer_id').val();
        if (!customerId) {
            alert('Please select a customer before submitting the form.');
            $('#customer_id').focus();
            return false;
        }
        
        // SIMPLIFIED VALIDATION: Allow submission with either product OR fabric
        // This is a direct implementation that bypasses complex validation
        
        // Skip all other validation except customer selection
        // We already checked for customer selection above
        
        // Just log that we're proceeding with form submission
        console.log('Form validation passed, proceeding with submission');
        
        // Check if all product rows have a selected product
        let hasInvalidProduct = false;
        $('select[name="product_id[]"]').each(function() {
            if (!$(this).val()) {
                hasInvalidProduct = true;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (hasInvalidProduct) {
            alert('Please select a product for all product rows.');
            return false;
        }
        
        // Check if all fabric rows have a selected fabric
        let hasInvalidFabric = false;
        $('select[name="fabric_id[]"]').each(function() {
            if (!$(this).val()) {
                hasInvalidFabric = true;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (hasInvalidFabric) {
            alert('Please select a fabric for all fabric rows.');
            return false;
        }
        
        // Clear previous hidden fields
        $('#hidden-fields-container').empty();
        
        // Map product fields to controller expected fields
        let productCount = 0;
        $('input[name="product_price[]"]').each(function(index) {
            const row = $(this).closest('tr');
            const price = $(this).val();
            const quantity = row.find('input[name="product_quantity[]"]').val() || 1;
            const productId = row.find('select[name="product_id[]"]').val();
            
            // Create hidden fields with the names the controller expects
            // Check if the product ID is valid
            if (productId) {
                $('#hidden-fields-container').append(`
                    <input type="hidden" name="job_type[]" value="product">
                    <input type="hidden" name="product_id[]" value="${productId}">
                    <input type="hidden" name="price_per_square_feet[]" value="${price}">
                    <input type="hidden" name="quantity[]" value="${quantity}">
                    <input type="hidden" name="height[]" value="0">
                    <input type="hidden" name="breadth[]" value="0">
                    <input type="hidden" name="stitching[]" value="false">
                `);
                console.log(`Added product row: ID=${productId}, Price=${price}, Quantity=${quantity}`);
                productCount++;
            }
        });
        
        console.log(`Total product rows added: ${productCount}`);
        
        // Map fabric fields to controller expected fields
        let fabricCount = 0;
        $('input[name="fabric_price_per_square_feet[]"]').each(function(index) {
            const row = $(this).closest('tr');
            const price = $(this).val();
            const quantity = row.find('input[name="fabric_quantity[]"]').val() || 1;
            // Make sure height and breadth are not required
            let height = row.find('input[name="fabric_height[]"]').val();
            let breadth = row.find('input[name="fabric_breadth[]"]').val();
            
            // Set default values if empty or zero
            height = (height && parseFloat(height) > 0) ? height : 1;
            breadth = (breadth && parseFloat(breadth) > 0) ? breadth : 1;
            
            // Update the input fields with default values if they were 0 or empty
            if (!row.find('input[name="fabric_height[]"]').val() || parseFloat(row.find('input[name="fabric_height[]"]').val()) <= 0) {
                row.find('input[name="fabric_height[]"]').val(height);
            }
            if (!row.find('input[name="fabric_breadth[]"]').val() || parseFloat(row.find('input[name="fabric_breadth[]"]').val()) <= 0) {
                row.find('input[name="fabric_breadth[]"]').val(breadth);
            }
            
            const fabricId = row.find('select[name="fabric_id[]"]').val();
            
            // Create hidden fields with the names the controller expects
            // Check if the fabric ID is valid
            if (fabricId) {
                $('#hidden-fields-container').append(`
                    <input type="hidden" name="job_type[]" value="flag">
                    <input type="hidden" name="product_id[]" value="${fabricId}">
                    <input type="hidden" name="fabric_id[]" value="${fabricId}">
                    <input type="hidden" name="price_per_square_feet[]" value="${price}">
                    <input type="hidden" name="quantity[]" value="${quantity}">
                    <input type="hidden" name="height[]" value="${height}">
                    <input type="hidden" name="breadth[]" value="${breadth}">
                    <input type="hidden" name="stitching[]" value="true">
                `);
                console.log(`Added fabric row: ID=${fabricId}, Price=${price}, Quantity=${quantity}, Height=${height}, Breadth=${breadth}`);
                fabricCount++;
            }
        });
        
        console.log(`Total fabric rows added: ${fabricCount}`);
        
        // Validate that at least one product or fabric row was added
        if (productCount === 0 && fabricCount === 0) {
            alert('Please add at least one product or fabric item to the order.');
            return false;
        }
        
        // Log the form data for debugging
        console.log('Form data prepared for submission');
        console.log('Product fields:', $('input[name="product_price[]"]').length);
        console.log('Fabric fields:', $('input[name="fabric_price_per_square_feet[]"]').length);
        console.log('Hidden fields created:', $('#hidden-fields-container input').length);
        
        // Log all form data for debugging
        const formData = new FormData(document.getElementById('custom-order-form'));
        console.log('Form data entries:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        
        // Show a loading message
        $('button[type="submit"]').html('<i class="fa fa-spinner fa-spin"></i> Creating Order...');
        $('button[type="submit"]').prop('disabled', true);
        
        // DIRECT APPROACH: Show success message with jQuery modal
        // This is a more reliable way to show a modal dialog
        
        // Show a success message and redirect
        alert("ORDER SUCCESSFULLY CREATED!");
        
        // Create a Bootstrap modal dynamically
        var modalHtml = `
            <div class="modal fade" id="dynamicSuccessModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" id="successModalLabel"><i class="fa fa-check-circle"></i> ORDER SUCCESSFULLY CREATED!</h5>
                        </div>
                        <div class="modal-body text-center">
                            <i class="fa fa-check-circle text-success" style="font-size: 100px;"></i>
                            <h2 class="mt-4 mb-3">Order Created Successfully!</h2>
                            <p class="mb-4" style="font-size: 20px;">Redirecting to new form in <span id="modal-countdown">5</span> seconds...</p>
                            <div class="progress mb-4" style="height: 25px;">
                                <div id="modal-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Append the modal to the body
        $('body').append(modalHtml);
        
        // Show the modal
        $('#dynamicSuccessModal').modal('show');
        
        // Start countdown for redirect
        var count = 5;
        var countdownInterval = setInterval(function() {
            count--;
            $('#modal-countdown').text(count);
            $('#modal-progress-bar').css('width', (count/5 * 100) + '%');
            
            if (count <= 0) {
                clearInterval(countdownInterval);
                // Redirect to new form page
                window.location.href = "{{ route('custom-orders.create') }}";
            }
        }, 1000);
        
        // Return true to allow the form to submit
        return true;
    }
});
</script>
@endpush

@push('scripts')
<!-- Include the fabric-calculations.js file -->
<script src="{{asset('/')}}js/fabric-calculations.js"></script>

<!-- Include the add-row-fix.js file -->
<script src="{{asset('/')}}js/add-row-fix.js"></script>

<!-- Include our custom order submission handler -->
<script src="{{asset('/')}}js/custom-order-submit.js"></script>
@endpush

@push('scripts')
<!-- Make sure required JS files are loaded first -->
<script src="{{asset('/')}}js/fabric-calculations.js"></script>
<script src="{{asset('/')}}js/add-row-fix.js"></script>

<!-- Load the edit order populate script -->
<script src="{{asset('/')}}js/edit-order-populate.js"></script>

<!-- Include test script for development -->
@if(config('app.env') === 'local')
<script src="{{asset('/')}}js/test-edit-order.js"></script>
@endif

<!-- This script must be loaded after add-row-fix.js -->
@if(isset($isEditing) && $isEditing)
<script>
    // Define order data as global variables
    window.isEditing = true;
    window.orderData = {!! json_encode($customOrder) !!};
    window.flagDetails = {!! json_encode($customOrder->flagDetails) !!};
    
    // Display a notification that we're in edit mode
    document.addEventListener('DOMContentLoaded', function() {
        // Show a notification instead of an alert
        const notification = document.createElement('div');
        notification.className = 'alert alert-info alert-dismissible fade show';
        notification.innerHTML = `
            <strong><i class="fa fa-pencil"></i> Edit Mode:</strong>
            You are editing order #${window.orderData.id}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        `;
        
        // Insert at the top of the form
        const form = document.getElementById('custom-order-form');
        if (form) {
            form.insertBefore(notification, form.firstChild);
        }
        
        console.log("Edit mode initialized for order #" + window.orderData.id);
    });
</script>
@endif
@endpush