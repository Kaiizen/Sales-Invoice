@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3>Bulk Stock Adjustment</h3>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('inventory.bulk-adjust') }}">
                        @csrf

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Current Stock</th>
                                        <th>Adjustment</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                        <tr>
                                            <td>{{ $product->name }}</td>
                                            <td>{{ $product->current_stock }}</td>
                                            <td>
                                                <input type="hidden" name="adjustments[{{ $loop->index }}][product_id]" 
                                                    value="{{ $product->id }}">
                                                <input type="number" class="form-control" 
                                                    name="adjustments[{{ $loop->index }}][quantity]" 
                                                    value="0" step="1">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" 
                                                    name="adjustments[{{ $loop->index }}][notes]">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="form-group row mb-0 mt-4">
                            <div class="col-md-6 offset-md-3 text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    Save All Adjustments
                                </button>
                                <a href="{{ route('inventory.dashboard') }}" class="btn btn-secondary btn-lg">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection