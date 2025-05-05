@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3>Manage Inventory: {{ $product->name }}</h3>
                    <div>
                        <a href="{{ route('inventory.product.index') }}" class="btn btn-secondary">Back to Products</a>
                        <a href="{{ route('product.show', $product->id) }}" class="btn btn-info">View Product Details</a>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Product Information</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Category:</strong> {{ $product->category->name }}</p>
                                    <p><strong>Unit:</strong> {{ $product->unit->name }}</p>
                                    <p><strong>Current Stock:</strong> {{ $product->current_stock }}</p>
                                    <p><strong>Minimum Stock:</strong> {{ $product->minimum_stock }}</p>
                                    <p>
                                        <strong>Status:</strong>
                                        @if($product->stock_status == 'out_of_stock')
                                            <span class="badge bg-danger">Out of Stock</span>
                                        @elseif($product->stock_status == 'low_stock')
                                            <span class="badge bg-warning">Low Stock</span>
                                        @else
                                            <span class="badge bg-success">In Stock</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Inventory Actions</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="nav nav-tabs" id="inventoryTabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="add-tab" data-bs-toggle="tab" data-bs-target="#add" type="button" role="tab" aria-controls="add" aria-selected="true">Add Stock</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="remove-tab" data-bs-toggle="tab" data-bs-target="#remove" type="button" role="tab" aria-controls="remove" aria-selected="false">Remove Stock</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="transfer-tab" data-bs-toggle="tab" data-bs-target="#transfer" type="button" role="tab" aria-controls="transfer" aria-selected="false">Transfer Stock</button>
                                        </li>
                                    </ul>
                                    <div class="tab-content p-3" id="inventoryTabsContent">
                                        <div class="tab-pane fade show active" id="add" role="tabpanel" aria-labelledby="add-tab">
                                            <form action="{{ route('inventory.product.add-stock', $product->id) }}" method="POST">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="add_quantity" class="form-label">Quantity to Add</label>
                                                    <input type="number" class="form-control" id="add_quantity" name="quantity" min="1" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="add_location_id" class="form-label">Location (Optional)</label>
                                                    <select class="form-select" id="add_location_id" name="location_id">
                                                        <option value="">No specific location</option>
                                                        @foreach($locations as $location)
                                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="add_notes" class="form-label">Notes</label>
                                                    <textarea class="form-control" id="add_notes" name="notes" rows="2"></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-success">Add Stock</button>
                                            </form>
                                        </div>
                                        <div class="tab-pane fade" id="remove" role="tabpanel" aria-labelledby="remove-tab">
                                            <form action="{{ route('inventory.product.remove-stock', $product->id) }}" method="POST">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="remove_quantity" class="form-label">Quantity to Remove</label>
                                                    <input type="number" class="form-control" id="remove_quantity" name="quantity" min="1" max="{{ $product->current_stock }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="remove_location_id" class="form-label">Location (Optional)</label>
                                                    <select class="form-select" id="remove_location_id" name="location_id">
                                                        <option value="">No specific location</option>
                                                        @foreach($locations as $location)
                                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="remove_notes" class="form-label">Notes</label>
                                                    <textarea class="form-control" id="remove_notes" name="notes" rows="2"></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-danger">Remove Stock</button>
                                            </form>
                                        </div>
                                        <div class="tab-pane fade" id="transfer" role="tabpanel" aria-labelledby="transfer-tab">
                                            <form action="{{ route('inventory.product.transfer-stock', $product->id) }}" method="POST">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="transfer_quantity" class="form-label">Quantity to Transfer</label>
                                                    <input type="number" class="form-control" id="transfer_quantity" name="quantity" min="1" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="from_location_id" class="form-label">From Location</label>
                                                    <select class="form-select" id="from_location_id" name="from_location_id" required>
                                                        <option value="">Select location</option>
                                                        @foreach($locations as $location)
                                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="to_location_id" class="form-label">To Location</label>
                                                    <select class="form-select" id="to_location_id" name="to_location_id" required>
                                                        <option value="">Select location</option>
                                                        @foreach($locations as $location)
                                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="transfer_notes" class="form-label">Notes</label>
                                                    <textarea class="form-control" id="transfer_notes" name="notes" rows="2"></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Transfer Stock</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5>Recent Stock Movements</h5>
                        </div>
                        <div class="card-body">
                            @if($movements->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Movement</th>
                                                <th>Quantity</th>
                                                <th>User</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($movements as $movement)
                                                <tr>
                                                    <td>{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                                                    <td>{{ $movement->movement_type_name }}</td>
                                                    <td class="{{ $movement->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                                        {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                                                    </td>
                                                    <td>{{ $movement->user->name ?? 'System' }}</td>
                                                    <td>{{ $movement->notes }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $movements->links() }}
                                </div>
                            @else
                                <div class="alert alert-info">
                                    No stock movements found for this product.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection