@extends('layouts.master')

@section('title', 'Product Detail')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-box"></i> Product Detail: {{ $product->name }}</h1>
        <p>Detailed view of product inventory and orders</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
        <li class="breadcrumb-item">Inventory</li>
        <li class="breadcrumb-item"><a href="{{ route('inventory.tracking.index') }}">Tracking</a></li>
        <li class="breadcrumb-item"><a href="{{ route('inventory.tracking.quantity') }}">Quantity</a></li>
        <li class="breadcrumb-item"><a href="#">{{ $product->name }}</a></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <div class="tile-title-w-btn">
                <h3 class="title">Product Summary</h3>
                <div class="btn-group">
                    <a class="btn btn-primary" href="{{ route('inventory.tracking.quantity') }}">
                        <i class="fa fa-arrow-left"></i> Back to Product List
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
                            <tr>
                                <th>Unit</th>
                                <td>{{ $product->unit->name ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div class="widget-small primary coloured-icon">
                            <i class="icon fa fa-cubes fa-3x"></i>
                            <div class="info">
                                <h4>Current Stock</h4>
                                <p><b>{{ $currentStock }} {{ $product->unit->name ?? 'units' }}</b></p>
                            </div>
                        </div>
                        <div class="widget-small warning coloured-icon">
                            <i class="icon fa fa-shopping-cart fa-3x"></i>
                            <div class="info">
                                <h4>Ordered</h4>
                                <p><b>{{ $orderedQuantity }} {{ $product->unit->name ?? 'units' }}</b></p>
                            </div>
                        </div>
                        <div class="widget-small {{ ($currentStock - $orderedQuantity) <= 0 ? 'danger' : 'info' }} coloured-icon">
                            <i class="icon fa fa-balance-scale fa-3x"></i>
                            <div class="info">
                                <h4>Available After Orders</h4>
                                <p><b>{{ $currentStock - $orderedQuantity }} {{ $product->unit->name ?? 'units' }}</b></p>
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
                <h3 class="title">Pending Orders ({{ $pendingOrderItems->count() }})</h3>
            </div>
            <div class="tile-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="pendingOrdersTable">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Discount</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingOrderItems as $item)
                            <tr>
                                <td>
                                    <a href="{{ route('invoices.show', $item->invoice_id) }}">
                                        #{{ $item->invoice->invoice_number ?? $item->invoice_id }}
                                    </a>
                                </td>
                                <td>{{ $item->invoice->customer->name ?? 'N/A' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->price, 2) }}</td>
                                <td>{{ number_format($item->discount, 2) }}%</td>
                                <td>{{ number_format($item->quantity * $item->price * (1 - $item->discount/100), 2) }}</td>
                                <td>{{ $item->invoice->date ?? $item->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <span class="badge badge-warning">
                                        {{ $item->invoice->status ?? 'Pending' }}
                                    </span>
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

<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <div class="tile-title">
                <h3 class="title">Inventory Movements</h3>
            </div>
            <div class="tile-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="movementsTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>User</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($movements as $movement)
                            <tr>
                                <td>{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <span class="badge badge-{{ $movement->movement_type == 'purchase' ? 'success' : ($movement->movement_type == 'sale' ? 'danger' : 'info') }}">
                                        {{ $movement->movement_type_name }}
                                    </span>
                                </td>
                                <td>{{ $movement->quantity }}</td>
                                <td>{{ $movement->user->name ?? 'System' }}</td>
                                <td>{{ $movement->notes }}</td>
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
                <h3 class="title">Completed Orders ({{ $completedOrderItems->count() }})</h3>
            </div>
            <div class="tile-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="completedOrdersTable">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($completedOrderItems as $item)
                            <tr>
                                <td>
                                    <a href="{{ route('invoices.show', $item->invoice_id) }}">
                                        #{{ $item->invoice->invoice_number ?? $item->invoice_id }}
                                    </a>
                                </td>
                                <td>{{ $item->invoice->customer->name ?? 'N/A' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->price, 2) }}</td>
                                <td>{{ number_format($item->quantity * $item->price * (1 - $item->discount/100), 2) }}</td>
                                <td>{{ $item->invoice->date ?? $item->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <span class="badge badge-success">
                                        {{ $item->invoice->status ?? 'Completed' }}
                                    </span>
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
        $('#pendingOrdersTable').DataTable({
            "order": [[ 6, "desc" ]] // Sort by date
        });
        
        $('#movementsTable').DataTable({
            "order": [[ 0, "desc" ]] // Sort by date
        });
        
        $('#completedOrdersTable').DataTable({
            "order": [[ 5, "desc" ]] // Sort by date
        });
    });
</script>
@endsection