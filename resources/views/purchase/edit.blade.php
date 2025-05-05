@extends('layouts.master')

@section('title', 'Edit Purchase | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-edit"></i> Edit Purchase #{{ $purchase->id }}</h1>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item"><a href="{{ route('purchases.index') }}">Purchases</a></li>
                <li class="breadcrumb-item active">Edit Purchase</li>
            </ul>
        </div>

        <div class="row">
            <div class="clearix"></div>
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title">Edit Purchase</h3>
                    <div class="tile-body">
                        <form method="POST" action="{{ route('purchases.update', $purchase->id) }}">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label class="control-label">Supplier</label>
                                    <select name="supplier_id" class="form-control @error('supplier_id') is-invalid @enderror" required>
                                        <option value="">Select Supplier</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" {{ $purchase->supplier_id == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('supplier_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="control-label">Date</label>
                                    <input name="date" class="form-control @error('date') is-invalid @enderror" type="date" value="{{ $purchase->date }}" required>
                                    @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="control-label">Notes (Optional)</label>
                                    <input name="notes" class="form-control" type="text" placeholder="Purchase notes" value="{{ $purchase->notes }}">
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
                                                    <th class="fabric-field" style="display:none;">Roll Info</th>
                                                    <th>Purchase Rate</th>
                                                    <th>Discount (%)</th>
                                                    <th>Amount</th>
                                                    <th><a class="btn btn-sm btn-success addRow"><i class="fa fa-plus"></i></a></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($purchase->purchaseDetails as $detail)
                                                <tr>
                                                    <td>
                                                        <select name="product_id[]" class="form-control productname @error('product_id.*') is-invalid @enderror" required>
                                                            <option value="">Select Product</option>
                                                            @foreach($products as $product)
                                                                <option value="{{ $product->id }}"
                                                                    data-is-fabric="{{ $product->is_fabric ? 'true' : 'false' }}"
                                                                    data-track-by-roll="{{ $product->track_by_roll ? 'true' : 'false' }}"
                                                                    {{ $detail->product_id == $product->id ? 'selected' : '' }}>
                                                                    {{ $product->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <input type="hidden" name="is_fabric[]" class="is-fabric" value="{{ $detail->product->is_fabric ? 'true' : 'false' }}">
                                                        <input type="hidden" name="track_by_roll[]" class="track-by-roll" value="{{ $detail->product->track_by_roll ? 'true' : 'false' }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="qty[]" class="form-control qty @error('qty.*') is-invalid @enderror" min="1" value="{{ $detail->qty }}" required>
                                                    </td>
                                                    @php
                                                        $fabricRoll = null;
                                                        if ($detail->product->is_fabric && $detail->product->track_by_roll) {
                                                            $fabricRoll = \App\FabricRoll::where('product_id', $detail->product_id)
                                                                ->where('notes', 'like', '%Purchase #' . $purchase->id . '%')
                                                                ->first();
                                                        }
                                                    @endphp
                                                    <td class="fabric-field" @if($detail->product->is_fabric && $detail->product->track_by_roll) style="display:table-cell;" @else style="display:none;" @endif>
                                                        <input type="number" name="width[]" class="form-control width" min="1" step="0.01" placeholder="Width"
                                                            value="{{ $fabricRoll ? $fabricRoll->width : '' }}"
                                                            @if($detail->product->is_fabric && $detail->product->track_by_roll) required @endif>
                                                    </td>
                                                    <td class="fabric-field" @if($detail->product->is_fabric && $detail->product->track_by_roll) style="display:table-cell;" @else style="display:none;" @endif>
                                                        <input type="number" name="length[]" class="form-control length" min="1" step="0.01" placeholder="Length"
                                                            value="{{ $fabricRoll ? $fabricRoll->length : '' }}"
                                                            @if($detail->product->is_fabric && $detail->product->track_by_roll) required @endif>
                                                    </td>
                                                    <td class="fabric-field roll-info-cell" @if($detail->product->is_fabric && $detail->product->track_by_roll) style="display:table-cell;" @else style="display:none;" @endif>
                                                        <div class="square-feet-info text-info small">
                                                            @if($fabricRoll && $detail->product->is_fabric && $detail->product->track_by_roll)
                                                                Each roll: {{ number_format($fabricRoll->original_square_feet, 2) }} sq ft
                                                            @endif
                                                        </div>
                                                        <div class="roll-preview text-success small">
                                                            @if($detail->product->is_fabric && $detail->product->track_by_roll)
                                                                Will create {{ $detail->qty }} fabric roll(s) with these dimensions
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="price[]" class="form-control price @error('price.*') is-invalid @enderror" min="0" step="0.01" value="{{ $detail->price }}" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="dis[]" class="form-control dis @error('dis.*') is-invalid @enderror" min="0" max="100" value="{{ $detail->discount }}" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="amount[]" class="form-control amount @error('amount.*') is-invalid @enderror" value="{{ $detail->amount }}" readonly required>
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-danger btn-sm remove"><i class="fa fa-trash"></i></a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="3"></td>
                                                    <td><b>Total</b></td>
                                                    <td><b class="total">{{ number_format($purchase->total_amount, 2) }}</b></td>
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
                                        <i class="fa fa-fw fa-lg fa-check-circle"></i>Update Purchase
                                    </button>
                                    <a class="btn btn-secondary" href="{{ route('purchases.show', $purchase->id) }}">
                                        <i class="fa fa-fw fa-lg fa-times-circle"></i>Cancel
                                    </a>
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
                
                // Calculate square feet (width and length are in inches)
                var squareFeet = (width * length) / 144;
                
                // Display square feet info if both width and length are provided
                if (width > 0 && length > 0) {
                    var infoText = 'Each roll: ' + squareFeet.toFixed(2) + ' sq ft';
                    var rollPreview = 'Will create ' + qty + ' fabric roll(s) with these dimensions';
                    
                    // Update info text
                    tr.find('.square-feet-info').text(infoText);
                    tr.find('.roll-preview').text(rollPreview);
                }
            }
            
            var amount = (qty * price) - ((qty * price * dis) / 100);
            tr.find('.amount').val(parseFloat(amount).toFixed(2));
            
            calculateTotal();
        });

        // Calculate total amount
        function calculateTotal() {
            var total = 0;
            $('.amount').each(function() {
                var amount = parseFloat($(this).val()) || 0;
                total += amount;
            });
            $('.total').text(parseFloat(total).toFixed(2));
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
                        <div class="square-feet-info text-info small"></div>
                        <div class="roll-preview text-success small"></div>
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

        // Initialize calculations and show fabric fields if needed
        calculateTotal();
        
        // Show fabric fields for existing rows if needed
        $('tbody tr').each(function() {
            var isFabric = $(this).find('.is-fabric').val() === 'true';
            var trackByRoll = $(this).find('.track-by-roll').val() === 'true';
            
            if (isFabric && trackByRoll) {
                $('.fabric-field').show();
            }
        });
    });
</script>
@endpush
