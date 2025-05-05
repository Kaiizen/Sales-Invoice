@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3>Out of Stock Report</h3>
                        <div>
                            <a href="{{ route('inventory.low-stock') }}" class="btn btn-warning">
                                View Low Stock
                            </a>
                            <a href="{{ route('inventory.dashboard') }}" class="btn btn-secondary">
                                Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if($products->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Last Stock Date</th>
                                        <th>Days Out of Stock</th>
                                        <th>Supplier</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                        <tr>
                                            <td>{{ $product->name }}</td>
                                            <td>{{ $product->category->name ?? 'N/A' }}</td>
                                            <td>{{ $product->last_stock_date ? $product->last_stock_date->format('Y-m-d') : 'Never' }}</td>
                                            <td>{{ $product->days_out_of_stock }}</td>
                                            <td>{{ $product->supplier->name ?? 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('inventory.adjust', $product) }}" 
                                                   class="btn btn-sm btn-primary">
                                                    Restock
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $products->links() }}
                        </div>
                    @else
                        <div class="alert alert-success">
                            No products are completely out of stock.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection