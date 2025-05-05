@extends('layouts.master')

@section('title', 'Fabric Inventory Tracking')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-scroll"></i> Fabric Inventory Tracking</h1>
        <p>Track fabric rolls and orders by square feet</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
        <li class="breadcrumb-item">Inventory</li>
        <li class="breadcrumb-item"><a href="{{ route('inventory.tracking.index') }}">Tracking</a></li>
        <li class="breadcrumb-item"><a href="#">Fabric</a></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <div class="tile-title-w-btn">
                <h3 class="title">Fabric Inventory Summary</h3>
                <div class="btn-group">
                    <a class="btn btn-primary" href="{{ route('inventory.tracking.index') }}">
                        <i class="fa fa-arrow-left"></i> Back to Tracking
                    </a>
                </div>
            </div>
            <div class="tile-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="fabricTable">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Active Rolls</th>
                                <th>Total Remaining (sq ft)</th>
                                <th>Ordered (sq ft)</th>
                                <th>Available After Orders (sq ft)</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productsWithRolls as $item)
                            <tr>
                                <td>{{ $item['product']->name }}</td>
                                <td>{{ $item['product']->category->name ?? 'N/A' }}</td>
                                <td>{{ $item['rolls']->count() }}</td>
                                <td>{{ number_format($item['total_remaining'], 2) }}</td>
                                <td>{{ number_format($item['ordered_square_feet'], 2) }}</td>
                                <td>{{ number_format($item['available_after_orders'], 2) }}</td>
                                <td>
                                    @if($item['available_after_orders'] <= 0)
                                        <span class="badge badge-danger">Critical</span>
                                    @elseif($item['available_after_orders'] < ($item['total_remaining'] * 0.2))
                                        <span class="badge badge-warning">Low</span>
                                    @else
                                        <span class="badge badge-success">Good</span>
                                    @endif
                                </td>
                                <td>
                                    <a class="btn btn-info btn-sm" href="{{ route('inventory.tracking.fabric.detail', $item['product']->id) }}">
                                        <i class="fa fa-eye"></i> Details
                                    </a>
                                </td>
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
        $('#fabricTable').DataTable({
            "order": [[ 6, "asc" ]], // Sort by status column
            "columnDefs": [
                { "type": "num", "targets": [2, 3, 4, 5] }
            ]
        });
    });
</script>
@endsection