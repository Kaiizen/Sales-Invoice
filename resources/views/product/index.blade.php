@extends('layouts.master')

@section('title', 'Product | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-th-list"></i> Product Table</h1>
            </div>
            <ul class="app-breadcrumb breadcrumb side">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Product</li>
                <li class="breadcrumb-item active"><a href="#">Product Table</a></li>
            </ul>
        </div>
        <div class="mb-3">
            <a class="btn btn-primary" href="{{route('products.create')}}"><i class="fa fa-plus"></i> Add Product</a>
            <a class="btn btn-success" href="{{route('fabric-rolls.create')}}"><i class="fa fa-plus"></i> Add Fabric Roll</a>
        </div>
        
        <ul class="nav nav-tabs mb-3" id="productTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="regular-products-tab" data-toggle="tab" href="#regular-products" role="tab" aria-controls="regular-products" aria-selected="true">
                    <i class="fa fa-cubes"></i> Regular Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="fabric-rolls-tab" data-toggle="tab" href="#fabric-rolls" role="tab" aria-controls="fabric-rolls" aria-selected="false">
                    <i class="fa fa-scroll"></i> Fabric Rolls
                </a>
            </li>
        </ul>

        <!-- Inventory Alerts Section -->
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-title-w-btn">
                        <h3 class="title"><i class="fa fa-exclamation-triangle text-warning"></i> Inventory Alerts</h3>
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
                                                    <button class="btn btn-sm btn-light" type="button" onclick="openStockModal({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->current_stock }}, {{ $product->is_fabric ? 'true' : 'false' }}, {{ $product->track_by_roll ? 'true' : 'false' }})">
                                                        <i class="fa fa-edit"></i> Update Stock
                                                    </button>
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
                                                    <button class="btn btn-sm btn-primary" type="button" onclick="openStockModal({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->current_stock }}, {{ $product->is_fabric ? 'true' : 'false' }}, {{ $product->track_by_roll ? 'true' : 'false' }})">
                                                        <i class="fa fa-edit"></i> Update Stock
                                                    </button>
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

        @if(session()->has('success'))
        <div class="alert alert-success">
            {{ session()->get('success') }}
        </div>
        @endif

        <div class="tab-content" id="productTabsContent">
            <!-- Regular Products Tab -->
            <div class="tab-pane fade show active" id="regular-products" role="tabpanel" aria-labelledby="regular-products-tab">
                <div class="row mt-2">
                    <div class="col-md-12">
                        <div class="tile">
                            <div class="tile-body">
                                <table class="table table-hover table-bordered" id="regularProductsTable">
                                    <thead>
                                    <tr>
                                        <th>Product </th>
                                        <th>Model </th>
                                        <th>Serial</th>
                                        <th>Sales Price</th>
                                        <th>Purchase Price</th>
                                        <th>Supplier</th>
                                        <th>Stock Status</th>
                                        <th>Image</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($products as $product)
                                        @if(!($product->is_fabric && $product->track_by_roll))
                                            @foreach($product->additionalProduct as $additional)
                                            <tr class="{{ $product->isLowStock() ? 'bg-warning' : '' }}">
                                                <td>{{$product->name}}</td>
                                                <td>{{$product->model}}</td>
                                                <td>{{$product->serial_number}}</td>
                                                <td>{{$product->sales_price}}</td>
                                                <td>{{$additional->price}}</td>
                                                <td>{{$additional->supplier->name}}</td>
                                                <td>
                                                    <span class="badge {{ $product->stock_status == 'in_stock' ? 'badge-success' : ($product->stock_status == 'low_stock' ? 'badge-warning' : 'badge-danger') }}">
                                                        {{ $product->current_stock }} / {{ $product->minimum_stock }}
                                                    </span>
                                                </td>
                                                <td><img width="40px" src="{{ asset('images/product/'.$product->image) }}"></td>
                                                <td>
                                                    <a class="btn btn-primary btn-sm" href="{{ route('products.edit', $product->id) }}"><i class="fa fa-edit" ></i></a>
                                                    <button class="btn btn-info btn-sm" type="button" onclick="openStockModal({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->current_stock }}, {{ $product->is_fabric ? 'true' : 'false' }}, {{ $product->track_by_roll ? 'true' : 'false' }})">
                                                        <i class="fa fa-cubes"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm waves-effect" type="button" onclick="deleteTag({{ $product->id }})">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                    <form id="delete-form-{{ $product->id }}" action="{{ route('products.destroy',$product->id) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        @endif
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Fabric Rolls Tab -->
            <div class="tab-pane fade" id="fabric-rolls" role="tabpanel" aria-labelledby="fabric-rolls-tab">
                <div class="row mt-2">
                    <div class="col-md-12">
                        <div class="tile">
                            <div class="tile-body">
                                <table class="table table-hover table-bordered" id="fabricRollsTable">
                                    <thead>
                                    <tr>
                                        <th>Fabric</th>
                                        <th>Model</th>
                                        <th>Serial</th>
                                        <th>Sales Price</th>
                                        <th>Purchase Price</th>
                                        <th>Supplier</th>
                                        <th>Dimensions</th>
                                        <th>Stock Status</th>
                                        <th>Image</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($products as $product)
                                        @if($product->is_fabric && $product->track_by_roll)
                                            @foreach($product->additionalProduct as $additional)
                                            <tr class="{{ $product->isLowStock() ? 'bg-warning' : '' }}">
                                                <td>
                                                    {{$product->name}}
                                                    <small class="d-block text-muted">
                                                        {{ number_format($product->total_square_feet, 2) }} sq ft
                                                    </small>
                                                </td>
                                                <td>{{$product->model}}</td>
                                                <td>{{$product->serial_number}}</td>
                                                <td>{{$product->sales_price}}</td>
                                                <td>{{$additional->price}}</td>
                                                <td>{{$additional->supplier->name}}</td>
                                                <td>
                                                    {{ number_format($product->roll_width, 2) }} × {{ number_format($product->roll_length, 2) }} ft
                                                </td>
                                                <td>
                                                    <span class="badge {{ $product->stock_status == 'in_stock' ? 'badge-success' : ($product->stock_status == 'low_stock' ? 'badge-warning' : 'badge-danger') }}">
                                                        {{ $product->fabricRolls()->active()->count() }} rolls
                                                        <small class="d-block">{{ number_format($product->total_square_feet, 2) }} sq ft</small>
                                                    </span>
                                                </td>
                                                <td><img width="40px" src="{{ asset('images/product/'.$product->image) }}"></td>
                                                <td>
                                                    <a class="btn btn-primary btn-sm" href="{{ route('products.edit', $product->id) }}"><i class="fa fa-edit" ></i></a>
                                                    <a class="btn btn-info btn-sm" href="#" onclick="viewFabricRolls({{ $product->id }}, '{{ $product->name }}')">
                                                        <i class="fa fa-list-alt"></i>
                                                    </a>
                                                    <button class="btn btn-danger btn-sm waves-effect" type="button" onclick="deleteTag({{ $product->id }})">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                    <form id="delete-form-{{ $product->id }}" action="{{ route('products.destroy',$product->id) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        @endif
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Stock Update Modal -->
    <div class="modal fade" id="stockUpdateModal" tabindex="-1" role="dialog" aria-labelledby="stockUpdateModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="stockUpdateModalLabel">Update Stock Quantity</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('products.update', 0) }}" method="POST">
                    @method('PUT')
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="product_id" id="product_id">
                        <input type="hidden" id="is_fabric" value="0">
                        <input type="hidden" id="track_by_roll" value="0">
                        
                        <div class="form-group">
                            <label for="product_name" class="col-form-label">Product:</label>
                            <input type="text" class="form-control" id="product_name" readonly>
                        </div>
                        
                        <div id="regular-product-fields">
                            <div class="form-group">
                                <label for="quantity" class="col-form-label">New Quantity:</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="0" required>
                                <small class="form-text text-muted">Auto-reorder will be enabled if quantity falls below minimum stock level.</small>
                            </div>
                        </div>
                        
                        <div id="fabric-product-message" class="alert alert-warning d-none">
                            <p><strong>Note:</strong> This is a fabric product tracked by roll.</p>
                            <p>Updating the quantity here will only change the count. For proper fabric inventory management:</p>
                            <ol>
                                <li>Click "Update Stock" to save the new quantity</li>
                                <li>Then use the "Edit" button to access the product edit page</li>
                                <li>Add new fabric rolls with specific dimensions</li>
                            </ol>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Fabric Rolls Modal -->
    <div class="modal fade" id="fabricRollsModal" tabindex="-1" role="dialog" aria-labelledby="fabricRollsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="fabricRollsModalLabel">Fabric Rolls</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h4 id="fabric-product-name"></h4>
                    <div id="fabric-rolls-container">
                        <div class="text-center">
                            <i class="fa fa-spinner fa-spin fa-2x"></i>
                            <p>Loading fabric rolls...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script type="text/javascript" src="{{asset('/')}}js/plugins/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="{{asset('/')}}js/plugins/dataTables.bootstrap.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#regularProductsTable').DataTable();
            $('#fabricRollsTable').DataTable();
            
            // Maintain active tab after page refresh
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                localStorage.setItem('activeProductTab', $(e.target).attr('href'));
            });
            
            var activeTab = localStorage.getItem('activeProductTab');
            if(activeTab){
                $('#productTabs a[href="' + activeTab + '"]').tab('show');
            }
        });
    </script>
    <script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
    <script type="text/javascript">
        function deleteTag(id) {
            swal({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
                confirmButtonClass: 'btn btn-success',
                cancelButtonClass: 'btn btn-danger',
                buttonsStyling: false,
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    event.preventDefault();
                    document.getElementById('delete-form-'+id).submit();
                } else if (
                    // Read more about handling dismissals
                    result.dismiss === swal.DismissReason.cancel
                ) {
                    swal(
                        'Cancelled',
                        'Your data is safe :)',
                        'error'
                    )
                }
            })
        }

        function openStockModal(id, name, currentStock, isFabric = false, trackByRoll = false) {
            $('#product_id').val(id);
            $('#product_name').val(name);
            $('#quantity').val(currentStock);
            $('#is_fabric').val(isFabric ? 1 : 0);
            $('#track_by_roll').val(trackByRoll ? 1 : 0);
            
            // Show/hide appropriate fields based on product type
            if (isFabric && trackByRoll) {
                $('#fabric-product-message').removeClass('d-none');
            } else {
                $('#fabric-product-message').addClass('d-none');
            }
            
            $('#stockUpdateModal').modal('show');
        }
        
        function viewFabricRolls(productId, productName) {
            $('#fabric-product-name').text(productName);
            $('#fabric-rolls-container').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Loading fabric rolls...</p></div>');
            $('#fabricRollsModal').modal('show');
            
            // Make AJAX call to get fabric rolls
            $.ajax({
                url: "{{ url('fabric-roll/product') }}/" + productId,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    var product = response.product;
                    var rolls = response.rolls;
                    
                    // Build the summary section
                    var summary = '<div class="alert alert-info mb-3">' +
                        '<strong>Summary:</strong> ' +
                        '<ul class="mb-0">' +
                        '<li>Total Square Feet: ' + parseFloat(product.total_square_feet).toFixed(2) + '</li>' +
                        '<li>Active Rolls: ' + product.active_rolls + '</li>' +
                        '<li>Depleted Rolls: ' + product.depleted_rolls + '</li>' +
                        '<li>Damaged Rolls: ' + product.damaged_rolls + '</li>' +
                        '</ul>' +
                        '</div>';
                    
                    // Build the table
                    var table = '<div class="table-responsive">' +
                        '<table class="table table-bordered table-striped">' +
                        '<thead>' +
                        '<tr>' +
                        '<th>Roll #</th>' +
                        '<th>Width (ft)</th>' +
                        '<th>Length (ft)</th>' +
                        '<th>Original Sq Ft</th>' +
                        '<th>Remaining Sq Ft</th>' +
                        '<th>Remaining %</th>' +
                        '<th>Status</th>' +
                        '<th>Received Date</th>' +
                        '</tr>' +
                        '</thead>' +
                        '<tbody id="fabric-rolls-list">';
                    
                    // Add rows for each roll
                    if (rolls.length > 0) {
                        $.each(rolls, function(index, roll) {
                            var statusClass = roll.status === 'active' ? 'success' :
                                             (roll.status === 'depleted' ? 'danger' : 'warning');
                            
                            table += '<tr>' +
                                '<td>' + roll.roll_number + '</td>' +
                                '<td>' + parseFloat(roll.width).toFixed(2) + '</td>' +
                                '<td>' + parseFloat(roll.length).toFixed(2) + '</td>' +
                                '<td>' + parseFloat(roll.original_square_feet).toFixed(2) + '</td>' +
                                '<td>' + parseFloat(roll.remaining_square_feet).toFixed(2) + '</td>' +
                                '<td>' + parseFloat(roll.remaining_percentage).toFixed(2) + '%</td>' +
                                '<td><span class="badge badge-' + statusClass + '">' +
                                    roll.status.charAt(0).toUpperCase() + roll.status.slice(1) +
                                    '</span></td>' +
                                '<td>' + roll.received_date + '</td>' +
                                '</tr>';
                        });
                    } else {
                        table += '<tr><td colspan="8" class="text-center">No fabric rolls found for this product</td></tr>';
                    }
                    
                    table += '</tbody></table></div>';
                    
                    // Update the modal content
                    $('#fabric-rolls-container').html(summary + table);
                },
                error: function(xhr, status, error) {
                    $('#fabric-rolls-container').html(
                        '<div class="alert alert-danger">' +
                        '<strong>Error:</strong> ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Failed to load fabric rolls') +
                        '</div>'
                    );
                }
            });
        }
    </script>
@endpush
