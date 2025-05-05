/**
 * Fabric Calculations JavaScript
 * 
 * This file handles real-time calculations for fabric dimensions, square feet,
 * and pricing in the custom orders form.
 */

console.log('Loading fabric-calculations.js');

$(document).ready(function() {
    console.log('Fabric calculations script initialized');
    
    // Debug information
    console.log('jQuery version:', $.fn.jquery);
    console.log('Product table exists:', $('#product-table').length > 0);
    console.log('Fabric table exists:', $('#fabric-table').length > 0);
    
    // Calculate row total
    function calculateRow(row) {
        try {
            console.log('Calculating row:', row);
            const jobType = row.find('select[name="job_type[]"]').val();
            const heightField = row.find('input[name="height[]"]');
            const breadthField = row.find('input[name="breadth[]"]');
            const priceField = row.find('input[name="price_per_square_feet[]"]');
            const quantityField = row.find('input[name="quantity[]"]');
            const totalPriceField = row.find('input[name="total_price[]"]');
            const sqFtField = row.find('input[name="square_feet[]"]');
            const sqFtDisplay = row.find('.sqft-display');
            const totalPriceDisplay = row.find('.total-price-display');
            
            console.log('Job type:', jobType);
            console.log('Height field exists:', heightField.length > 0);
            console.log('Breadth field exists:', breadthField.length > 0);
            console.log('Price field exists:', priceField.length > 0);
            console.log('Quantity field exists:', quantityField.length > 0);
            
            // Skip calculation if fields are not visible
            if (totalPriceField.closest('td').is(':hidden')) {
                console.log('Total price field is hidden, skipping calculation');
                return;
            }
            
            // Get values
            const quantity = parseInt(quantityField.val()) || 1;
            console.log('Quantity:', quantity);
            
            // Make sure height and breadth are never required
            heightField.prop('required', false);
            breadthField.prop('required', false);
            
            // Different calculation based on job type
            if (jobType === 'product' || jobType === 'other') {
                console.log('Calculating product row');
                // For product type, use a simple price * quantity calculation
                const productPrice = parseFloat(priceField.val()) || 0;
                const totalPrice = (productPrice * quantity).toFixed(2);
                
                console.log('Product price:', productPrice);
                console.log('Total price:', totalPrice);
                
                // Update the total price field
                totalPriceField.val('Rs. ' + totalPrice);
                
                // Update the total price display with just the final value
                totalPriceDisplay.html(`
                    <div class="price-display">
                        <div class="result">Rs. <span class="price-amount">${totalPrice}</span></div>
                    </div>
                `);
                
                // Style the calculation display
                totalPriceDisplay.find('.calculation').css({
                    'color': '#6c757d',
                    'font-size': '0.9em'
                });
                
                totalPriceDisplay.find('.result').css({
                    'font-weight': 'bold',
                    'color': '#28a745',
                    'font-size': '1.1em',
                    'margin-top': '3px'
                });
                
                // Add highlight effect
                totalPriceField.addClass('bg-light-yellow');
                
                // Clear square feet display for product type
                sqFtDisplay.html('');
                sqFtField.val('');
                
                // Remove highlight effect after a short delay
                setTimeout(() => {
                    totalPriceField.removeClass('bg-light-yellow');
                }, 300);
            }
            else if (jobType === 'flag') {
                console.log('Calculating fabric row');
                // For flag/fabric type, use the square feet calculation
                const height = parseFloat(heightField.val()) || 0;
                const breadth = parseFloat(breadthField.val()) || 0;
                const pricePerSqFt = parseFloat(priceField.val()) || 0;

                console.log('Height:', height);
                console.log('Breadth:', breadth);
                console.log('Price per sqft:', pricePerSqFt);

                // Calculate square feet (height and breadth are in feet)
                const squareFeet = (height * breadth).toFixed(2);
                const totalSquareFeet = (squareFeet * quantity).toFixed(2);
                const totalPrice = (squareFeet * pricePerSqFt * quantity).toFixed(2);
                
                console.log('Square feet:', squareFeet);
                console.log('Total square feet:', totalSquareFeet);
                console.log('Total price:', totalPrice);
                
                // Add highlight effect
                sqFtField.addClass('bg-light-yellow');
                sqFtDisplay.addClass('bg-light-yellow');
                totalPriceField.addClass('bg-light-yellow');
                
                // Update values with just the final value
                sqFtField.val(squareFeet + ' sqft');
                
                // Update the square feet display with just the final value
                if (quantity > 1) {
                    sqFtDisplay.html(`
                        <div class="calculation-display">
                            <div><span class="calc-result">${totalSquareFeet}</span> sqft</div>
                        </div>
                    `);
                } else {
                    sqFtDisplay.html(`
                        <div class="calculation-display">
                            <div><span class="calc-result">${squareFeet}</span> sqft</div>
                        </div>
                    `);
                }
                
                totalPriceField.val('Rs. ' + totalPrice);
                
                // Update the total price display with just the final value
                totalPriceDisplay.html(`
                    <div class="price-display">
                        <div>Rs. <span class="price-amount">${totalPrice}</span></div>
                    </div>
                `);
                
                // Remove highlight effect after a short delay
                setTimeout(() => {
                    sqFtField.removeClass('bg-light-yellow');
                    sqFtDisplay.removeClass('bg-light-yellow');
                    totalPriceField.removeClass('bg-light-yellow');
                }, 300);
            }
            
            calculateGrandTotal();
        } catch (e) {
            console.error('Calculation error:', e);
        }
    }
    
    // Calculate the grand total
    function calculateGrandTotal() {
        try {
            console.log('Calculating grand total');
            let grandTotal = 0;
            $('input[name="total_price[]"]').each(function() {
                const val = $(this).val().replace('Rs. ', '');
                grandTotal += parseFloat(val) || 0;
            });
            
            console.log('Grand total:', grandTotal);
            
            // Add animation effect to grand total
            const grandTotalElement = $('.grand-total');
            grandTotalElement.addClass('bg-light-yellow');
            grandTotalElement.html('Rs. ' + grandTotal.toFixed(2));
            
            // Add a pulsing effect to the grand total
            grandTotalElement.parent().addClass('pulse-animation');
            setTimeout(() => {
                grandTotalElement.parent().removeClass('pulse-animation');
            }, 1000);
            
            // Remove highlight after a short delay
            setTimeout(() => {
                grandTotalElement.removeClass('bg-light-yellow');
            }, 500);
            
            // Update the form title with the total
            $('.tile-title').html('Custom Order Details - Total: Rs. ' + grandTotal.toFixed(2));
        } catch (e) {
            console.error('Grand total calculation error:', e);
        }
    }
    
    // Product selection handler - fetch price from API
    function handleProductSelection(productSelect) {
        console.log('Product selected via fabric-calculations.js');
        const row = $(productSelect).closest('tr');
        const productId = $(productSelect).val();
        const selectedOption = $(productSelect).find('option:selected');
        const priceField = row.find('input[name="product_price[]"]');
        
        console.log('Product ID:', productId);
        console.log('Selected option:', selectedOption.text());
        console.log('Price field found:', priceField.length > 0);
        
        if (!productId) {
            priceField.val('');
            calculateRow(row);
            return;
        }
        
        // First try to get price from data attribute
        const dataPrice = parseFloat(selectedOption.data('price'));
        console.log('Data price from attribute:', dataPrice);
        
        // Try to extract price from option text if data-price is not available
        if (!dataPrice && selectedOption.text().includes('(Rs.')) {
            const priceMatch = selectedOption.text().match(/\(Rs\.\s*([\d.]+)/);
            if (priceMatch && priceMatch[1]) {
                const extractedPrice = priceMatch[1];
                console.log('Extracted price from text:', extractedPrice);
                priceField.val(extractedPrice);
                priceField.addClass('bg-light-yellow');
                setTimeout(() => {
                    priceField.removeClass('bg-light-yellow');
                }, 1000);
                calculateRow(row);
                return;
            }
        }
        
        if (dataPrice > 0) {
            console.log('Using price from data attribute:', dataPrice);
            priceField.val(dataPrice);
            priceField.addClass('bg-light-yellow');
            setTimeout(() => {
                priceField.removeClass('bg-light-yellow');
            }, 1000);
            calculateRow(row);
            return;
        }
        
        // If no data price, fetch from API
        console.log('Fetching price from API for product ID:', productId);
        priceField.val('Loading...');
        
        $.ajax({
            url: '/api/products/' + productId + '/price',
            type: 'GET',
            success: function(response) {
                console.log('API response:', response);
                if (response.success && response.price) {
                    priceField.val(response.price);
                } else {
                    // Fallback to direct route if API fails
                    $.ajax({
                        url: '/products/' + productId + '/price',
                        type: 'GET',
                        success: function(response) {
                            console.log('Fallback response:', response);
                            if (response && response.price) {
                                priceField.val(response.price);
                            } else {
                                priceField.val(0);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Fallback error:', error);
                            priceField.val(0);
                        },
                        complete: function() {
                            calculateRow(row);
                        }
                    });
                }
                calculateRow(row);
            },
            error: function(xhr, status, error) {
                console.error('API error:', error);
                // Fallback to direct route if API fails
                $.ajax({
                    url: '/products/' + productId + '/price',
                    type: 'GET',
                    success: function(response) {
                        console.log('Fallback response:', response);
                        if (response && response.price) {
                            priceField.val(response.price);
                        } else {
                            priceField.val(0);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Fallback error:', error);
                        priceField.val(0);
                    },
                    complete: function() {
                        calculateRow(row);
                    }
                });
            }
        });
    }

    // Fabric selection handler - fetch price from API
    function handleFabricSelection(fabricSelect) {
        console.log('Fabric selected via fabric-calculations.js');
        const row = $(fabricSelect).closest('tr');
        const fabricId = $(fabricSelect).val();
        const selectedOption = $(fabricSelect).find('option:selected');
        const priceField = row.find('input[name="fabric_price_per_square_feet[]"]');
        
        console.log('Fabric ID:', fabricId);
        console.log('Selected option:', selectedOption.text());
        console.log('Price field found:', priceField.length > 0);
        
        if (!fabricId) {
            priceField.val('');
            calculateRow(row);
            return;
        }
        
        // First try to get price from data attribute
        const dataPrice = parseFloat(selectedOption.data('price'));
        console.log('Data price from attribute:', dataPrice);
        
        // Try to extract price from option text if data-price is not available
        if (!dataPrice && selectedOption.text().includes('(Rs.')) {
            const priceMatch = selectedOption.text().match(/\(Rs\.\s*([\d.]+)/);
            if (priceMatch && priceMatch[1]) {
                const extractedPrice = priceMatch[1];
                console.log('Extracted fabric price from text:', extractedPrice);
                priceField.val(extractedPrice);
                priceField.addClass('bg-light-yellow');
                setTimeout(() => {
                    priceField.removeClass('bg-light-yellow');
                }, 1000);
                calculateRow(row);
                return;
            }
        }
        
        if (dataPrice > 0) {
            console.log('Using fabric price from data attribute:', dataPrice);
            priceField.val(dataPrice);
            priceField.addClass('bg-light-yellow');
            setTimeout(() => {
                priceField.removeClass('bg-light-yellow');
            }, 1000);
            calculateRow(row);
            return;
        }
        
        // If no data price, fetch from API
        console.log('Fetching price from API for fabric ID:', fabricId);
        priceField.val('Loading...');
        
        $.ajax({
            url: '/api/products/' + fabricId + '/price',
            type: 'GET',
            success: function(response) {
                console.log('API response:', response);
                if (response.success && response.price) {
                    priceField.val(response.price);
                } else {
                    // Fallback to direct route if API fails
                    $.ajax({
                        url: '/products/' + fabricId + '/price',
                        type: 'GET',
                        success: function(response) {
                            console.log('Fallback response:', response);
                            if (response && response.price) {
                                priceField.val(response.price);
                            } else {
                                priceField.val(0);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Fallback error:', error);
                            priceField.val(0);
                        },
                        complete: function() {
                            calculateRow(row);
                        }
                    });
                }
                calculateRow(row);
            },
            error: function(xhr, status, error) {
                console.error('API error:', error);
                // Fallback to direct route if API fails
                $.ajax({
                    url: '/products/' + fabricId + '/price',
                    type: 'GET',
                    success: function(response) {
                        console.log('Fallback response:', response);
                        if (response && response.price) {
                            priceField.val(response.price);
                        } else {
                            priceField.val(0);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Fallback error:', error);
                        priceField.val(0);
                    },
                    complete: function() {
                        calculateRow(row);
                    }
                });
            }
        });
    }
    
    // Initialize all event handlers
    function initializeEventHandlers() {
        console.log('Initializing event handlers');
        
        // Product table event handlers
        $('#product-table').on('change', 'select.product-select', function() {
            console.log('Product select changed');
            handleProductSelection(this);
        });
        
        $('#product-table').on('input', 'input[name="product_quantity[]"], input[name="product_price[]"]', function() {
            console.log('Product quantity or price changed');
            calculateRow($(this).closest('tr'));
        });
        
        // Fabric table event handlers
        $('#fabric-table').on('change', 'select.fabric-select', function() {
            console.log('Fabric select changed');
            handleFabricSelection(this);
        });
        
        $('#fabric-table').on('input', 'input[name="fabric_height[]"], input[name="fabric_breadth[]"], input[name="fabric_price_per_square_feet[]"], input[name="fabric_quantity[]"]', function() {
            console.log('Fabric dimension, price, or quantity changed');
            calculateRow($(this).closest('tr'));
        });
        
        // Handle job type change
        $('tbody').on('change', '.job-type-select', function() {
            console.log('Job type changed');
            const row = $(this).closest('tr');
            const jobType = $(this).val();
            const fabricTypeSelect = row.find('.fabric-type-select');
            const isProduct = jobType === 'product';
            const isFlag = jobType === 'flag';
            
            console.log('Job type:', jobType);
            
            // Filter product options based on job type
            if (isFlag) {
                // For flag type, show fabric products and hide regular products
                fabricTypeSelect.find('option[data-type="fabric"]').show();
                fabricTypeSelect.find('option[data-type="product"]').hide();
                fabricTypeSelect.prop('required', true);
            } else {
                // For product or other types, show regular products and hide fabric products
                fabricTypeSelect.find('option[data-type="fabric"]').hide();
                fabricTypeSelect.find('option[data-type="product"]').show();
                fabricTypeSelect.prop('required', true);
            }
            
            // Reset product selection and price when job type changes
            fabricTypeSelect.val('');
            row.find('input[name="price_per_square_feet[]"]').val('');
            
            // Handle field visibility based on job type
            if (isProduct || jobType === 'other') {
                // Hide dimension fields for product type
                row.find('input[name="height[]"]').closest('td').hide();
                row.find('input[name="breadth[]"]').closest('td').hide();
                row.find('input[name="square_feet[]"]').closest('td').hide();
                
                // Show product selection, quantity and price fields
                row.find('select[name="product_id[]"]').closest('td').show();
                row.find('input[name="quantity[]"]').closest('td').show();
                row.find('input[name="price_per_square_feet[]"]').closest('td').show();
                row.find('input[name="total_price[]"]').closest('td').show();
            } else if (isFlag) {
                // Show all fields for flag type
                row.find('input[name="height[]"]').closest('td').show();
                row.find('input[name="breadth[]"]').closest('td').show();
                row.find('input[name="square_feet[]"]').closest('td').show();
                row.find('select[name="product_id[]"]').closest('td').show();
                row.find('input[name="price_per_square_feet[]"]').closest('td').show();
                row.find('input[name="quantity[]"]').closest('td').show();
                row.find('input[name="total_price[]"]').closest('td').show();
            }
            
            calculateRow(row);
        });
        
        // Handle fabric type change
        $('tbody').on('change', '.fabric-type-select', function() {
            console.log('Fabric type changed');
            const row = $(this).closest('tr');
            const selectedOption = $(this).find('option:selected');
            const availableSquareFeet = parseFloat(selectedOption.data('square-feet')) || 0;
            const productPrice = parseFloat(selectedOption.data('price')) || 0;
            const jobType = row.find('select[name="job_type[]"]').val();
            
            console.log('Available square feet:', availableSquareFeet);
            console.log('Product price:', productPrice);
            console.log('Job type:', jobType);
            
            // Display available square feet info for fabric products
            if (availableSquareFeet > 0) {
                row.find('.square-feet-info').html(`<small class="text-info">Available: ${availableSquareFeet} sqft</small>`);
            }
            
            // Auto-populate price field for product type
            if (jobType === 'product' && productPrice > 0) {
                row.find('input[name="price_per_square_feet[]"]').val(productPrice);
            }
            
            calculateRow(row);
        });
        
        // Add row button
        $('.addProductRow').on('click', function() {
            console.log('Adding product row');
            setTimeout(function() {
                $('#product-table tbody tr:last').find('select.product-select').trigger('change');
            }, 100);
        });
        
        $('.addFabricRow').on('click', function() {
            console.log('Adding fabric row');
            setTimeout(function() {
                $('#fabric-table tbody tr:last').find('select.fabric-select').trigger('change');
            }, 100);
        });
        
        // Remove row button
        $('tbody').on('click', '.remove-product, .remove-fabric', function(e) {
            console.log('Removing row');
            e.preventDefault();
            if ($('tbody tr').length > 1) {
                $(this).closest('tr').remove();
                calculateGrandTotal();
            }
        });
        
        // Customer details fetch
        $('#customer_id').on('change', function() {
            console.log('Customer changed');
            const customerId = $(this).val();
            
            if (!customerId) {
                $('#customer_address, #customer_mobile, #customer_email').val('');
                return;
            }
            
            // Show loading state
            $('#customer_address, #customer_mobile, #customer_email').val('Loading...');
            
            // Fetch customer details from API
            $.ajax({
                url: `/api/customers/${customerId}`,
                method: 'GET',
                success: function(response) {
                    console.log('Customer API response:', response);
                    if (response.success && response.data) {
                        const customer = response.data;
                        $('#customer_address').val(customer.address || 'N/A');
                        $('#customer_mobile').val(customer.mobile || 'N/A');
                        $('#customer_email').val(customer.email || 'N/A');
                    } else {
                        // Try direct fetch if API fails
                        fetchCustomerDirectly(customerId);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Customer API error:', error);
                    // Try direct fetch if API fails
                    fetchCustomerDirectly(customerId);
                }
            });
        });
        
        // Fallback function to fetch customer directly
        function fetchCustomerDirectly(customerId) {
            console.log('Fetching customer directly');
            $.ajax({
                url: `/customers/${customerId}/details`,
                method: 'GET',
                success: function(response) {
                    console.log('Customer direct response:', response);
                    if (response && response.customer) {
                        const customer = response.customer;
                        $('#customer_address').val(customer.address || 'N/A');
                        $('#customer_mobile').val(customer.mobile || 'N/A');
                        $('#customer_email').val(customer.email || 'N/A');
                    } else {
                        $('#customer_address').val('Customer information available in database');
                        $('#customer_mobile').val('Please check customer details');
                        $('#customer_email').val('Contact administrator if needed');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Customer direct error:', error);
                    // If all else fails, show a message that customer exists but details can't be fetched
                    $('#customer_address').val('Customer exists in database');
                    $('#customer_mobile').val('Details not available via API');
                    $('#customer_email').val('Continue with order creation');
                }
            });
        }
    }
    
    // Initialize calculations
    function initializeCalculations() {
        console.log('Initializing calculations');
        
        // Initialize product rows
        $('#product-table tbody tr').each(function() {
            calculateRow($(this));
        });
        
        // Initialize fabric rows
        $('#fabric-table tbody tr').each(function() {
            calculateRow($(this));
        });
        
        // Calculate grand total
        calculateGrandTotal();
    }
    
    // Define global function to calculate all totals
    window.calculateAllTotals = function() {
        console.log('Calculating all totals');
        initializeCalculations();
    };
    
    // Initialize everything
    initializeEventHandlers();
    initializeCalculations();
    
    console.log('Fabric calculations script initialization complete');
});