@extends('layouts.master')

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@section('title', 'Invoice | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-edit"></i> Create Invoice</h1>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Invoices</li>
                <li class="breadcrumb-item"><a href="#">Create</a></li>
            </ul>
        </div>


         <div class="row">
             <div class="clearix"></div>
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title">Invoice</h3>
                    <div class="tile-body">
                        <form  method="POST" action="{{route('invoice.store')}}">
                            @csrf
                            <div class="form-group col-md-3">
                                <label class="control-label">Customer Name</label>
                                <select name="customer_id" class="form-control">
                                    <option>Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option name="customer_id" value="{{$customer->id}}">{{$customer->name}} </option>
                                    @endforeach
                                </select>                            </div>
                            <div class="form-group col-md-3">
                                <label class="control-label">Date</label>
                                <input name="date"  class="form-control datepicker"  value="<?php echo date('Y-m-d')?>" type="date" placeholder="Enter your email">
                            </div>



                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th scope="col">Product</th>
                                <th scope="col">Quantity</th>
                                <th scope="col">Price</th>
                                <th scope="col">Discount %</th>
                                <th scope="col">Amount</th>
                                <th scope="col"><a class="addRow badge badge-success text-white"><i class="fa fa-plus"></i> Add Row</a></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td><select name="product_id[]" class="form-control productname" required onchange="updatePrice(this)">
                                        <option value="">Select Product</option>
                                    @foreach($products as $product)
                                            <option value="{{$product->id}}" data-price="{{$product->sales_price}}">{{$product->name}} ({{$product->sales_price}})</option>
                                        @endforeach
                                    </select></td>
                                <td><input type="text" name="qty[]" class="form-control qty" ></td>
                                <td><input type="text" name="price[]" class="form-control price" readonly></td>
                                <td><input type="text" name="dis[]" class="form-control dis" ></td>
                                <td><input type="text" name="amount[]" class="form-control amount" ></td>
                                <td><a   class="btn btn-danger remove"> <i class="fa fa-remove"></i></a></td>
                             </tr>
                            </tbody>
                            <tfoot>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td><b>Total</b></td>
                                <td><b class="total"></b></td>
                                <td></td>
                            </tr>
                            </tfoot>

                        </table>

                            <div >
                                <button class="btn btn-primary" type="submit">Submit</button>
                            </div>
                     </form>
                    </div>
                </div>


                </div>
            </div>







    </main>

@endsection
@push('js')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
     <script src="{{asset('/')}}js/multifield/jquery.multifield.min.js"></script>




    <script type="text/javascript">
        // Global function for updating price - moved outside document.ready
        function updatePrice(selectElement) {
            try {
                // Get the selected option
                var selectedOption = selectElement.options[selectElement.selectedIndex];
                
                if (!selectedOption || selectedOption.value === "") {
                    console.log("No product selected");
                    return;
                }
                
                // Try multiple approaches to get the price
                
                // Approach 1: Get from data-price attribute
                var price = selectedOption.getAttribute('data-price');
                console.log("Price from data-price attribute:", price);
                
                // Approach 2: Extract from option text if it contains price in parentheses
                var optionText = selectedOption.text;
                console.log("Option text:", optionText);
                
                var priceMatch = optionText.match(/\(([^)]+)\)/);
                if (priceMatch && priceMatch[1]) {
                    var extractedPrice = priceMatch[1].replace(/[^\d.]/g, '');
                    console.log("Extracted price from text:", extractedPrice);
                    
                    if (extractedPrice) {
                        price = extractedPrice;
                    }
                }
                
                // Approach 3: Make a direct AJAX call to get the price
                if (!price || price === "") {
                    console.log("No price found, making AJAX call");
                    
                    // Use jQuery's synchronous AJAX to ensure we get the price before continuing
                    $.ajax({
                        type: 'GET',
                        url: '/find-price',
                        async: false,
                        data: {"id": selectedOption.value},
                        success: function(data) {
                            if (data && data.sales_price) {
                                price = data.sales_price;
                                console.log("Price from AJAX:", price);
                            }
                        }
                    });
                }
                
                // Find the parent row
                var row = selectElement.closest('tr');
                
                // Find the price input in this row
                var priceInput = row.querySelector('.price');
                
                // Set the price
                if (price && price !== "") {
                    priceInput.value = price;
                    console.log("Setting price to:", price);
                    
                    // Make it read-only
                    priceInput.readOnly = true;
                    
                    // Focus on quantity
                    row.querySelector('.qty').focus();
                    
                    // Trigger calculation
                    $(row).find('.qty').trigger('keyup');
                } else {
                    console.error("Could not determine price for selected product");
                }
            } catch (e) {
                console.error("Error in updatePrice:", e);
            }
        }
        
        // Initialize all product selects on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log("DOM fully loaded");
            
            // Set up all existing product selects
            var productSelects = document.querySelectorAll('.productname');
            console.log("Found " + productSelects.length + " product selects");
            
            productSelects.forEach(function(select) {
                // Set up change event handler
                select.addEventListener('change', function() {
                    console.log("Product select changed");
                    updatePrice(this);
                });
                
                // If a product is already selected, update its price
                if (select.selectedIndex > 0) {
                    console.log("Product already selected, updating price");
                    updatePrice(select);
                }
            });
        });
        
        $(document).ready(function(){
            console.log("jQuery document ready");

            $('tbody').delegate('.qty,.price,.dis', 'keyup', function () {
                var tr = $(this).parent().parent();
                var qty = tr.find('.qty').val();
                var price = tr.find('.price').val();
                var dis = tr.find('.dis').val();
                var amount = (qty * price)-(qty * price * dis)/100;
                tr.find('.amount').val(amount);
                total();
            });
            
            function total(){
                var total = 0;
                $('.amount').each(function (i,e) {
                    var amount =$(this).val()-0;
                    total += amount;
                })
                $('.total').html(total);
            }

            $('.addRow').on('click', function () {
                addRow();
            });

            function addRow() {
                var addRow = '<tr>\n' +
                    '         <td><select name="product_id[]" class="form-control productname" required onchange="updatePrice(this)">\n' +
                    '         <option value="">Select Product</option>\n' +
'                                        @foreach($products as $product)\n' +
'                                            <option value="{{$product->id}}" data-price="{{$product->sales_price}}">{{$product->name}} ({{$product->sales_price}})</option>\n' +
'                                        @endforeach\n' +
                    '               </select></td>\n' +
'                                <td><input type="text" name="qty[]" class="form-control qty" ></td>\n' +
'                                <td><input type="text" name="price[]" class="form-control price" readonly></td>\n' +
'                                <td><input type="text" name="dis[]" class="form-control dis" ></td>\n' +
'                                <td><input type="text" name="amount[]" class="form-control amount" ></td>\n' +
'                                <td><a   class="btn btn-danger remove"> <i class="fa fa-remove"></i></a></td>\n' +
'                             </tr>';
                $('tbody').append(addRow);
                
                // Set up the new select
                var newSelect = $('tbody tr:last-child .productname')[0];
                newSelect.addEventListener('change', function() {
                    console.log("New product select changed");
                    updatePrice(this);
                });
            };

            // Replace deprecated .live() with .on()
            $(document).on('click', '.remove', function () {
                var l =$('tbody tr').length;
                if(l==1){
                    alert('you cant delete last one')
                }else{
                    $(this).parent().parent().remove();
                }
            });
        });
    </script>

@endpush
