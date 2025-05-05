@extends('layouts.master')

@section('title', 'Add Fabric Roll | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-edit"></i>Add New Fabric Roll</h1>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Products</li>
                <li class="breadcrumb-item"><a href="#">Add Fabric Roll</a></li>
            </ul>
        </div>

        @if(session()->has('message'))
            <div class="alert alert-success">
                {{ session()->get('message') }}
            </div>
        @endif

        <div class="">
            <a class="btn btn-primary" href="{{route('products.index')}}"><i class="fa fa-list"></i> Manage Products</a>
        </div>
        <div class="row mt-2">

            <div class="clearix"></div>
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title">Fabric Roll Details</h3>
                    <div class="tile-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form method="POST" action="{{route('fabric-rolls.store')}}" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="control-label">Fabric Name</label>
                                    <input name="name" class="form-control @error('name') is-invalid @enderror" type="text" placeholder="Fabric Name">
                                    @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="control-label">Serial Number</label>
                                    <input name="serial_number" class="form-control @error('serial_number') is-invalid @enderror" type="number" placeholder="Enter Serial Number">
                                    @error('serial_number')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="control-label">Model</label>
                                    <input name="model" class="form-control @error('model') is-invalid @enderror" type="text" placeholder="Enter Model">
                                    @error('model')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="control-label">Category</label>

                                    <select name="category_id" class="form-control">
                                        <option>---Select Category---</option>
                                        @foreach($categories as $id => $name)
                                            <option value="{{$id}}">{{$name}}</option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Note: Products can only be added to parent categories.</small>

                                    @error('category_id')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="control-label">Selling Price</label>
                                    <input name="sales_price" class="form-control @error('sales_price') is-invalid @enderror" type="number" placeholder="Enter Selling Price">
                                    @error('sales_price')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="control-label">Unit</label>
                                    <select name="unit_id" class="form-control" readonly>
                                        @foreach($units as $unit)
                                            <option value="{{$unit->id}}" {{ $unit->id == $defaultUnitId ? 'selected' : '' }}>{{$unit->name}}</option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Fabric rolls are measured in Square Feet</small>
                                    @error('unit_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="control-label">Image</label>
                                    <input name="image" class="form-control @error('image') is-invalid @enderror" type="file">
                                    @error('image')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="control-label">Tax </label>
                                    <select name="tax_id" class="form-control">
                                        <option>---Select Tax---</option>
                                        @foreach($taxes as $tax)
                                            @if($tax->name == '0' || $tax->name == '13')
                                                <option value="{{$tax->id}}">{{$tax->name}} %</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('tax_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Fabric Roll Specific Fields -->
                            <div class="tile">
                                <h3 class="tile-title">Fabric Roll Dimensions</h3>
                                <div class="tile-body">
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label class="control-label">Roll Width (feet)</label>
                                            <input name="roll_width" value="{{ old('roll_width') }}" class="form-control" type="number" step="0.01" placeholder="Width in feet">
                                            @error('roll_width')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label class="control-label">Roll Length (feet)</label>
                                            <input name="roll_length" value="{{ old('roll_length') }}" class="form-control" type="number" step="0.01" placeholder="Length in feet">
                                            @error('roll_length')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label class="control-label">Number of Rolls</label>
                                            <input value="{{ old('number_of_rolls', 1) }}" name="number_of_rolls" class="form-control" type="number" min="1" step="1" placeholder="Number of rolls with these dimensions">
                                            <small class="form-text text-muted">Enter the number of rolls with identical dimensions.</small>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label class="control-label">Alert Threshold (%)</label>
                                            <input value="{{ old('alert_threshold_percent', 20) }}" name="alert_threshold_percent" class="form-control" type="number" min="1" max="100" placeholder="Alert when below this percentage">
                                            <small class="form-text text-muted">You'll receive alerts when fabric is below this percentage of the original amount.</small>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <div id="square-feet-per-roll-display" class="alert alert-info mt-3 d-none">
                                                Each roll contains <strong>0</strong> square feet of fabric.
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <div id="total-square-feet-display" class="alert alert-success mt-3 d-none">
                                                Total for all rolls: <strong>0</strong> square feet of fabric.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tile">
                                <h3 class="tile-title">Supplier Information</h3>
                                <div id="example-2" class="content">
                                    <div class="group row">
                                        <div class="form-group col-md-5">
                                             <select name="supplier_id[]" class="form-control">
                                                <option>Select Supplier</option>
                                                @foreach($suppliers as $supplier)
                                                    <option value="{{$supplier->id}}">{{$supplier->name}} </option>
                                                @endforeach
                                            </select>
                                         </div>
                                        <div class="form-group col-md-5">
                                             <input name="supplier_price[]" class="form-control @error('supplier_price') is-invalid @enderror" type="number" placeholder="Purchase Price">
                                            <span class="text-danger">{{ $errors->has('additional_body') ? $errors->first('body') : '' }}</span>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <button type="button" id="btnAdd-2" class="btn btn-success btn-sm float-right"><i class="fa fa-plus"></i></button>
                                            <button type="button" class="btn btn-danger btn-sm btnRemove float-right"><i class="fa fa-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                             </div>
                            <div class="form-group col-md-4 align-self-end">
                                <button class="btn btn-success" type="submit"><i class="fa fa-fw fa-lg fa-check-circle"></i>Add Fabric Roll</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>

     </main>
@endsection
@push('js')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
     <script src="{{asset('/')}}js/multifield/jquery.multifield.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function(){
            var maxField = 10; //Input fields increment limitation
            var addButton = $('.add_button'); //Add button selector
            var wrapper = $('.field_wrapper'); //Input field wrapper
            var fieldHTML = '<div><select name="supplier_id[]" class="form-control"><option class="form-control">Select Supplier</option>@foreach($suppliers as $supplier)<option value="{{$supplier->id}}">{{$supplier->name}}</option>@endforeach</select><input name="supplier_price[]" class="form-control" type="text" placeholder="Enter Sales Price"><a href="javascript:void(0);" class="remove_button btn btn-danger" title="Delete field"><i class="fa fa-minus"></i></a></div>'
            var x = 1; //Initial field counter is 1

            //Once add button is clicked
            $(addButton).click(function(){
                //Check maximum number of input fields
                if(x < maxField){
                    x++; //Increment field counter
                    $(wrapper).append(fieldHTML); //Add field html
                }
            });

            //Once remove button is clicked
            $(wrapper).on('click', '.remove_button', function(e){
                e.preventDefault();
                $(this).parent('div').remove(); //Remove field html
                x--; //Decrement field counter
            });

            $('#example-2').multifield({
                section: '.group',
                btnAdd:'#btnAdd-2',
                btnRemove:'.btnRemove'
            });
            
            // Calculate square feet when dimensions or number of rolls change
            $('input[name="roll_width"], input[name="roll_length"], input[name="number_of_rolls"]').change(function() {
                var width = parseFloat($('input[name="roll_width"]').val()) || 0;
                var length = parseFloat($('input[name="roll_length"]').val()) || 0;
                var numberOfRolls = parseInt($('input[name="number_of_rolls"]').val()) || 1;
                
                var squareFeetPerRoll = width * length; // Calculate square feet per roll (dimensions are in feet)
                var totalSquareFeet = squareFeetPerRoll * numberOfRolls; // Calculate total square feet for all rolls
                
                // Display the calculated square feet per roll
                if (width > 0 && length > 0) {
                    $('#square-feet-per-roll-display').removeClass('d-none');
                    $('#square-feet-per-roll-display strong').text(squareFeetPerRoll.toFixed(2));
                    
                    // Display the total square feet for all rolls
                    $('#total-square-feet-display').removeClass('d-none');
                    $('#total-square-feet-display strong').text(totalSquareFeet.toFixed(2));
                } else {
                    $('#square-feet-per-roll-display').addClass('d-none');
                    $('#total-square-feet-display').addClass('d-none');
                }
            });
        });
    </script>
@endpush