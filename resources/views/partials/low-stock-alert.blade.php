<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <div class="tile-title-w-btn">
                <h3 class="title"><i class="fa fa-exclamation-triangle text-warning"></i> Inventory Alerts</h3>
                <div class="btn-group">
                    <a class="btn btn-primary" href="{{ route('products.index') }}"><i class="fa fa-list"></i> View All Products</a>
                </div>
            </div>
            <div class="tile-body">
                @php
                    $lowStockProducts = App\Product::lowStock()->get();
                    $outOfStockProducts = App\Product::outOfStock()->get();
                    $lowStockFabrics = App\Product::where('is_fabric', true)
                        ->where('track_by_roll', true)
                        ->whereRaw('total_square_feet <= (total_square_feet * (alert_threshold_percent / 100))')
                        ->where('total_square_feet', '>', 0)
                        ->get();
                @endphp

                @if($lowStockProducts->count() > 0 || $outOfStockProducts->count() > 0 || $lowStockFabrics->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Type</th>
                                    <th>Current Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($outOfStockProducts as $product)
                                    <tr class="bg-danger text-white">
                                        <td>{{ $product->name }}</td>
                                        <td>Regular</td>
                                        <td>{{ $product->current_stock }}</td>
                                        <td><span class="badge badge-danger">Out of Stock</span></td>
                                        <td>
                                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-light">
                                                <i class="fa fa-edit"></i> Update Stock
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach

                                @foreach($lowStockProducts as $product)
                                    <tr class="bg-warning">
                                        <td>{{ $product->name }}</td>
                                        <td>Regular</td>
                                        <td>{{ $product->current_stock }} / {{ $product->minimum_stock }}</td>
                                        <td><span class="badge badge-warning">Low Stock</span></td>
                                        <td>
                                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fa fa-edit"></i> Update Stock
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach

                                @foreach($lowStockFabrics as $product)
                                    <tr class="bg-warning">
                                        <td>{{ $product->name }}</td>
                                        <td>Fabric (Roll)</td>
                                        <td>
                                            {{ number_format($product->total_square_feet, 2) }} sq ft
                                            <div class="progress mt-1">
                                                <div class="progress-bar bg-warning" 
                                                    role="progressbar" 
                                                    style="width: {{ $product->remaining_square_feet_percentage }}%"
                                                    aria-valuenow="{{ $product->remaining_square_feet_percentage }}" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100">
                                                    {{ number_format($product->remaining_square_feet_percentage, 0) }}%
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge badge-warning">Low Stock</span></td>
                                        <td>
                                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fa fa-edit"></i> Add Roll
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-success">
                        <i class="fa fa-check-circle"></i> All inventory levels are normal.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>