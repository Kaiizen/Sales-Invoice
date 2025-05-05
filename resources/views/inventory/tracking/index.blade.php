@extends('layouts.master')

@section('title', 'Inventory Tracking')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-boxes"></i> Inventory Tracking</h1>
        <p>Track inventory and orders simultaneously</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
        <li class="breadcrumb-item">Inventory</li>
        <li class="breadcrumb-item"><a href="#">Tracking</a></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="tile">
            <h3 class="tile-title">Fabric Inventory (Square Feet)</h3>
            <div class="tile-body">
                <p>Track fabric rolls based on square feet with real-time order tracking.</p>
                <ul>
                    @foreach($fabricCategories as $category)
                    <li>
                        <strong>{{ $category->name }}</strong>
                        <ul>
                            @foreach($category->product as $product)
                            <li>
                                <a href="{{ route('inventory.tracking.fabric.detail', $product->id) }}">
                                    {{ $product->name }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </li>
                    @endforeach
                </ul>
            </div>
            <div class="tile-footer">
                <a class="btn btn-primary" href="{{ route('inventory.tracking.fabric') }}">
                    <i class="fa fa-fw fa-lg fa-check-circle"></i>View All Fabric Inventory
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="tile">
            <h3 class="tile-title">Product Inventory (Quantity)</h3>
            <div class="tile-body">
                <p>Track products like flag stands by quantity with real-time order tracking.</p>
                <ul>
                    @foreach($quantityCategories as $category)
                    <li>
                        <strong>{{ $category->name }}</strong>
                        <ul>
                            @foreach($category->product as $product)
                            <li>
                                <a href="{{ route('inventory.tracking.quantity.detail', $product->id) }}">
                                    {{ $product->name }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </li>
                    @endforeach
                </ul>
            </div>
            <div class="tile-footer">
                <a class="btn btn-primary" href="{{ route('inventory.tracking.quantity') }}">
                    <i class="fa fa-fw fa-lg fa-check-circle"></i>View All Product Inventory
                </a>
            </div>
        </div>
    </div>
</div>
@endsection