@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Adjust Stock: {{ $product->name }}</h3>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('inventory.adjust') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="current_stock" class="col-md-4 col-form-label text-md-right">
                                Current Stock
                            </label>
                            <div class="col-md-6">
                                <input id="current_stock" type="text" class="form-control" 
                                    value="{{ $product->current_stock }}" disabled>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="quantity" class="col-md-4 col-form-label text-md-right">
                                Adjustment Quantity
                            </label>
                            <div class="col-md-6">
                                <input id="quantity" type="number" class="form-control @error('quantity') is-invalid @enderror" 
                                    name="quantity" required autofocus>
                                
                                <small class="form-text text-muted">
                                    Use positive numbers to add stock, negative to remove
                                </small>

                                @error('quantity')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="notes" class="col-md-4 col-form-label text-md-right">
                                Notes
                            </label>
                            <div class="col-md-6">
                                <textarea id="notes" class="form-control @error('notes') is-invalid @enderror" 
                                    name="notes" rows="3"></textarea>

                                @error('notes')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    Save Adjustment
                                </button>
                                <a href="{{ route('inventory.dashboard') }}" class="btn btn-secondary">
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