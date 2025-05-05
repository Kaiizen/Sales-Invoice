@extends('layouts.master')

@section('content')
<div class="container">
    <h2>Create New Sale</h2>
    <form method="POST" action="{{ route('sales.store') }}">
        @csrf

        <div class="form-group">
            <label for="customer_id">Customer *</label>
            <select name="customer_id" id="customer_id" class="form-control" required>
                <option value="">Select Customer</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>

        <div id="customer-details" class="card mb-3" style="display: none;">
            <div class="card-body">
                <h5 class="card-title">Customer Details</h5>
                <p class="card-text"><strong>Address:</strong> <span id="customer-address"></span></p>
                <p class="card-text"><strong>Phone:</strong> <span id="customer-phone"></span></p>
                <p class="card-text"><strong>Email:</strong> <span id="customer-email"></span></p>
                <p class="card-text"><strong>Details:</strong> <span id="customer-details-text"></span></p>
            </div>
        </div>

        <div class="form-group">
            <label for="product_id">Product *</label>
            <select name="product_id" id="product_id" class="form-control" required>
                <option value="">Select Product</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="qty">Quantity *</label>
            <input type="number" name="qty" id="qty" class="form-control" required min="1">
        </div>

        <div class="form-group">
            <label for="price">Price *</label>
            <input type="number" step="0.01" name="price" id="price" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="dis">Discount (%)</label>
            <input type="number" step="0.01" name="dis" id="dis" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Create Sale</button>
    </form>
</div>
@endsection