@extends('layouts.master')
@section('title', 'Manage Custom Orders')

@section('content')
<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="fa fa-shopping-cart"></i> Custom Orders</h1>
            <p>Manage all custom orders</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item"><a href="{{ route('custom-orders.index') }}">Custom Orders</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <div class="tile-title-w-btn">
                    <h3 class="title">All Custom Orders</h3>
                    <div class="btn-group">
                        <a class="btn btn-primary icon-btn" href="{{ route('custom-orders.create') }}">
                            <i class="fa fa-plus"></i>Create New Order
                        </a>
                    </div>
                </div>

                <div class="tile-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                        
                        <!-- Success Modal -->
                        <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header bg-success text-white">
                                        <h5 class="modal-title" id="successModalLabel"><i class="fa fa-check-circle"></i> Success!</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="text-center mb-4">
                                            <i class="fa fa-check-circle text-success" style="font-size: 64px;"></i>
                                        </div>
                                        <h4 class="text-center">{{ session('success') }}</h4>
                                        <p class="text-center mt-3">Your order has been successfully created and is now visible in the list below.</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-success" data-dismiss="modal">OK</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Script to show the modal -->
                        <script>
                            $(document).ready(function() {
                                $('#successModal').modal('show');
                            });
                        </script>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="sampleTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Product Type</th>
                                    <th colspan="2" class="text-center" style="background-color: #f0f0f0; border-bottom: 2px solid #999;">
                                        <div class="d-flex justify-content-center align-items-center">
                                            <span>Quantity</span>
                                            <i class="fa fa-info-circle ml-1" data-toggle="tooltip" title="Orders can include both finished products and raw fabric materials"></i>
                                        </div>
                                    </th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Actions</th>
                                </tr>
                                <tr>
                                    <th colspan="3"></th>
                                    <th class="text-center" style="background-color: #e8f4f8; border-bottom: 2px solid #4dabf7;">
                                        <div class="d-flex justify-content-center align-items-center">
                                            <i class="fa fa-shopping-bag mr-1 text-primary"></i>
                                            <span>Products</span>
                                        </div>
                                    </th>
                                    <th class="text-center" style="background-color: #e8f8e8; border-bottom: 2px solid #4daf7a;">
                                        <div class="d-flex justify-content-center align-items-center">
                                            <i class="fa fa-flag mr-1 text-success"></i>
                                            <span>Flags</span>
                                        </div>
                                    </th>
                                    <th colspan="4"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr>
                                        <td>{{ $order->id }}</td>
                                        <td>{{ $order->customer ? $order->customer->name : 'N/A' }}</td>
                                        <td>
                                            @if($order->flagDetails->count() > 0)
                                                @php
                                                    $types = [];
                                                    foreach($order->flagDetails as $detail) {
                                                        if (!in_array($detail->flag_type, $types)) {
                                                            $types[] = $detail->flag_type;
                                                        }
                                                    }
                                                @endphp
                                                {{ implode(', ', $types) }}
                                            @else
                                                {{ $order->flag_type }}
                                            @endif
                                        </td>
                                        <td class="text-center" style="background-color: #f8fbff;">
                                            @php
                                                $productQuantity = 0;
                                                $productDetails = [];
                                                
                                                if($order->flagDetails->count() > 0) {
                                                    foreach($order->flagDetails as $detail) {
                                                        if($detail->flag_type != 'flag') {
                                                            $productQuantity += $detail->quantity;
                                                            $productDetails[] = [
                                                                'id' => $detail->id,
                                                                'product_name' => $detail->product ? $detail->product->name : 'Unknown',
                                                                'quantity' => $detail->quantity
                                                            ];
                                                        }
                                                    }
                                                }
                                            @endphp
                                            
                                            @if($order->flagDetails->count() > 0)
                                                @if($productQuantity > 0)
                                                    <span class="badge badge-primary" data-toggle="tooltip"
                                                          title="{{ count($productDetails) }} different product(s)">
                                                        {{ $productQuantity }}
                                                    </span>
                                                    <div class="small text-muted mt-1">
                                                        @foreach($productDetails as $detail)
                                                            <div>
                                                                <a href="{{ route('custom-orders.show', $order->id) }}#product-{{ $detail['id'] }}"
                                                                   class="text-primary">
                                                                    {{ $detail['product_name'] }}:
                                                                    <span class="badge badge-light">{{ $detail['quantity'] }} units</span>
                                                                </a>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="badge badge-primary">0</span>
                                                @endif
                                            @else
                                                @if($order->flag_type == 'product' || $order->flag_type != 'flag')
                                                    <span class="badge badge-primary">{{ $order->quantity }}</span>
                                                @else
                                                    <span class="badge badge-primary">0</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="text-center" style="background-color: #f8fff8;">
                                            @php
                                                $fabricQuantity = 0;
                                                $fabricSqFt = 0;
                                                $fabricDetails = [];
                                                
                                                if($order->flagDetails->count() > 0) {
                                                    foreach($order->flagDetails as $detail) {
                                                        if($detail->flag_type == 'flag') {
                                                            $fabricQuantity += $detail->quantity;
                                                            $fabricSqFt += ($detail->square_feet * $detail->quantity);
                                                            $fabricDetails[] = [
                                                                'id' => $detail->id,
                                                                'fabric_name' => $detail->product ? $detail->product->name : 'Unknown',
                                                                'quantity' => $detail->quantity,
                                                                'dimensions' => $detail->height . ' × ' . $detail->breadth,
                                                                'square_feet' => $detail->square_feet
                                                            ];
                                                        }
                                                    }
                                                }
                                            @endphp
                                            
                                            @if($order->flagDetails->count() > 0)
                                                @if($fabricQuantity > 0)
                                                    <span class="badge badge-success" data-toggle="tooltip"
                                                          title="{{ number_format($fabricSqFt, 2) }} sq.ft total">
                                                        {{ $fabricQuantity }}
                                                    </span>
                                                    <div class="small text-muted mt-1">
                                                        @foreach($fabricDetails as $detail)
                                                            <div>
                                                                <a href="{{ route('custom-orders.show', $order->id) }}#flag-{{ $detail['id'] }}"
                                                                   class="text-info">
                                                                    <strong>{{ $detail['fabric_name'] }}</strong>: {{ $detail['quantity'] }}
                                                                    <span class="badge badge-light">{{ $detail['dimensions'] }} ft</span>
                                                                    <span class="badge badge-secondary">{{ number_format($detail['square_feet'], 2) }} sq.ft</span>
                                                                </a>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="badge badge-success">0</span>
                                                @endif
                                            @else
                                                @if($order->flag_type == 'flag')
                                                    <span class="badge badge-success" data-toggle="tooltip" title="{{ number_format($order->square_feet * $order->quantity, 2) }} sq.ft">
                                                        {{ $order->quantity }}
                                                    </span>
                                                    <div class="small text-muted mt-1">
                                                        <div>
                                                            <span class="badge badge-light">{{ $order->height }} × {{ $order->breadth }} ft</span>
                                                            <span class="badge badge-secondary">{{ number_format($order->square_feet, 2) }} sq.ft</span>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="badge badge-success">0</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge
                                                @if($order->status == 'Pending') badge-warning
                                                @elseif($order->status == 'In Progress') badge-info
                                                @elseif($order->status == 'Completed') badge-success
                                                @elseif($order->status == 'Cancelled') badge-danger
                                                @else badge-secondary
                                                @endif
                                            ">
                                                {{ $order->status }}
                                            </span>
                                        </td>
                                        <td>{{ $order->created_at->setTimezone('Asia/Katmandu')->format('Y-m-d') }}</td>
                                        <td>{{ $order->created_at->setTimezone('Asia/Katmandu')->format('H:i:s') }}</td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('custom-orders.show', $order) }}" class="btn btn-sm btn-info">View</a>
                                                <a href="{{ route('custom-orders.edit', $order) }}" class="btn btn-sm btn-primary">Edit</a>
                                                <form action="{{ route('custom-orders.destroy', $order->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this order?')">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ Auth::user()->isAdmin() ? '8' : '7' }}" class="text-center">
                                            No custom orders found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script type="text/javascript" src="{{ asset('js/plugins/jquery.dataTables.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/plugins/dataTables.bootstrap.min.js') }}"></script>
<script type="text/javascript">$('#sampleTable').DataTable();</script>
<script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        // Initialize DataTable
        $('#sampleTable').DataTable();
        
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endpush