@extends('layouts.master')

@section('title', 'Fabric Detail')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-scroll"></i> Fabric Detail: {{ $product->name }}</h1>
        <p>Detailed view of fabric inventory and orders</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
        <li class="breadcrumb-item">Inventory</li>
        <li class="breadcrumb-item"><a href="{{ route('inventory.tracking.index') }}">Tracking</a></li>
        <li class="breadcrumb-item"><a href="{{ route('inventory.tracking.fabric') }}">Fabric</a></li>
        <li class="breadcrumb-item"><a href="#">{{ $product->name }}</a></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <div class="tile-title-w-btn">
                <h3 class="title">Fabric Summary</h3>
                <div class="btn-group">
                    <a class="btn btn-primary" href="{{ route('inventory.tracking.fabric') }}">
                        <i class="fa fa-arrow-left"></i> Back to Fabric List
                    </a>
                </div>
            </div>
            <div class="tile-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th>Product Name</th>
                                <td>{{ $product->name }}</td>
                            </tr>
                            <tr>
                                <th>Category</th>
                                <td>{{ $product->category->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>SKU</th>
                                <td>{{ $product->sku }}</td>
                            </tr>
                            <tr>
                                <th>Description</th>
                                <td>{{ $product->description }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div class="widget-small primary coloured-icon">
                            <i class="icon fa fa-ruler fa-3x"></i>
                            <div class="info">
                                <h4>Total Remaining</h4>
                                <p><b>{{ number_format($totalRemainingSquareFeet, 2) }} sq ft</b></p>
                            </div>
                        </div>
                        <div class="widget-small warning coloured-icon">
                            <i class="icon fa fa-shopping-cart fa-3x"></i>
                            <div class="info">
                                <h4>Ordered</h4>
                                <p><b>{{ number_format($orderedSquareFeet, 2) }} sq ft</b></p>
                            </div>
                        </div>
                        <div class="widget-small {{ ($totalRemainingSquareFeet - $orderedSquareFeet) <= 0 ? 'danger' : 'info' }} coloured-icon">
                            <i class="icon fa fa-balance-scale fa-3x"></i>
                            <div class="info">
                                <h4>Available After Orders</h4>
                                <p><b>{{ number_format($totalRemainingSquareFeet - $orderedSquareFeet, 2) }} sq ft</b></p>
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
            <div class="tile-title">
                <h3 class="title">Active Fabric Rolls ({{ $activeRolls->count() }})</h3>
            </div>
            <div class="tile-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="activeRollsTable">
                        <thead>
                            <tr>
                                <th>Roll Number</th>
                                <th>Width (ft)</th>
                                <th>Length (ft)</th>
                                <th>Original (sq ft)</th>
                                <th>Remaining (sq ft)</th>
                                <th>Used %</th>
                                <th>Location</th>
                                <th>Received Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeRolls as $roll)
                            @php
                                $usedPercentage = number_format(100 - $roll->remaining_percentage, 0);
                            @endphp
                            <tr>
                                <td>{{ $roll->roll_number }}</td>
                                <td>{{ $roll->width }}</td>
                                <td>{{ $roll->length }}</td>
                                <td>{{ number_format($roll->original_square_feet, 2) }}</td>
                                <td>{{ number_format($roll->remaining_square_feet, 2) }}</td>
                                <td>
                                    {{ $usedPercentage }}%
                                </td>
                                <td>{{ $roll->location->name ?? 'N/A' }}</td>
                                <td>{{ $roll->received_date->format('Y-m-d') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <div class="tile-title">
                <h3 class="title">Pending Orders ({{ $pendingOrders->count() }})</h3>
            </div>
            <div class="tile-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="pendingOrdersTable">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Flag Type</th>
                                <th>Dimensions</th>
                                <th>Square Feet</th>
                                <th>Quantity</th>
                                <th>Total Square Feet</th>
                                <th>Status</th>
                                <th>Created Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingOrders as $detail)
                            <tr>
                                <td>
                                    <a href="{{ route('custom-orders.show', $detail->custom_order_id) }}">
                                        #{{ $detail->custom_order_id }}
                                    </a>
                                </td>
                                <td>{{ $detail->customOrder->customer->name ?? 'N/A' }}</td>
                                <td>{{ $detail->flag_type }}</td>
                                <td>{{ $detail->height }} x {{ $detail->breadth }}</td>
                                <td>{{ number_format($detail->square_feet, 2) }}</td>
                                <td>{{ $detail->quantity }}</td>
                                <td>{{ number_format($detail->square_feet * $detail->quantity, 2) }}</td>
                                <td>
                                    <span class="badge badge-{{ $detail->customOrder->status == 'Pending' ? 'warning' : 'primary' }}">
                                        {{ $detail->customOrder->status }}
                                    </span>
                                </td>
                                <td>{{ $detail->created_at->format('Y-m-d') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="tile">
            <div class="tile-title">
                <h3 class="title">Depleted Rolls ({{ $depletedRolls->count() }})</h3>
            </div>
            <div class="tile-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="depletedRollsTable">
                        <thead>
                            <tr>
                                <th>Roll Number</th>
                                <th>Original (sq ft)</th>
                                <th>Received Date</th>
                                <th>Depleted Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($depletedRolls as $roll)
                            <tr>
                                <td>{{ $roll->roll_number }}</td>
                                <td>{{ number_format($roll->original_square_feet, 2) }}</td>
                                <td>{{ $roll->received_date->format('Y-m-d') }}</td>
                                <td>{{ $roll->updated_at->format('Y-m-d') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="tile">
            <div class="tile-title">
                <h3 class="title">Damaged Rolls ({{ $damagedRolls->count() }})</h3>
            </div>
            <div class="tile-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="damagedRollsTable">
                        <thead>
                            <tr>
                                <th>Roll Number</th>
                                <th>Original (sq ft)</th>
                                <th>Remaining (sq ft)</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($damagedRolls as $roll)
                            <tr>
                                <td>{{ $roll->roll_number }}</td>
                                <td>{{ number_format($roll->original_square_feet, 2) }}</td>
                                <td>{{ number_format($roll->remaining_square_feet, 2) }}</td>
                                <td>{{ $roll->notes }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <div class="tile-title">
                <h3 class="title">Completed Orders ({{ $completedOrders->count() }})</h3>
            </div>
            <div class="tile-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="completedOrdersTable">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Flag Type</th>
                                <th>Square Feet</th>
                                <th>Quantity</th>
                                <th>Total Square Feet</th>
                                <th>Status</th>
                                <th>Completed Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($completedOrders as $detail)
                            <tr>
                                <td>
                                    <a href="{{ route('custom-orders.show', $detail->custom_order_id) }}">
                                        #{{ $detail->custom_order_id }}
                                    </a>
                                </td>
                                <td>{{ $detail->customOrder->customer->name ?? 'N/A' }}</td>
                                <td>{{ $detail->flag_type }}</td>
                                <td>{{ number_format($detail->square_feet, 2) }}</td>
                                <td>{{ $detail->quantity }}</td>
                                <td>{{ number_format($detail->square_feet * $detail->quantity, 2) }}</td>
                                <td>
                                    <span class="badge badge-success">
                                        {{ $detail->customOrder->status }}
                                    </span>
                                </td>
                                <td>{{ $detail->customOrder->updated_at->format('Y-m-d') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        $('#activeRollsTable').DataTable({
            "order": [[ 7, "desc" ]] // Sort by received date
        });
        
        $('#pendingOrdersTable').DataTable({
            "order": [[ 8, "desc" ]] // Sort by created date
        });
        
        $('#depletedRollsTable').DataTable({
            "order": [[ 3, "desc" ]] // Sort by depleted date
        });
        
        $('#damagedRollsTable').DataTable();
        
        $('#completedOrdersTable').DataTable({
            "order": [[ 7, "desc" ]] // Sort by completed date
        });
    });
</script>
@endsection