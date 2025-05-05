@extends('layouts.master')

@section('title', 'Add Purchase | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-edit"></i> Add Purchase</h1>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Purchase</li>
                <li class="breadcrumb-item"><a href="#">Add Purchase</a></li>
            </ul>
        </div>

        <div class="row">
            <div class="clearix"></div>
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title">Purchase Entry</h3>
                    <div class="tile-body">
                        <form method="POST" action="{{ route('purchases.store') }}">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label class="control-label">Supplier</label>
                                    <select name="supplier_id" class="form-control @error('supplier_id') is-invalid @enderror" required>
                                        <option value="">Select Supplier</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('supplier_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="control-label">Date</label>
                                    <input name="date" class="form-control @error('date') is-invalid @enderror" type="date" value="{{ date('Y-m-d') }}" required>
                                    @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="control-label">Notes (Optional)</label>
                                    <input name="notes" class="form-control" type="text" placeholder="Purchase notes">
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="purchase-items">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Quantity</th>
                                                    <th class="fabric-field" style="display:none;">Width (ft)</th>
                                                    <th class="fabric-field" style="display:none;">Length (ft)</th>
                                                    <th class="fabric-field" style="display:none;">Square Feet Calculation</th>
                                                    <th>Purchase Rate</th>
                                                    <th>Discount (%)</th>
                                                    <th>Amount</th>
                                                    <th><a class="btn btn-sm btn-success addRow"><i class="fa fa-plus"></i></a></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <select name="product_id[]" class="form-control productname @error('product_id.*') is-invalid @enderror" required>
                                                            <option value="">Select Product</option>
                                                            @foreach($products as $product)
                                                                <option value="{{ $product->id }}"
                                                                    data-is-fabric="{{ $product->is_fabric ? 'true' : 'false' }}"
                                                                    data-track-by-roll="{{ $product->track_by_roll ? 'true' : 'false' }}">
                                                                    {{ $product->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <input type="hidden" name="is_fabric[]" class="is-fabric" value="false">
                                                        <input type="hidden" name="track_by_roll[]" class="track-by-roll" value="false">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="qty[]" class="form-control qty @error('qty.*') is-invalid @enderror" min="1" required>
                                                    </td>
                                                    <td class="fabric-field" style="display:none;">
                                                        <input type="number" name="width[]" class="form-control width" min="1" step="0.01" placeholder="Width">
                                                    </td>
                                                    <td class="fabric-field" style="display:none;">
                                                        <input type="number" name="length[]" class="form-control length" min="1" step="0.01" placeholder="Length">
                                                    </td>
                                                    <td class="fabric-field roll-info-cell" style="display:none;">
                                                        <div class="square-feet-info text-info"></div>
                                                        <div class="roll-preview text-success"></div>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="price[]" class="form-control price @error('price.*') is-invalid @enderror" min="0" step="0.01" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="dis[]" class="form-control dis @error('dis.*') is-invalid @enderror" min="0" max="100" value="0" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="amount[]" class="form-control amount @error('amount.*') is-invalid @enderror" readonly required>
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-danger btn-sm remove"><i class="fa fa-trash"></i></a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="5" class="fabric-field-colspan"></td>
                                                    <td colspan="2" class="no-fabric-colspan"></td>
                                                    <td><b>Total</b></td>
                                                    <td><b class="total">0.00</b></td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fa fa-fw fa-lg fa-check-circle"></i>Save Purchase
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

@push('js')
<style>
    /* Styling for fabric roll calculations */
    .roll-info-cell {
        transition: all 0.3s ease;
    }
    
    .roll-info-cell.highlighted {
        background-color: #f0f8ff !important;
        border: 2px solid #28a745;
    }
    
    .square-feet-info, .roll-preview {
        padding: 8px;
        border-radius: 5px;
        margin-bottom: 5px;
    }
    
    /* Pulse animation for calculations */
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.03); }
        100% { transform: scale(1); }
    }
    
    .pulse-animation {
        animation: pulse 0.5s ease-in-out;
    }
</style>

<script type="text/javascript">
    $(document).ready(function() {
        // When supplier is selected
        $('select[name="supplier_id"]').on('change', function() {
            // You could load supplier-specific pricing here if needed
        });

        // When product is selected
        $('tbody').delegate('.productname', 'change', function() {
            var tr = $(this).closest('tr');
            var productId = tr.find('.productname').val();
            var supplierId = $('select[name="supplier_id"]').val();
            var selectedOption = $(this).find('option:selected');
            var isFabric = selectedOption.data('is-fabric') === true;
            var trackByRoll = selectedOption.data('track-by-roll') === true;
            
            // Update hidden fields
            tr.find('.is-fabric').val(isFabric);
            tr.find('.track-by-roll').val(trackByRoll);
            
            // Update footer colspan
            updateFooterColspan();
            
            // Show/hide fabric fields
            if (isFabric && trackByRoll) {
                tr.find('.fabric-field').show();
                tr.find('.width, .length').prop('required', true);
                
                // Also show header columns
                $('.fabric-field').show();
            } else {
                tr.find('.fabric-field').hide();
                tr.find('.width, .length').prop('required', false);
                
                // Check if any other rows need fabric fields
                var anyFabricVisible = false;
                $('tbody tr').each(function() {
                    if ($(this).find('.is-fabric').val() === 'true' &&
                        $(this).find('.track-by-roll').val() === 'true') {
                        anyFabricVisible = true;
                    }
                });
                
                if (!anyFabricVisible) {
                    $('.fabric-field').hide();
                }
                
                // Update footer colspan
                updateFooterColspan();
            }

            if (productId && supplierId) {
                $.ajax({
                    type: 'GET',
                    url: "{{ route('findPricePurchase') }}",
                    dataType: 'json',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        'id': productId,
                        'supplier_id': supplierId
                    },
                    success: function(data) {
                        if (data && data.price) {
                            tr.find('.price').val(data.price);
                        }
                        tr.find('.qty').focus();
                    },
                    error: function() {
                        // If no supplier price is found, leave it blank for manual entry
                        tr.find('.qty').focus();
                    }
                });
            }
        });

        // Calculate amount when quantity, price, discount, width or length changes
        $('tbody').delegate('.qty, .price, .dis, .width, .length', 'keyup change', function() {
            var tr = $(this).closest('tr');
            var qty = tr.find('.qty').val() || 0;
            var price = tr.find('.price').val() || 0;
            var dis = tr.find('.dis').val() || 0;
            var isFabric = tr.find('.is-fabric').val() === 'true';
            var trackByRoll = tr.find('.track-by-roll').val() === 'true';
            
            // For fabric products tracked by roll, calculate square feet
            if (isFabric && trackByRoll) {
                var width = tr.find('.width').val() || 0;
                var length = tr.find('.length').val() || 0;
                
                // Calculate square feet per roll (width and length are in feet)
                var squareFeetPerRoll = width * length;
                
                // Calculate total square feet for all rolls
                var totalSquareFeet = squareFeetPerRoll * qty;
                
                // Add pulse animation effect when values change
                tr.find('.roll-info-cell').addClass('highlighted pulse-animation');
                setTimeout(function() {
                    tr.find('.roll-info-cell').removeClass('pulse-animation');
                }, 500);
                
                // Display square feet info if both width and length are provided
                if (width > 0 && length > 0) {
                    // Format the calculation display similar to custom orders
                    var infoText = '<div style="font-size: 16px; font-weight: bold; color: #007bff;">' +
                                   width + ' × ' + length + ' = ' +
                                   '<span style="color: #dc3545; font-size: 18px;">' + squareFeetPerRoll.toFixed(2) + '</span> sq ft per roll</div>';
                    
                    var totalText = '<div style="font-size: 16px; font-weight: bold; margin-top: 5px;">' +
                                    '<span style="color: #dc3545; font-size: 18px;">' + squareFeetPerRoll.toFixed(2) + '</span> × ' +
                                    '<span style="color: #007bff;">' + qty + '</span> = ' +
                                    '<span style="color: #28a745; font-size: 20px;">' + totalSquareFeet.toFixed(2) + '</span> total sq ft</div>';
                    
                    var rollPreview = '<div style="margin-top: 5px; font-weight: bold;">Will create ' + qty + ' fabric roll(s) with these dimensions</div>';
                    
                    // Update info text with more visual styling
                    tr.find('.square-feet-info').html(infoText);
                    tr.find('.roll-preview').html(rollPreview + totalText);
                    
                    // Apply styling to the cell for better visibility
                    tr.find('.roll-info-cell').css({
                        'background-color': '#f8f9fa',
                        'border-radius': '5px',
                        'padding': '8px',
                        'box-shadow': '0 2px 4px rgba(0,0,0,0.1)'
                    });
                    
                    // Calculate amount based on total square feet (similar to custom orders)
                    var amount = (totalSquareFeet * price) - ((totalSquareFeet * price * dis) / 100);
                    tr.find('.amount').val(parseFloat(amount).toFixed(2));
                } else {
                    // If width or length is not provided, calculate normally
                    var amount = (qty * price) - ((qty * price * dis) / 100);
                    tr.find('.amount').val(parseFloat(amount).toFixed(2));
                }
            } else {
                // For non-fabric products, calculate normally
                var amount = (qty * price) - ((qty * price * dis) / 100);
                tr.find('.amount').val(parseFloat(amount).toFixed(2));
            }
            
            calculateTotal();
        });

        // Calculate total amount
        function calculateTotal() {
            var total = 0;
            $('.amount').each(function() {
                var amount = parseFloat($(this).val()) || 0;
                total += amount;
            });
            
            // Add animation to the total when it changes
            var totalElement = $('.total');
            var oldTotal = parseFloat(totalElement.text()) || 0;
            
            if (oldTotal !== total) {
                totalElement.addClass('pulse-animation');
                setTimeout(function() {
                    totalElement.removeClass('pulse-animation');
                }, 500);
            }
            
            totalElement.text(parseFloat(total).toFixed(2));
        }

        // Add new row
        $('.addRow').on('click', function() {
            var newRow = `
                <tr>
                    <td>
                        <select name="product_id[]" class="form-control productname" required>
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}"
                                    data-is-fabric="{{ $product->is_fabric ? 'true' : 'false' }}"
                                    data-track-by-roll="{{ $product->track_by_roll ? 'true' : 'false' }}">
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="is_fabric[]" class="is-fabric" value="false">
                        <input type="hidden" name="track_by_roll[]" class="track-by-roll" value="false">
                    </td>
                    <td>
                        <input type="number" name="qty[]" class="form-control qty" min="1" required>
                    </td>
                    <td class="fabric-field" style="display:none;">
                        <input type="number" name="width[]" class="form-control width" min="1" step="0.01" placeholder="Width">
                    </td>
                    <td class="fabric-field" style="display:none;">
                        <input type="number" name="length[]" class="form-control length" min="1" step="0.01" placeholder="Length">
                    </td>
                    <td class="fabric-field roll-info-cell" style="display:none;">
                        <div class="square-feet-info text-info"></div>
                        <div class="roll-preview text-success"></div>
                    </td>
                    <td>
                        <input type="number" name="price[]" class="form-control price" min="0" step="0.01" required>
                    </td>
                    <td>
                        <input type="number" name="dis[]" class="form-control dis" min="0" max="100" value="0" required>
                    </td>
                    <td>
                        <input type="number" name="amount[]" class="form-control amount" readonly required>
                    </td>
                    <td>
                        <a class="btn btn-danger btn-sm remove"><i class="fa fa-trash"></i></a>
                    </td>
                </tr>
            `;
            $('tbody').append(newRow);
        });

        // Remove row
        $('tbody').on('click', '.remove', function() {
            var rowCount = $('tbody tr').length;
            if (rowCount > 1) {
                $(this).closest('tr').remove();
                calculateTotal();
            } else {
                alert('At least one product is required.');
            }
        });
        
        // Function to update footer colspan based on fabric fields visibility
        function updateFooterColspan() {
            var fabricFieldsVisible = $('.fabric-field:visible').length > 0;
            
            if (fabricFieldsVisible) {
                $('.fabric-field-colspan').attr('colspan', 5);
                $('.no-fabric-colspan').attr('colspan', 0);
            } else {
                $('.fabric-field-colspan').attr('colspan', 0);
                $('.no-fabric-colspan').attr('colspan', 5);
            }
        }
        
        // Initialize footer colspan
        updateFooterColspan();
    });
</script>
@endpush
