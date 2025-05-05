@extends('layouts.master')

@section('title', 'Fabric Products')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-scissors"></i> Fabric Products</h1>
        <p>Manage fabric products and rolls</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
        <li class="breadcrumb-item">Products</li>
        <li class="breadcrumb-item"><a href="#">Fabric Products</a></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <div class="tile-title-w-btn">
                <h3 class="title">All Fabric Products</h3>
                <div class="btn-group">
                    <a class="btn btn-primary" href="{{ route('products.create') }}?type=fabric">
                        <i class="fa fa-plus"></i> Add New Fabric
                    </a>
                </div>
            </div>
            <div class="tile-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="fabricTable">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Total Square Feet</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            <tr>
                                <td>
                                    <img src="{{ asset('images/product/' . $product->image) }}" alt="{{ $product->name }}" width="50">
                                </td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->category->name ?? 'N/A' }}</td>
                                <td>{{ number_format($product->total_square_feet, 2) }} sq ft</td>
                                <td>
                                    @if($product->stock_status == 'out_of_stock')
                                        <span class="badge badge-danger">Out of Stock</span>
                                    @elseif($product->stock_status == 'low_stock')
                                        <span class="badge badge-warning">Low Stock</span>
                                    @else
                                        <span class="badge badge-success">In Stock</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a class="btn btn-info btn-sm" href="{{ route('inventory.tracking.fabric.detail', $product->id) }}">
                                            <i class="fa fa-eye"></i> View Rolls
                                        </a>
                                        <a class="btn btn-primary btn-sm" href="{{ route('products.edit', $product->id) }}">
                                            <i class="fa fa-edit"></i> Edit
                                        </a>
                                        <button class="btn btn-danger btn-sm" onclick="confirmDelete('{{ $product->id }}')">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                    </div>
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this fabric product? This will also delete all associated fabric rolls.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        $('#fabricTable').DataTable();
    });
    
    function confirmDelete(id) {
        $('#deleteForm').attr('action', '{{ url("products") }}/' + id);
        $('#deleteModal').modal('show');
    }
</script>
@endsection