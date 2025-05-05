@extends('layouts.master')
@section('title', 'Create Flag Order')

@section('content')
    @include('partials.header')
    @include('partials.sidebar')
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-flag"></i> Create Flag Order</h1>
                <p>Create a new custom flag order</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item"><a href="{{ route('custom-orders.index') }}">Custom Orders</a></li>
                <li class="breadcrumb-item">Create Flag Order</li>
            </ul>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-body">
                        <form method="POST" action="{{ route('custom-orders.store') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="order_type" value="flag">
                            <input type="hidden" name="job_type[]" value="flag">
                            
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h4 class="mb-0"><i class="fa fa-file-text"></i> Job Order Form</h4>
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
                                                    <h4 class="text-danger mb-0">{{ $orderNumber ?? '887' }}</h4>
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
                                                        <option value="{{ $customer->id }}" {{ $customer->id == $customer_id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Address:</label>
                                                <input type="text" class="form-control" id="customer_address" value="{{ $customer->address ?? '' }}" readonly>
                                            </div>
                                            <div class="form-group">
                                                <label>Contact:</label>
                                                <input type="text" class="form-control" id="customer_contact" value="{{ $customer->mobile ?? '' }}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Order Date:</label>
                                                <input type="date" name="order_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Delivery Date:</label>
                                                <input type="date" name="delivery_date" class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Email:</label>
                                                <input type="email" class="form-control" id="customer_email" value="{{ $customer->email ?? '' }}" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="border-bottom pb-2 mb-3">JOB DETAILS:</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Job Type:</label>
                                                <select name="job_type" class="form-control" required>
                                                    <option value="">Select Job Type</option>
                                                    <option value="banner">Banner</option>
                                                    <option value="custom_flag">Custom Flag</option>
                                                    <option value="saas">SAAS</option>
                                                    <option value="country_flag">Country Flag</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Fabric Type:</label>
                                                <select name="fabric_type" class="form-control" required>
                                                    <option value="">Select Fabric Type</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->name }}">{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Fabric Composition:</label>
                                                <select name="fabric_composition" class="form-control">
                                                    <option value="100% Polyester">100% Polyester</option>
                                                    <option value="100% Cotton">100% Cotton</option>
                                                    <option value="Polyester Blend">Polyester Blend</option>
                                                    <option value="Satin Silk">Satin Silk</option>
                                                    <option value="Knitted Polyester">Knitted Polyester</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Design Provided:</label>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="design_provided" id="design_yes" value="Yes">
                                                    <label class="form-check-label" for="design_yes">Yes</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="design_provided" id="design_no" value="No">
                                                    <label class="form-check-label" for="design_no">No</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Fabric Weight:</label>
                                                <select name="fabric_weight" class="form-control">
                                                    <option value="110 gsm">110 gsm (Lightweight)</option>
                                                    <option value="130 gsm">130 gsm (Standard)</option>
                                                    <option value="160 gsm">160 gsm (Medium)</option>
                                                    <option value="200 gsm">200 gsm (Heavy)</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Fabric Origin:</label>
                                                <select name="fabric_origin" class="form-control">
                                                    <option value="Imported">Imported</option>
                                                    <option value="Local">Local</option>
                                                    <option value="China">China</option>
                                                    <option value="India">India</option>
                                                    <option value="Thailand">Thailand</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Received by:</label>
                                                <input type="text" name="received_by" class="form-control" value="{{ $currentUser->full_name }}" readonly>
                                                <input type="hidden" name="user_id" value="{{ $currentUser->id }}">
                                            </div>
                                            <div class="form-group">
                                                <label>Contact Through:</label>
                                                <select name="contact_through" class="form-control">
                                                    <option value="">Select Contact Method</option>
                                                    <option value="whatsapp">WhatsApp</option>
                                                    <option value="instagram">Instagram</option>
                                                    <option value="facebook">Facebook</option>
                                                    <option value="linkedin">LinkedIn</option>
                                                    <option value="website">Website</option>
                                                    <option value="friends">Friends</option>
                                                    <option value="referral">Referral</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Initial Payment:</label>
                                                <input type="number" name="initial_payment" class="form-control" step="0.01">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Flag Details Section -->
                                    <div class="mt-4">
                                        <h6 class="border-bottom pb-2">Flag Details</h6>
                                        <div class="table-responsive" style="width: 100%; max-width: 1200px; margin: 0 auto;">
                                            <table class="table table-bordered table-hover" style="width: 100%;">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th width="20%">Flag Type</th>
                                                        <th width="8%">Height</th>
                                                        <th width="8%">Breadth</th>
                                                        <th width="8%">SqFt</th>
                                                        <th width="12%">Price/SqFt</th>
                                                        <th width="12%">Qty</th>
                                                        <th width="20%">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="flag-items">
                                                    <tr>
                                                        <td>
                                                            <input type="hidden" name="product_id[]" value="-1">
                                                            <input type="hidden" name="job_type[]" value="flag">
                                                            <select name="flag_type[]" class="form-control flag-type-select" required>
                                                                <option value="">Select Type</option>
                                                                <option value="national">National Flag</option>
                                                                <option value="company">Company Flag</option>
                                                                <option value="custom">Custom Design</option>
                                                            </select>
                                                        </td>
                                                        <td><input type="number" step="0.01" name="height[]" class="form-control dimension-input" required></td>
                                                        <td><input type="number" step="0.01" name="breadth[]" class="form-control dimension-input" required></td>
                                                        <td><input type="text" name="square_feet[]" class="form-control bg-light" readonly></td>
                                                        <td><input type="number" step="0.01" name="price_per_square_feet[]" class="form-control price-input" required></td>
                                                        <td><input type="number" name="quantity[]" class="form-control" min="1" required value="1"></td>
                                                        <td>
                                                            <div class="d-flex">
                                                                <input type="text" name="total_price[]" class="form-control bg-light font-weight-bold" readonly>
                                                                <a class="btn btn-danger remove ml-2"><i class="fa fa-remove"></i></a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="add-row-container">
                                                        <td colspan="7" class="p-0">
                                                            <button type="button" id="addRowBtn" class="btn btn-info addRow w-100 py-3" style="border-radius: 0; margin: 0; width: 100%; display: block; border: none;">
                                                                <i class="fa fa-plus"></i> Add Row
                                                            </button>
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
                                                    <span class="grand-total" style="font-size: 18px;">Rs. 0.00</span>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                    </div>

                                    <div class="form-group mt-3">
                                        <label>Remarks:</label>
                                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                                    </div>

                                    <div class="mt-3">
                                        <p class="small">
                                            I agree to pay the total amount when payment is due for service requested and product/materials used.<br>
                                            I acknowledge that all changes have been made as per the order specifications and agree not to dispute the product after receiving it.
                                        </p>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Client's Signature:</label>
                                                <input type="text" name="client_signature" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Payment Method:</label>
                                                <select name="payment_method" class="form-control">
                                                    <option value="Online">Online</option>
                                                    <option value="Cash">Cash</option>
                                                    <option value="Cheque">Cheque</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="design_file">Design File</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="design_file" name="design_file" accept=".jpg,.jpeg,.png,.pdf">
                                            <label class="custom-file-label" for="design_file">Choose file</label>
                                        </div>
                                        <small class="form-text text-muted">Accepted formats: JPG, PNG, PDF (max 2MB)</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Flag Details section has been moved up to replace the Order Items table -->

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Create Order
                                </button>
                                <a href="{{ route('custom-orders.index') }}" class="btn btn-secondary">
                                    <i class="fa fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Calculate row total
    function calculateRow() {
        try {
            const rows = document.querySelectorAll('#flag-items tr:not(.add-row-container)');
            let grandTotal = 0;
            
            rows.forEach(row => {
                // Skip if this is the button row
                if (row.classList.contains('add-row-container')) {
                    return;
                }
                
                // Get input elements
                const heightInput = row.querySelector('input[name="height[]"]');
                const breadthInput = row.querySelector('input[name="breadth[]"]');
                const priceInput = row.querySelector('input[name="price_per_square_feet[]"]');
                const quantityInput = row.querySelector('input[name="quantity[]"]');
                const sqftInput = row.querySelector('input[name="square_feet[]"]');
                const totalInput = row.querySelector('input[name="total_price[]"]');
                
                // Skip if any required element is missing
                if (!heightInput || !breadthInput || !priceInput || !quantityInput || !sqftInput || !totalInput) {
                    console.log('Skipping row - missing required elements');
                    return;
                }
                
                // Get values with fallbacks
                const height = parseFloat(heightInput.value) || 0;
                const breadth = parseFloat(breadthInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                const quantity = parseInt(quantityInput.value) || 1;
                
                // Calculate square feet
                const sqft = height * breadth;
                sqftInput.value = sqft.toFixed(2) + ' sqft';
                
                // Calculate total price
                const total = sqft * price * quantity;
                totalInput.value = 'Rs. ' + total.toFixed(2);
                
                grandTotal += total;
            });
            
            // Update grand total
            const grandTotalElement = document.querySelector('.grand-total');
            if (grandTotalElement) {
                grandTotalElement.textContent = 'Rs. ' + grandTotal.toFixed(2);
            }
        } catch (error) {
            console.error('Error in calculateRow:', error);
        }
    }
    
    // Add event listeners to input fields
    document.querySelectorAll('#flag-items input').forEach(input => {
        input.addEventListener('input', calculateRow);
    });
    
    // Add Row button implementation
    $(document).on('click', '.addRow', function(e) {
        e.preventDefault(); // Prevent any default action
        console.log('Add Row button clicked');
        
        // Get the first row (data row)
        var firstRow = $('#flag-items tr:first');
        console.log('First row found:', firstRow.length > 0);
        
        // Clone it
        var newRow = firstRow.clone(true);
        console.log('Row cloned');
        
        // Clear the inputs
        newRow.find('input').val('');
        newRow.find('select').val('');
        
        // Restore the job_type value to 'flag'
        newRow.find('input[name="job_type[]"]').val('flag');
        
        // Set default quantity to 1
        newRow.find('input[name="quantity[]"]').val(1);
        
        // Insert the new row before the button row
        $('.add-row-container').before(newRow);
        console.log('New row added to table');
        
        // Make sure event listeners are attached
        newRow.find('input').off('input').on('input', calculateRow);
        
        // Recalculate totals
        calculateRow();
        
        return false; // Prevent default action
    });
    
    // Remove row button
    $(document).on('click', '.remove', function() {
        if ($('#flag-items tr').length > 2) { // 2 because we have the button row
            $(this).closest('tr').remove();
            calculateRow();
        } else {
            // If it's the last row, just clear the values
            const row = $(this).closest('tr');
            row.find('input').val('');
            row.find('input[name="quantity[]"]').val(1);
            calculateRow();
        }
    });
    
    // Initialize calculations
    calculateRow();
    
    // Direct click handler for the Add Row button
    $('#addRowBtn').on('click', function(e) {
        e.preventDefault();
        console.log('Add Row button clicked directly');
        
        // Get the first row (data row)
        var firstRow = $('#flag-items tr:first');
        console.log('First row found:', firstRow.length > 0);
        
        // Clone it
        var newRow = firstRow.clone(true);
        console.log('Row cloned');
        
        // Clear the inputs
        newRow.find('input').val('');
        newRow.find('select').val('');
        
        // Restore the job_type value to 'flag'
        newRow.find('input[name="job_type[]"]').val('flag');
        
        // Set default quantity to 1
        newRow.find('input[name="quantity[]"]').val(1);
        
        // Insert the new row before the button row
        $('.add-row-container').before(newRow);
        console.log('New row added to table');
        
        // Make sure event listeners are attached
        newRow.find('input').off('input').on('input', calculateRow);
        
        // Recalculate totals
        calculateRow();
    });
});
</script>
