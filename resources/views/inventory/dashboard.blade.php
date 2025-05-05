@extends('layouts.master')
@section('title', 'Inventory Dashboard')

@section('content')
<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="fa fa-dashboard"></i> Inventory Dashboard</h1>
            <p>Monitor and manage your inventory</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item">Inventory</li>
            <li class="breadcrumb-item active">Dashboard</li>
        </ul>
    </div>

    @if(session()->has('success'))
    <div class="alert alert-success">
        {{ session()->get('success') }}
    </div>
    @endif

    @if(session()->has('error'))
    <div class="alert alert-danger">
        {{ session()->get('error') }}
    </div>
    @endif

    <!-- Inventory Summary Cards -->
    <div class="row">
        <div class="col-md-6 col-lg-3">
            <div class="widget-small primary coloured-icon">
                <i class="icon fa fa-cubes fa-3x"></i>
                <div class="info">
                    <h4>Total Products</h4>
                    <p><b>{{ App\Product::count() }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small warning coloured-icon">
                <i class="icon fa fa-exclamation-triangle fa-3x"></i>
                <div class="info">
                    <h4>Low Stock</h4>
                    <p><b>{{ $lowStockProducts->count() + $lowStockFabrics->count() }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small danger coloured-icon">
                <i class="icon fa fa-times-circle fa-3x"></i>
                <div class="info">
                    <h4>Out of Stock</h4>
                    <p><b>{{ $outOfStockProducts->count() }}</b></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="widget-small info coloured-icon">
                <i class="icon fa fa-exchange fa-3x"></i>
                <div class="info">
                    <h4>Movements</h4>
                    <p><b>{{ App\InventoryMovement::count() }}</b></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <div class="tile-title-w-btn">
                    <h3 class="title">Quick Actions</h3>
                </div>
                <div class="tile-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('inventory.movements') }}" class="btn btn-primary btn-block mb-2">
                                <i class="fa fa-exchange"></i> View Movements
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('inventory.low-stock') }}" class="btn btn-warning btn-block mb-2">
                                <i class="fa fa-exclamation-triangle"></i> Low Stock Items
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('inventory.out-of-stock') }}" class="btn btn-danger btn-block mb-2">
                                <i class="fa fa-times-circle"></i> Out of Stock Items
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('inventory.report') }}" class="btn btn-info btn-block mb-2">
                                <i class="fa fa-bar-chart"></i> Inventory Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Alerts Section -->
    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <div class="tile-title-w-btn">
                    <h3 class="title"><i class="fa fa-exclamation-triangle text-warning"></i> Inventory Alerts</h3>
                </div>
                <div class="tile-body">
                    @if($lowStockProducts->count() > 0 || $outOfStockProducts->count() > 0 || $lowStockFabrics->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Type</th>
                                        <th>Current Stock</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($outOfStockProducts as $product)
                                        <tr class="bg-danger text-white">
                                            <td>{{ $product->name }}</td>
                                            <td>Regular</td>
                                            <td>{{ $product->current_stock }}</td>
                                            <td><span class="badge badge-danger">Out of Stock</span></td>
                                            <td>
                                                <a href="{{ route('inventory.adjust', $product->id) }}" class="btn btn-sm btn-light">
                                                    <i class="fa fa-edit"></i> Update Stock
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @foreach($lowStockProducts as $product)
                                        <tr class="bg-warning">
                                            <td>{{ $product->name }}</td>
                                            <td>Regular</td>
                                            <td>{{ $product->current_stock }} / {{ $product->minimum_stock }}</td>
                                            <td><span class="badge badge-warning">Low Stock</span></td>
                                            <td>
                                                <a href="{{ route('inventory.adjust', $product->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fa fa-edit"></i> Update Stock
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @foreach($lowStockFabrics as $product)
                                        <tr class="bg-warning">
                                            <td>{{ $product->name }}</td>
                                            <td>Fabric (Roll)</td>
                                            <td>
                                                {{ number_format($product->total_square_feet, 2) }} sq ft
                                                <div class="progress mt-1">
                                                    <div class="progress-bar bg-warning"
                                                        role="progressbar"
                                                        style="width: {{ $product->remaining_square_feet_percentage }}%"
                                                        aria-valuenow="{{ $product->remaining_square_feet_percentage }}"
                                                        aria-valuemin="0"
                                                        aria-valuemax="100">
                                                        {{ number_format($product->remaining_square_feet_percentage, 0) }}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="badge badge-warning">Low Stock</span></td>
                                            <td>
                                                <a href="{{ route('inventory.add-fabric-roll', $product->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fa fa-plus"></i> Add Roll
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-success">
                            <i class="fa fa-check-circle"></i> All inventory levels are normal.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Movements -->
    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <div class="tile-title-w-btn">
                    <h3 class="title"><i class="fa fa-exchange"></i> Recent Inventory Movements</h3>
                    <div class="btn-group">
                        <a class="btn btn-primary" href="{{ route('inventory.movements') }}">
                            <i class="fa fa-list"></i> View All
                        </a>
                    </div>
                </div>
                <div class="tile-body">
                    @if($recentMovements->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Product</th>
                                        <th>Type</th>
                                        <th>Quantity</th>
                                        <th>User</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentMovements as $movement)
                                        <tr>
                                            <td>{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                                            <td>{{ $movement->product->name }}</td>
                                            <td>
                                                <span class="badge badge-{{ $movement->movement_type == 'in' ? 'success' : 'danger' }}">
                                                    {{ $movement->movement_type_name }}
                                                </span>
                                            </td>
                                            <td class="{{ $movement->movement_type == 'in' ? 'text-success' : 'text-danger' }}">
                                                {{ $movement->movement_type == 'in' ? '+' : '-' }}{{ $movement->quantity }}
                                                @if($movement->unit_type == 'square_feet')
                                                    ({{ number_format($movement->amount, 2) }} sq ft)
                                                @endif
                                            </td>
                                            <td>{{ $movement->user->name ?? 'System' }}</td>
                                            <td>{{ $movement->notes }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            No recent inventory movements found.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Movement Summary Chart -->
    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <div class="tile-title">
                    <h3 class="title"><i class="fa fa-bar-chart"></i> Inventory Movement Trends (Last 30 Days)</h3>
                </div>
                <div class="tile-body">
                    <canvas id="movementChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script src="{{ asset('js/plugins/chart.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Movement Chart
        const movementCtx = document.getElementById('movementChart').getContext('2d');
        
        const movementData = @json($movementSummary);
        const labels = movementData.map(item => item.date);
        const inData = movementData.map(item => item.in_count);
        const outData = movementData.map(item => item.out_count);
        
        new Chart(movementCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Stock In',
                    data: inData,
                    borderColor: '#4BC0C0',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    fill: true
                }, {
                    label: 'Stock Out',
                    data: outData,
                    borderColor: '#FF6384',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
@endpush