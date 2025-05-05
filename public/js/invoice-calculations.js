/**
 * Invoice Calculations
 * This script handles calculations for product and fabric items in invoices
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Invoice calculations script loaded');

    // Define the calculateProductGrandTotal function globally
    window.calculateProductGrandTotal = function() {
        console.log('Calculating product grand total');
        let grandTotal = 0;
        
        // Sum up all product total prices
        $('input[name="product_total_price[]"]').each(function() {
            const val = $(this).val().replace('Rs. ', '');
            const amount = parseFloat(val) || 0;
            grandTotal += amount;
            console.log('Adding to product total:', val, '=', amount, 'Running total:', grandTotal);
        });
        
        console.log('Final product grand total:', grandTotal);
        
        // Update the product grand total display
        $('.product-grand-total').html('Rs. ' + grandTotal.toFixed(2));
        
        // Update combined total if the element exists
        updateCombinedTotal();
        
        return grandTotal;
    };
    
    // Define the calculateFabricGrandTotal function globally
    window.calculateFabricGrandTotal = function() {
        console.log('Calculating fabric grand total');
        let grandTotal = 0;
        
        // Sum up all fabric total prices
        $('input[name="fabric_total_price[]"]').each(function() {
            const val = $(this).val().replace('Rs. ', '');
            const amount = parseFloat(val) || 0;
            grandTotal += amount;
            console.log('Adding to fabric total:', val, '=', amount, 'Running total:', grandTotal);
        });
        
        console.log('Final fabric grand total:', grandTotal);
        
        // Update the fabric grand total display
        $('.fabric-grand-total').html('Rs. ' + grandTotal.toFixed(2));
        
        // Update combined total if the element exists
        updateCombinedTotal();
        
        return grandTotal;
    };
    
    // Function to update the combined total
    function updateCombinedTotal() {
        const productTotal = parseFloat($('.product-grand-total').text().replace('Rs. ', '')) || 0;
        const fabricTotal = parseFloat($('.fabric-grand-total').text().replace('Rs. ', '')) || 0;
        const combinedTotal = productTotal + fabricTotal;
        
        console.log('Combined total calculation:', productTotal, '+', fabricTotal, '=', combinedTotal);
        
        // Update the combined grand total if the element exists
        if ($('.combined-grand-total').length > 0) {
            $('.combined-grand-total').text('Rs. ' + combinedTotal.toFixed(2));
        }
    }
    
    // Calculate product row totals when price or quantity changes
    $(document).on('input', 'input[name="product_price[]"], input[name="product_quantity[]"]', function() {
        const row = $(this).closest('tr');
        calculateProductRow(row);
    });
    
    // Calculate fabric row totals when dimensions, price, or quantity changes
    $(document).on('input', 'input[name="fabric_height[]"], input[name="fabric_breadth[]"], input[name="fabric_price_per_sqft[]"], input[name="fabric_quantity[]"]', function() {
        const row = $(this).closest('tr');
        calculateFabricRow(row);
    });
    
    // Calculate product row total
    window.calculateProductRow = function(row) {
        const quantity = parseInt($(row).find('input[name="product_quantity[]"]').val()) || 1;
        const priceField = $(row).find('input[name="product_price[]"]');
        const totalField = $(row).find('input[name="product_total_price[]"]');
        
        if (priceField.length && totalField.length) {
            const price = parseFloat(priceField.val()) || 0;
            const total = (price * quantity).toFixed(2);
            
            console.log('Product calculation:', price, 'x', quantity, '=', total);
            
            // Update the total field
            totalField.val('Rs. ' + total);
            
            // Highlight the updated field
            totalField.addClass('bg-light-yellow');
            setTimeout(() => {
                totalField.removeClass('bg-light-yellow');
            }, 300);
            
            // Update the grand total
            calculateProductGrandTotal();
        }
    };
    
    // Calculate fabric row total
    window.calculateFabricRow = function(row) {
        const height = parseFloat($(row).find('input[name="fabric_height[]"]').val()) || 0;
        const breadth = parseFloat($(row).find('input[name="fabric_breadth[]"]').val()) || 0;
        const pricePerSqFt = parseFloat($(row).find('input[name="fabric_price_per_sqft[]"]').val()) || 0;
        const quantity = parseInt($(row).find('input[name="fabric_quantity[]"]').val()) || 1;
        const sqFtField = $(row).find('input[name="fabric_sqft[]"]');
        const totalField = $(row).find('input[name="fabric_total_price[]"]');
        
        if (sqFtField.length && totalField.length) {
            const sqFt = (height * breadth).toFixed(2);
            const totalSqFt = (sqFt * quantity).toFixed(2);
            const total = (sqFt * pricePerSqFt * quantity).toFixed(2);
            
            console.log('Fabric calculation:', height, 'x', breadth, '=', sqFt, 'sqft x', pricePerSqFt, 'x', quantity, '=', total);
            
            // Update the sqft field
            sqFtField.val(sqFt);
            
            // Update the total field
            totalField.val('Rs. ' + total);
            
            // Highlight the updated fields
            sqFtField.addClass('bg-light-yellow');
            totalField.addClass('bg-light-yellow');
            setTimeout(() => {
                sqFtField.removeClass('bg-light-yellow');
                totalField.removeClass('bg-light-yellow');
            }, 300);
            
            // Update the grand total
            calculateFabricGrandTotal();
        }
    };
    
    // Initial calculation on page load
    function initializeCalculations() {
        console.log('Initializing calculations');
        
        // Calculate all product rows
        $('input[name="product_total_price[]"]').each(function() {
            const row = $(this).closest('tr');
            calculateProductRow(row);
        });
        
        // Calculate all fabric rows
        $('input[name="fabric_total_price[]"]').each(function() {
            const row = $(this).closest('tr');
            calculateFabricRow(row);
        });
        
        // Calculate grand totals
        calculateProductGrandTotal();
        calculateFabricGrandTotal();
    }
    
    // Run initial calculations
    initializeCalculations();
    
    console.log('Invoice calculations script initialized');
});