@extends('layouts.master')
@section('title', 'Custom Orders Kanban')

@section('content')
<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="fa fa-tasks"></i> Custom Orders Kanban</h1>
            <p>View orders by status</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item"><a href="{{ route('custom-orders.index') }}">Custom Orders</a></li>
            <li class="breadcrumb-item">Kanban</li>
        </ul>
    </div>

    <div class="row">
        @foreach(App\CustomOrder::STATUSES as $status)
        <div class="col-md-3">
            <div class="tile">
                <div class="tile-title-w-btn">
                    <h3 class="title">{{ $status }}</h3>
                    <span class="badge badge-{{ $status === 'Pending' ? 'warning' : ($status === 'In Production' ? 'info' : ($status === 'Ready' ? 'success' : 'primary')) }}">
                        {{ $orders->where('status', $status)->count() }}
                    </span>
                </div>
                <div class="tile-body kanban-column">
                    @foreach($orders->where('status', $status) as $order)
                    <div class="kanban-card">
                        <div class="card-header">
                            <h5>Order #{{ $order->id }}</h5>
                            <small>{{ $order->flag_type }}</small>
                        </div>
                        <div class="card-body">
                            <p>Size: {{ $order->size }}</p>
                            <p>Qty: {{ $order->quantity }}</p>
                            <p>Customer: {{ $order->customer ? $order->customer->name : 'N/A' }}</p>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('custom-orders.show', $order) }}" class="btn btn-sm btn-info">View</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>
</main>
@endsection

@push('styles')
<style>
    .kanban-column {
        min-height: 500px;
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 4px;
    }
    .kanban-card {
        background: white;
        border-radius: 4px;
        padding: 10px;
        margin-bottom: 15px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .kanban-card .card-header {
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
        margin-bottom: 10px;
    }
    .kanban-card .card-footer {
        border-top: 1px solid #eee;
        padding-top: 10px;
        margin-top: 10px;
    }
</style>
@endpush