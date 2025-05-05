@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3>Low Stock Report</h3>
                        <a href="{{ route('inventory.dashboard') }}" class="btn btn-secondary">
                            Back to Dashboard
                        </a>
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
                                        <th>Current Stock</th>
                                        <th>Minimum Stock</th>
                                        <th>Difference</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                        <tr>
                                            <td>{{ $product->name }}</td>
                                            <td>{{ $product->category->name ?? 'N/A' }}</td>
                                            <td>{{ $product->current_stock }}</td>
                                            <td>{{ $product->minimum_stock }}</td>
                                            <td>{{ $product->minimum_stock - $product->current_stock }}</td>
                                            <td>
                                                <span class="badge badge-warning">Low Stock</span>
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
                            No products with low stock levels.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection