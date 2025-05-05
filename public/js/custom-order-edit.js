/**
 * Custom Order Edit JavaScript
 * 
 * This file handles all functionality related to editing custom orders:
 * - Populating the form with original order details
 * - Highlighting pre-populated fields
 * - Recalculating totals
 * - Handling status updates
 */

document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on the edit page
    const editForm = document.querySelector('form[action*="custom-orders"][action*="update"]');
    if (!editForm) {
        return;
    }
    
    // Add a notification that we're in edit mode
    const notification = document.createElement('div');
    notification.className = 'edit-mode-notification alert alert-info alert-dismissible fade show';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="mr-3">
                <i class="fa fa-pencil-square-o fa-3x text-primary"></i>
            </div>
            <div>
                <h4 class="alert-heading"><strong>Edit Mode</strong></h4>
                <p class="mb-0">You are editing this order. All original details have been loaded automatically.</p>
                <p class="mb-0 text-muted small">Make your changes and click "Update Order" to save.</p>
            </div>
        </div>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    `;
    
    // Insert at the top of the form
    editForm.insertBefore(notification, editForm.firstChild);
    
    // Add a visual indicator to the form
    editForm.classList.add('border', 'border-info', 'p-3', 'rounded');
    
    // Check if we're in edit mode with order data
    if (typeof window.isEditing !== 'undefined' && window.isEditing === true) {
        // Populate customer and order details
        populateCustomerAndOrderDetails();
        
        // Populate product and fabric items
        populateOrderItems();
        
        // Recalculate all totals
        setTimeout(recalculateAllTotals, 1000);
    }
    
    // Highlight all fields to show they're pre-populated
    setTimeout(highlightFields, 500);
    
    // Add event listener for status update button
    setupStatusUpdateButton();
    
    // Add event listener for form submission
    editForm.addEventListener('submit', function(e) {
        // Ensure all calculations are up to date before submission
        recalculateAllTotals();
    });
    
    /**
     * Highlight pre-populated fields
     */
    function highlightFields() {
        // Get all form fields
        const fields = editForm.querySelectorAll('input, select, textarea');
        let populatedCount = 0;
        
        // Add a sequential delay to each field for a wave effect
        fields.forEach((field, index) => {
            if (field.value && !field.classList.contains('btn') && field.type !== 'hidden') {
                populatedCount++;
                
                // Add a small delay for each field to create a wave effect
                setTimeout(() => {
                    // Add highlight class
                    field.classList.add('bg-light-yellow');
                    
                    // Add a label if it's a significant field
                    if (['customer_id', 'status', 'total_price'].includes(field.id)) {
                        const fieldLabel = field.closest('.form-group').querySelector('label');
                        if (fieldLabel) {
                            fieldLabel.innerHTML += ' <span class="badge badge-success">Pre-filled</span>';
                        }
                    }
                    
                    // Add a data attribute to store the original value
                    field.dataset.originalValue = field.value;
                    
                    // Add a border to indicate pre-populated field
                    field.style.borderLeft = '3px solid #28a745';
                    
                    // Remove highlight after a delay but keep the border
                    setTimeout(() => {
                        field.classList.remove('bg-light-yellow');
                    }, 1500);
                    
                    // Add change event listener to highlight changes
                    field.addEventListener('change', function() {
                        if (this.value !== this.dataset.originalValue) {
                            this.style.borderLeft = '3px solid #dc3545'; // Changed value
                            this.classList.add('highlight-animation');
                            setTimeout(() => {
                                this.classList.remove('highlight-animation');
                            }, 1000);
                        } else {
                            this.style.borderLeft = '3px solid #28a745'; // Back to original value
                        }
                    });
                }, index * 100); // 100ms delay between each field
            }
        });
        
        // Add a summary message
        const summaryMsg = document.createElement('div');
        summaryMsg.className = 'alert alert-success mt-3';
        summaryMsg.innerHTML = `
            <i class="fa fa-check-circle"></i>
            <strong>${populatedCount} fields</strong> have been automatically populated with the original order details.
        `;
        
        // Insert after the notification
        const notification = editForm.querySelector('.edit-mode-notification');
        if (notification) {
            notification.parentNode.insertBefore(summaryMsg, notification.nextSibling);
            
            // Remove the summary message after 5 seconds
            setTimeout(() => {
                summaryMsg.classList.add('fade');
                setTimeout(() => {
                    if (summaryMsg.parentNode) {
                        summaryMsg.parentNode.removeChild(summaryMsg);
                    }
                }, 500);
            }, 5000);
        }
    }
    
    /**
     * Setup the status update button
     */
    function setupStatusUpdateButton() {
        const updateStatusBtn = document.getElementById('updateStatusBtn');
        
        if (updateStatusBtn) {
            // Add visual indicator that this button works independently
            updateStatusBtn.classList.add('pulse-animation');
            updateStatusBtn.style.boxShadow = '0 0 10px rgba(0, 123, 255, 0.5)';
            
            // Add tooltip
            updateStatusBtn.setAttribute('title', 'Update status without saving the entire form');
            
            updateStatusBtn.addEventListener('click', function() {
                const statusSelect = document.getElementById('status');
                const orderId = window.location.pathname.split('/').pop();
                
                if (!statusSelect || !orderId) {
                    return;
                }
                
                const newStatus = statusSelect.value;
                
                // Show loading state
                updateStatusBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Updating...';
                updateStatusBtn.disabled = true;
                
                // Send AJAX request to update status
                const csrfToken = document.querySelector('input[name="_token"]').value;
                
                fetch(`/custom-orders/${orderId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus })
                })
                .then(response => response.json())
                .then(data => {
                    // Show success message
                    const successMsg = document.createElement('div');
                    successMsg.className = 'alert alert-success mt-2';
                    successMsg.innerHTML = `
                        <i class="fa fa-check-circle"></i>
                        Status updated to <strong>${newStatus}</strong> successfully!
                    `;
                    
                    // Insert after the status input group
                    const inputGroup = updateStatusBtn.closest('.input-group');
                    inputGroup.parentNode.insertBefore(successMsg, inputGroup.nextSibling);
                    
                    // Remove the success message after 3 seconds
                    setTimeout(() => {
                        successMsg.classList.add('fade');
                        setTimeout(() => {
                            if (successMsg.parentNode) {
                                successMsg.parentNode.removeChild(successMsg);
                            }
                        }, 500);
                    }, 3000);
                    
                    // Reset button state
                    updateStatusBtn.innerHTML = '<i class="fa fa-refresh"></i> Update Status';
                    updateStatusBtn.disabled = false;
                    
                    // Add highlight effect to status select
                    statusSelect.classList.add('bg-light-yellow');
                    setTimeout(() => {
                        statusSelect.classList.remove('bg-light-yellow');
                    }, 1500);
                })
                .catch(error => {
                    // Show error message
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'alert alert-danger mt-2';
                    errorMsg.innerHTML = `
                        <i class="fa fa-exclamation-circle"></i>
                        Error updating status: ${error.message || 'Unknown error'}
                    `;
                    
                    // Insert after the status input group
                    const inputGroup = updateStatusBtn.closest('.input-group');
                    inputGroup.parentNode.insertBefore(errorMsg, inputGroup.nextSibling);
                    
                    // Remove the error message after 5 seconds
                    setTimeout(() => {
                        errorMsg.classList.add('fade');
                        setTimeout(() => {
                            if (errorMsg.parentNode) {
                                errorMsg.parentNode.removeChild(errorMsg);
                            }
                        }, 500);
                    }, 5000);
                    
                    // Reset button state
                    updateStatusBtn.innerHTML = '<i class="fa fa-refresh"></i> Update Status';
                    updateStatusBtn.disabled = false;
                });
            });
        }
    }
    
    /**
     * Populate customer and order details
     */
    function populateCustomerAndOrderDetails() {
        // Set customer
        const customerSelect = document.getElementById('customer_id');
        if (customerSelect && window.orderData.customer_id) {
            customerSelect.value = window.orderData.customer_id;
            
            // Trigger change event to load customer details
            const event = new Event('change');
            customerSelect.dispatchEvent(event);
        }
        
        // Set order date
        const orderDateInput = document.querySelector('input[name="order_date"]');
        if (orderDateInput && window.orderData.created_at) {
            const orderDate = new Date(window.orderData.created_at);
            const formattedDate = orderDate.toISOString().split('T')[0]; // Format as YYYY-MM-DD
            orderDateInput.value = formattedDate;
        }
        
        // Set delivery date
        const deliveryDateInput = document.querySelector('input[name="delivery_date"]');
        if (deliveryDateInput && window.orderData.delivery_date) {
            const deliveryDate = new Date(window.orderData.delivery_date);
            const formattedDate = deliveryDate.toISOString().split('T')[0]; // Format as YYYY-MM-DD
            deliveryDateInput.value = formattedDate;
        }
        
        // Set contact through
        const contactThroughSelect = document.querySelector('select[name="contact_through"]');
        if (contactThroughSelect && window.orderData.contact_through) {
            contactThroughSelect.value = window.orderData.contact_through;
        }
        
        // Set design provided
        if (window.orderData.design_file) {
            const designYesRadio = document.getElementById('design_yes');
            if (designYesRadio) {
                designYesRadio.checked = true;
            }
        } else {
            const designNoRadio = document.getElementById('design_no');
            if (designNoRadio) {
                designNoRadio.checked = true;
            }
        }
        
        // Set payment method
        const paymentMethodSelect = document.querySelector('select[name="payment_method"]');
        if (paymentMethodSelect && window.orderData.payment_method) {
            paymentMethodSelect.value = window.orderData.payment_method;
        }
        
        // Set special instructions
        const specialInstructionsTextarea = document.querySelector('textarea[name="special_instructions"]');
        if (specialInstructionsTextarea && window.orderData.special_instructions) {
            specialInstructionsTextarea.value = window.orderData.special_instructions;
        }
    }
    
    /**
     * Populate product and fabric items
     */
    function populateOrderItems() {
        if (!window.flagDetails || window.flagDetails.length === 0) {
            return;
        }
        
        // Clear existing rows first (except the first one and the add row button)
        $('#product-table tbody tr:not(:first-child):not(.add-row-container)').remove();
        $('#fabric-table tbody tr:not(:first-child):not(.add-row-container)').remove();
        
        // Count product and fabric items
        let productCount = 0;
        let fabricCount = 0;
        
        window.flagDetails.forEach(detail => {
            if (detail.flag_type === 'flag') {
                fabricCount++;
            } else {
                productCount++;
            }
        });
        
        // Add necessary rows for products
        for (let i = 1; i < productCount; i++) {
            addProductRow();
        }
        
        // Add necessary rows for fabrics
        for (let i = 1; i < fabricCount; i++) {
            addFabricRow();
        }
        
        // Get all product and fabric rows
        const productRows = document.querySelectorAll('#product-table tbody tr:not(.add-row-container)');
        const fabricRows = document.querySelectorAll('#fabric-table tbody tr:not(.add-row-container)');
        
        // Populate product rows
        let productIndex = 0;
        window.flagDetails.forEach(detail => {
            if (detail.flag_type !== 'flag' && productIndex < productRows.length) {
                populateProductRow(productRows[productIndex], detail);
                productIndex++;
            }
        });
        
        // Populate fabric rows
        let fabricIndex = 0;
        window.flagDetails.forEach(detail => {
            if (detail.flag_type === 'flag' && fabricIndex < fabricRows.length) {
                populateFabricRow(fabricRows[fabricIndex], detail);
                fabricIndex++;
            }
        });
    }
    
    /**
     * Populate a product row with data
     */
    function populateProductRow(row, detail) {
        // Set product
        const productSelect = row.querySelector('select[name="product_id[]"]');
        if (productSelect && detail.product_id) {
            productSelect.value = detail.product_id;
            
            // Trigger change event to load product details and price
            const event = new Event('change');
            productSelect.dispatchEvent(event);
            
            // Also trigger jQuery change event for good measure
            $(productSelect).trigger('change');
        }
        
        // Set price
        const priceInput = row.querySelector('input[name="product_price[]"]');
        if (priceInput && detail.price_per_square_feet) {
            priceInput.value = detail.price_per_square_feet;
        }
        
        // Set quantity
        const quantityInput = row.querySelector('input[name="product_quantity[]"]');
        if (quantityInput && detail.quantity) {
            quantityInput.value = detail.quantity;
        }
    }
    
    /**
     * Populate a fabric row with data
     */
    function populateFabricRow(row, detail) {
        // Set fabric
        const fabricSelect = row.querySelector('select[name="fabric_id[]"]');
        if (fabricSelect && detail.product_id) {
            fabricSelect.value = detail.product_id;
            
            // Trigger change event to load fabric details and price
            const event = new Event('change');
            fabricSelect.dispatchEvent(event);
            
            // Also trigger jQuery change event for good measure
            $(fabricSelect).trigger('change');
        }
        
        // Set dimensions
        const heightInput = row.querySelector('input[name="fabric_height[]"]');
        if (heightInput && detail.height) {
            heightInput.value = detail.height;
        }
        
        const breadthInput = row.querySelector('input[name="fabric_breadth[]"]');
        if (breadthInput && detail.breadth) {
            breadthInput.value = detail.breadth;
        }
        
        // Set price
        const priceInput = row.querySelector('input[name="fabric_price_per_square_feet[]"]');
        if (priceInput && detail.price_per_square_feet) {
            priceInput.value = detail.price_per_square_feet;
        }
        
        // Set quantity
        const quantityInput = row.querySelector('input[name="fabric_quantity[]"]');
        if (quantityInput && detail.quantity) {
            quantityInput.value = detail.quantity;
        }
        
        // Set stitching
        const stitchingSelect = row.querySelector('select[name="fabric_stitching[]"]');
        if (stitchingSelect && detail.stitching !== undefined) {
            stitchingSelect.value = detail.stitching ? '1' : '0';
        }
    }
    
    /**
     * Recalculate all totals
     */
    function recalculateAllTotals() {
        // Trigger calculation for all product rows
        $('#product-table tbody tr:not(.add-row-container)').each(function() {
            const row = $(this);
            if (typeof calculateProductRow === 'function') {
                calculateProductRow(row);
            }
        });
        
        // Trigger calculation for all fabric rows
        $('#fabric-table tbody tr:not(.add-row-container)').each(function() {
            const row = $(this);
            if (typeof calculateFabricRow === 'function') {
                calculateFabricRow(row);
            } else if (typeof calculateFabricSquareFeetAndTotal === 'function') {
                calculateFabricSquareFeetAndTotal();
            }
        });
        
        // Calculate grand totals
        if (typeof calculateProductGrandTotal === 'function') {
            calculateProductGrandTotal();
        }
        
        if (typeof calculateFabricGrandTotal === 'function') {
            calculateFabricGrandTotal();
        }
        
        if (typeof calculateCombinedTotal === 'function') {
            calculateCombinedTotal();
        }
    }
    
    // Make the recalculateAllTotals function globally available
    window.calculateAllTotals = recalculateAllTotals;
    
    // Helper functions for adding rows
    function addProductRow() {
        const addButton = document.getElementById('addProductRowBtn');
        if (addButton) {
            addButton.click();
        }
    }
    
    function addFabricRow() {
        const addButton = document.getElementById('addFabricRowBtn');
        if (addButton) {
            addButton.click();
        }
    }
});