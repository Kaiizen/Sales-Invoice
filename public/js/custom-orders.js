document.addEventListener('DOMContentLoaded', function() {
    // Handle dynamic row functionality
    $(document).on('click', '.addRow', function () {
        // Get the first data row (not the button row)
        const tableBody = $(this).closest('tbody');
        const firstRow = tableBody.find('tr:first');
        
        // Clone the first row
        const newRow = firstRow.clone();
        
        // Clear the inputs
        newRow.find('input').val('');
        newRow.find('select').val('');
        
        // Set default quantity to 1
        newRow.find('input[name="quantity[]"]').val(1);
        
        // Add the new row before the button row
        $(this).closest('tr').before(newRow);
        
        // Recalculate totals if the function exists
        if (typeof calculateRow === 'function') {
            calculateRow();
        }
    });

    // Handle job type changes per row
    $(document).on('change', '.job-type-select', function() {
        const row = $(this).closest('tr');
        const jobType = this.value;
        const isProduct = jobType === 'product';
        const isOther = jobType === 'other';

        // Get the table and header row
        const table = row.closest('table');
        const headerRow = table.find('thead tr');
        
        // Hide the flag option in the dropdown
        row.find('.job-type-select option[value="flag"]').hide();
        
        // Completely restructure the table based on job type
        if (isProduct) {
            // For product type, show only relevant columns and use full width
            
            // Update header texts and column widths
            headerRow.find('th:eq(0)').text('Product Type').css('width', '15%');
            headerRow.find('th:eq(1)').text('Product Name').css('width', '30%');
            headerRow.find('th:eq(2)').text('Unit').css('width', '10%');
            headerRow.find('th:eq(3)').text('Quantity').css('width', '15%');
            headerRow.find('th:eq(4)').text('Price/Unit').css('width', '15%');
            headerRow.find('th:eq(5)').text('Total').css('width', '15%');
            
            // Hide unnecessary columns
            headerRow.find('th:eq(6)').hide(); // Hide SqFt column
            headerRow.find('th:eq(7)').hide(); // Hide Stitching column
            
            // Show only relevant fields in this row
            row.find('select[name="job_type[]"]').closest('td').show(); // Product Type
            row.find('select[name="product_id[]"]').closest('td').show().css('width', '30%'); // Product Name
            
            // Create or update the unit field to show "pcs" (pieces)
            const unitCell = row.find('input[name="height[]"]').closest('td');
            if (!unitCell.find('.unit-display').length) {
                unitCell.html('<div class="unit-display form-control-static">pcs</div>');
            }
            unitCell.show();
            
            // Hide unnecessary fields
            row.find('input[name="breadth[]"]').closest('td').hide(); // Hide Breadth
            row.find('input[name="square_feet[]"]').closest('td').hide(); // Hide SqFt
            row.find('input[name="price_per_square_feet[]"]').closest('td').show(); // Price/Unit
            row.find('input[name="quantity[]"]').closest('td').show(); // Quantity
            row.find('input[name="total_price[]"]').closest('td').show(); // Total
            
            // Add some styling to make the table more user-friendly
            row.find('.unit-display').css({
                'text-align': 'center',
                'font-weight': 'bold',
                'padding': '10px',
                'background-color': '#f8f9fa',
                'border-radius': '4px',
                'border': '1px solid #ced4da'
            });
            
            // Set product dropdown to required and show only regular products
            row.find('select[name="product_id[]"]').prop('required', true);
            updateProductDropdown(row, 'product');
            
            // Clean up the UI
            row.find('select, input').css({
                'border': '1px solid #ced4da',
                'border-radius': '4px',
                'padding': '8px 12px',
                'width': '100%'
            });
            
            // Make the total price display more prominent
            row.find('.total-price-display').css({
                'font-weight': 'bold',
                'color': '#28a745',
                'font-size': '1.1em'
            });
        }
        else if (isOther) {
            // For other types, show simplified columns
            
            // Update header texts and column widths
            headerRow.find('th:eq(0)').text('Type').css('width', '15%');
            headerRow.find('th:eq(1)').text('Description').css('width', '30%');
            headerRow.find('th:eq(2)').text('Unit').css('width', '10%');
            headerRow.find('th:eq(3)').text('Quantity').css('width', '15%');
            headerRow.find('th:eq(4)').text('Price/Unit').css('width', '15%');
            headerRow.find('th:eq(5)').text('Total').css('width', '15%');
            
            // Hide unnecessary columns
            headerRow.find('th:eq(6)').hide(); // Hide SqFt column
            headerRow.find('th:eq(7)').hide(); // Hide Stitching column
            
            // Show only relevant fields
            row.find('select[name="job_type[]"]').closest('td').show(); // Type
            row.find('select[name="product_id[]"]').closest('td').hide(); // Hide Product dropdown
            
            // Create or update the unit field for custom unit
            const unitCell = row.find('input[name="height[]"]').closest('td');
            if (!unitCell.find('.unit-input').length) {
                unitCell.html('<input type="text" name="unit[]" class="form-control unit-input" placeholder="Unit">');
            }
            unitCell.show();
            
            row.find('input[name="breadth[]"]').closest('td').hide(); // Hide Breadth
            row.find('input[name="square_feet[]"]').closest('td').hide(); // Hide SqFt
            row.find('input[name="price_per_square_feet[]"]').closest('td').show(); // Price/Unit
            row.find('input[name="quantity[]"]').closest('td').show(); // Quantity
            row.find('input[name="total_price[]"]').closest('td').show(); // Total
            
            // Product selection not required for other types
            row.find('select[name="product_id[]"]').prop('required', false);
            
            // Clean up the UI
            row.find('select, input').css({
                'border': '1px solid #ced4da',
                'border-radius': '4px',
                'padding': '8px 12px',
                'width': '100%'
            });
            
            // Make the total price display more prominent
            row.find('.total-price-display').css({
                'font-weight': 'bold',
                'color': '#28a745',
                'font-size': '1.1em'
            });
        }
        
        // Apply the same changes to all rows if this is the first row
        if (row.is(':first-child')) {
            const allRows = table.find('tbody tr');
            allRows.each(function() {
                const currentRow = $(this);
                if (!currentRow.is(row)) {
                    // Set the same job type for all rows
                    currentRow.find('.job-type-select').val(jobType).trigger('change');
                }
            });
        }
    });
    
    // Function to update product dropdown based on type
    function updateProductDropdown(row, type) {
        const dropdown = row.find('select[name="product_id[]"]');
        
        // First, update the dropdown label based on type
        if (type === 'fabric') {
            dropdown.attr('placeholder', 'Select Fabric');
            dropdown.find('option:first').text('Select Fabric');
        } else if (type === 'product') {
            dropdown.attr('placeholder', 'Select Product');
            dropdown.find('option:first').text('Select Product');
        }
        
        // Show/hide options based on type
        dropdown.find('option').each(function() {
            const option = $(this);
            const productType = option.data('type');
            
            if (type === 'fabric' && productType === 'fabric') {
                option.show();
            } else if (type === 'product' && productType !== 'fabric') {
                option.show();
            } else {
                option.hide();
            }
        });
        
        // Reset selection if current selection doesn't match type
        const selectedOption = dropdown.find('option:selected');
        const selectedType = selectedOption.data('type');
        
        if ((type === 'fabric' && selectedType !== 'fabric') ||
            (type === 'product' && selectedType === 'fabric')) {
            dropdown.val('');
        }
        
        // Add some styling to make the dropdown more user-friendly
        dropdown.css({
            'font-weight': 'bold',
            'border': '1px solid #ced4da',
            'border-radius': '4px',
            'padding': '8px 12px',
            'width': '100%'
        });
    }

    // Handle product selection
    $(document).on('change', '.product-select', async function() {
        // Product selection changed
        const productId = this.value;
        const row = $(this).closest('tr');
        const selectedOption = $(this).find('option:selected');
        
        if(!productId) return;

        // First try to get price from data attribute
        const dataPrice = selectedOption.attr('data-price');
        
        // Try to extract price from option text if data-price is not available
        let extractedPrice = null;
        if (selectedOption.text().includes('(Rs.')) {
            const priceMatch = selectedOption.text().match(/\(Rs\.\s*([\d.]+)/);
            if (priceMatch && priceMatch[1]) {
                extractedPrice = priceMatch[1];
                // Price extracted from text
            }
        }
        
        // Set price in product_price field if it exists
        const priceField = row.find('input[name="product_price[]"]');
        if (priceField.length > 0) {
            // Set price in the field
            if (dataPrice) {
                priceField.val(dataPrice);
            } else if (extractedPrice) {
                priceField.val(extractedPrice);
            }
            
            // Highlight the field
            priceField.addClass('bg-light-yellow');
            setTimeout(() => {
                priceField.removeClass('bg-light-yellow');
            }, 1000);
            
            // Trigger calculation if available
            if (typeof calculateProductRow === 'function') {
                // Calculate product row
                calculateProductRow(row);
            } else {
                // Manual calculation as fallback
                const quantity = parseInt(row.find('input[name="product_quantity[]"]').val()) || 1;
                const price = parseFloat(priceField.val()) || 0;
                const total = (price * quantity).toFixed(2);
                // Calculate total
                
                // Update total field
                const totalField = row.find('input[name="product_total_price[]"]');
                if (totalField.length > 0) {
                    totalField.val('Rs. ' + total);
                }
                
                // No need to update display div as it's been removed
            }
        }

        try {
            // Fetch product details from API
            const response = await fetch(`/api/products/${productId}`);
            const product = await response.json();
            
            // Process API response
            
            row.find('[name="height[]"]').val(product.height || '');
            row.find('[name="breadth[]"]').val(product.breadth || '');
            row.find('[name="price_per_square_feet[]"]').val(product.price || '');
            
            // Also update product_price if it wasn't already set and we got a price from the API
            if (priceField.length > 0 && !priceField.val() && product.sales_price) {
                // Set price from API
                priceField.val(product.sales_price);
                
                // Trigger calculation if available
                // Check if calculation function exists
                if (typeof calculateProductRow === 'function') {
                    // Calculate product row
                    calculateProductRow(row);
                } else {
                    // Manual calculation as fallback
                    const quantity = parseInt(row.find('input[name="product_quantity[]"]').val()) || 1;
                    const price = parseFloat(priceField.val()) || 0;
                    const total = (price * quantity).toFixed(2);
                    // Calculate total
                    
                    // Update total field
                    const totalField = row.find('input[name="product_total_price[]"]');
                    if (totalField.length > 0) {
                        totalField.val('Rs. ' + total);
                    }
                    
                    // No need to update display div as it's been removed
                }
            }
            
            row.find('[name="quantity[]"]').val(1);
        } catch (error) {
            console.error('Error fetching product details:', error);
        }
    });
    
    // Add event handler for quantity changes
    $(document).on('input', 'input[name="product_quantity[]"]', function() {
        // Product quantity changed
        const row = $(this).closest('tr');
        
        // Call the calculateProductRow function
        if (typeof calculateProductRow === 'function') {
            // Calculate product row
            calculateProductRow(row);
        } else {
            // Manual calculation as fallback
            const quantity = parseInt($(this).val()) || 1;
            const priceField = row.find('input[name="product_price[]"]');
            const price = parseFloat(priceField.val()) || 0;
            const total = (price * quantity).toFixed(2);
            
            // Calculate total
            
            // Update total field
            const totalField = row.find('input[name="product_total_price[]"]');
            if (totalField.length > 0) {
                totalField.val('Rs. ' + total);
            }
            
            // No need to update display div as it's been removed
        }
    });
    
    // Get elements if they exist
    const jobTypeSelect = document.getElementById('job_type');
    const productSelect = document.getElementById('product_id');
    const dimensionsFields = document.querySelectorAll('.dimension-field');

    if(jobTypeSelect) {
        jobTypeSelect.addEventListener('change', function() {
            const isFlag = this.value === 'flag';
            const isProduct = this.value === 'product';
            
            // Toggle dimension fields
            if(dimensionsFields && dimensionsFields.length > 0) {
                dimensionsFields.forEach(field => {
                    field.disabled = !isFlag;
                    if(!isFlag) field.value = '';
                });
                
                // Add a global calculateProductRow function to ensure it's available
                window.calculateProductRow = function(row) {
                    // Global calculation function
                    
                    // Get the quantity and price
                    const quantity = parseInt($(row).find('input[name="product_quantity[]"]').val()) || 1;
                    const priceField = $(row).find('input[name="product_price[]"]');
                    const totalPriceField = $(row).find('input[name="product_total_price[]"]');
                    
                    // Get values for calculation
                    
                    // Skip calculation if price is still loading
                    if (priceField.val() === 'Loading...') {
                        return;
                    }
                    
                    // For product type, use a simple price * quantity calculation
                    const productPrice = parseFloat(priceField.val()) || 0;
                    const totalPrice = (productPrice * quantity).toFixed(2);
                    
                    
                    // Update the total price field with the final value
                    totalPriceField.val('Rs. ' + totalPrice);
                    
                    // Make sure the total is visible by adding styling
                    totalPriceField.css({
                        'background-color': '#f8fff8',
                        'color': '#28a745',
                        'font-weight': 'bold',
                        'font-size': '16px',
                        'border': '1px solid #28a745'
                    });
                    
                    // Add highlight effect
                    totalPriceField.addClass('bg-light-yellow');
                    setTimeout(() => {
                        totalPriceField.removeClass('bg-light-yellow');
                        // Keep the styling after highlight effect
                        totalPriceField.css({
                            'background-color': '#f8fff8',
                            'color': '#28a745',
                            'font-weight': 'bold',
                            'font-size': '16px',
                            'border': '1px solid #28a745'
                        });
                    }, 500);
                    
                    // Calculate grand total if the function exists
                    if (typeof calculateProductGrandTotal === 'function') {
                        calculateProductGrandTotal();
                    } else {
                        // Calculate grand total manually
                        let grandTotal = 0;
                        $('input[name="product_total_price[]"]').each(function() {
                            const val = $(this).val().replace('Rs. ', '');
                            grandTotal += parseFloat(val) || 0;
                        });
                        
                        // Update grand total
                        $('.product-grand-total').html('Rs. ' + grandTotal.toFixed(2));
                        
                        // Update combined total if the element exists
                        if ($('.combined-grand-total').length > 0) {
                            const fabricTotal = parseFloat($('.fabric-grand-total').text().replace('Rs. ', '')) || 0;
                            const combinedTotal = grandTotal + fabricTotal;
                            $('.combined-grand-total').text('Rs. ' + combinedTotal.toFixed(2));
                        }
                    }
                };
            }
            
            // Toggle product selection
            if(productSelect) {
                productSelect.disabled = !isProduct;
                if(!isProduct) productSelect.value = '';
            }
        });
    }

    if(productSelect) {
        productSelect.addEventListener('change', async function() {
            const productId = this.value;
            if(!productId) return;

            try {
                const response = await fetch(`/api/products/${productId}`);
                const product = await response.json();
                
                const heightField = document.getElementById('height');
                const breadthField = document.getElementById('breadth');
                const priceField = document.getElementById('price_per_square_feet');
                const quantityField = document.getElementById('quantity');
                
                if(heightField) heightField.value = product.height || '';
                if(breadthField) breadthField.value = product.breadth || '';
                if(priceField) priceField.value = product.price || '';
                if(quantityField) quantityField.value = 1;
            } catch (error) {
                console.error('Error fetching product details:', error);
            }
        });
    }
});