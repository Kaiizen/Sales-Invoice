@extends('layouts.master')

@section('title', 'Purchase Details | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-file-text-o"></i> Purchase #{{ $purchase->id }}</h1>
                <p>Purchase details and items</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item"><a href="{{ route('purchases.index') }}">Purchases</a></li>
                <li class="breadcrumb-item active">Purchase #{{ $purchase->id }}</li>
            </ul>
        </div>

        @if(session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <section class="invoice">
                        <div class="row mb-4">
                            <div class="col-6">
                                <h2 class="page-header">Purchase Information</h2>
                            </div>
                            <div class="col-6">
                                <h5 class="text-right">Date: {{ $purchase->date }}</h5>
                            </div>
                        </div>
                        <div class="row invoice-info">
                            <div class="col-md-6">
                                <h5>Supplier Information:</h5>
                                <address>
                                    <strong>{{ $purchase->supplier->name }}</strong><br>
                                    {{ $purchase->supplier->address }}<br>
                                    Phone: {{ $purchase->supplier->mobile }}<br>
                                    Details: {{ $purchase->supplier->details }}
                                </address>
                            </div>
                            <div class="col-md-6 text-right">
                                <h5>Purchase Details:</h5>
                                <b>Purchase ID:</b> {{ $purchase->id }}<br>
                                <b>Date:</b> {{ $purchase->date }}<br>
                                <b>Total Amount:</b> {{ number_format($purchase->total_amount, 2) }}<br>
                                @if($purchase->notes)
                                    <b>Notes:</b> {{ $purchase->notes }}
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 table-responsive mt-4">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Discount (%)</th>
                                            <th>Subtotal</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchase->purchaseDetails as $detail)
                                            <tr>
                                                <td>
                                                    {{ $detail->product->name }}
                                                    @if($detail->product->is_fabric && $detail->product->track_by_roll)
                                                        <span class="badge badge-info">Fabric Roll</span>
                                                    @endif
                                                </td>
                                                <td>{{ $detail->qty }}</td>
                                                <td>{{ number_format($detail->price, 2) }}</td>
                                                <td>{{ $detail->discount }}%</td>
                                                <td>{{ number_format($detail->amount, 2) }}</td>
                                                <td>
                                                    @if($detail->product->is_fabric && $detail->product->track_by_roll)
                                                        <button type="button" class="btn btn-sm btn-info view-rolls" data-detail-id="{{ $detail->id }}">
                                                            <i class="fa fa-eye"></i> View Rolls
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                            <!-- Fabric rolls will be shown in modal -->
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4" class="text-right">Total:</th>
                                            <th>{{ number_format($purchase->total_amount, 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="row d-print-none mt-2">
                            <div class="col-12 text-right">
                                <a class="btn btn-primary" href="{{ route('purchases.edit', $purchase->id) }}">
                                    <i class="fa fa-edit"></i> Edit
                                </a>
                                <a class="btn btn-danger" href="javascript:void(0);" onclick="confirmDelete('{{ $purchase->id }}')">
                                    <i class="fa fa-trash"></i> Delete
                                </a>
                                <form id="delete-form-{{ $purchase->id }}" action="{{ route('purchases.destroy', $purchase->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                <a class="btn btn-info" href="{{ route('purchases.index') }}">
                                    <i class="fa fa-arrow-left"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('js')
    <script type="text/javascript">
        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this purchase? This will reverse all inventory changes.')) {
                document.getElementById('delete-form-' + id).submit();
            }
        }
        
        // Fabric rolls modal functionality
        $(document).ready(function() {
            $('.view-rolls').on('click', function() {
                var detailId = $(this).data('detail-id');
                var productId = null;
                var purchaseId = {{ $purchase->id }};
                
                // Find the product ID from the table row
                $(this).closest('tr').find('td').each(function(index) {
                    if (index === 0) {
                        // Extract product ID from the first cell (product name)
                        var productName = $(this).text().trim();
                        // We need to find the product ID from the server
                        // For now, we'll use the detail ID
                    }
                });
                
                // Show modal with loading indicator
                $('#fabricRollsModal').modal('show');
                $('#fabricRollsModalBody').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-3x"></i><p class="mt-2">Loading fabric rolls...</p></div>');
                $('#fabricRollsModalTitle').text('Fabric Rolls');
                
                // Load fabric rolls for this purchase detail
                $.ajax({
                    url: '/purchases/' + purchaseId + '/details/' + detailId + '/fabric-rolls',
                    method: 'GET',
                    success: function(response) {
                        var html = '<table class="table table-sm table-bordered">';
                        html += '<thead class="thead-light"><tr>';
                        html += '<th>Roll Number</th>';
                        html += '<th>Width (ft)</th>';
                        html += '<th>Length (ft)</th>';
                        html += '<th>Square Feet</th>';
                        html += '<th>Remaining</th>';
                        html += '<th>Status</th>';
                        html += '</tr></thead><tbody>';
                        
                        if (response.rolls && response.rolls.length > 0) {
                            response.rolls.forEach(function(roll) {
                                html += '<tr>';
                                html += '<td>' + roll.roll_number + '</td>';
                                html += '<td>' + roll.width + '</td>';
                                html += '<td>' + roll.length + '</td>';
                                html += '<td>' + parseFloat(roll.original_square_feet).toFixed(2) + '</td>';
                                html += '<td>' + parseFloat(roll.remaining_square_feet).toFixed(2) + ' (' + parseFloat(roll.remaining_percentage).toFixed(2) + '%)</td>';
                                html += '<td>';
                                
                                if (roll.status === 'active') {
                                    html += '<span class="badge badge-success">Active</span>';
                                } else if (roll.status === 'depleted') {
                                    html += '<span class="badge badge-secondary">Depleted</span>';
                                } else if (roll.status === 'damaged') {
                                    html += '<span class="badge badge-danger">Damaged</span>';
                                }
                                
                                html += '</td>';
                                html += '</tr>';
                            });
                        } else {
                            html += '<tr><td colspan="6" class="text-center">No fabric rolls found</td></tr>';
                        }
                        
                        html += '</tbody></table>';
                        
                        $('#fabricRollsModalBody').html(html);
                    },
                    error: function() {
                        $('#fabricRollsModalBody').html('<div class="alert alert-danger">Error loading fabric rolls</div>');
                    }
                });
            });
        });
    </script>
@endpush

<!-- Fabric Rolls Modal -->
<div class="modal fade" id="fabricRollsModal" tabindex="-1" role="dialog" aria-labelledby="fabricRollsModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fabricRollsModalTitle">Fabric Rolls</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="fabricRollsModalBody">
                <!-- Fabric rolls will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
