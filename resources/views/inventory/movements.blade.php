@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3>Inventory Movements</h3>
                        <a href="{{ route('inventory.dashboard') }}" class="btn btn-secondary">
                            Back to Dashboard
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if($movements->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Product</th>
                                        <th>Movement Type</th>
                                        <th>Quantity</th>
                                        <th>User</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($movements as $movement)
                                        <tr>
                                            <td>{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                                            <td>{{ $movement->product->name }}</td>
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
                            No inventory movements found.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection