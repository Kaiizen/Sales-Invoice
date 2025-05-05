// Add Row Fix Script
// This script is loaded after all other scripts to ensure it runs last

// Define global functions for adding rows
window.addProductRow = function() {
    console.log('Product Add Row function called');
    
    // Get the first product row
    var productTable = document.getElementById('product-table');
    var firstRow = productTable.querySelector('tbody tr:first-child');
    
    if (firstRow) {
        // Clone the row
        var newRow = firstRow.cloneNode(true);
        
        // Clear inputs
        newRow.querySelectorAll('input').forEach(function(input) {
            input.value = '';
        });
        
        newRow.querySelectorAll('select').forEach(function(select) {
            select.selectedIndex = 0;
        });
        
        // Set quantity to 1
        var quantityInput = newRow.querySelector('input[name="product_quantity[]"]');
        if (quantityInput) {
            quantityInput.value = '1';
        }
        
        // Insert before the button row
        var buttonRow = productTable.querySelector('.add-row-container');
        if (buttonRow) {
            buttonRow.parentNode.insertBefore(newRow, buttonRow);
        } else {
            // Fallback: append to tbody
            productTable.querySelector('tbody').appendChild(newRow);
        }
        
        return newRow;
    } else {
        console.error('Could not find the first row to clone');
        return null;
    }
};

window.addFabricRow = function() {
    console.log('Fabric Add Row function called');
    
    // Get the first fabric row
    var fabricTable = document.getElementById('fabric-table');
    var firstRow = fabricTable.querySelector('tbody tr:first-child');
    
    if (firstRow) {
        // Clone the row
        var newRow = firstRow.cloneNode(true);
        
        // Clear inputs
        newRow.querySelectorAll('input').forEach(function(input) {
            input.value = '';
        });
        
        newRow.querySelectorAll('select').forEach(function(select) {
            select.selectedIndex = 0;
        });
        
        // Set quantity to 1
        var quantityInput = newRow.querySelector('input[name="fabric_quantity[]"]');
        if (quantityInput) {
            quantityInput.value = '1';
        }
        
        // Insert before the button row
        var buttonRow = fabricTable.querySelector('.add-row-container');
        if (buttonRow) {
            buttonRow.parentNode.insertBefore(newRow, buttonRow);
        } else {
            // Fallback: append to tbody
            fabricTable.querySelector('tbody').appendChild(newRow);
        }
        
        return newRow;
    } else {
        console.error('Could not find the first row to clone');
        return null;
    }
};

document.addEventListener('DOMContentLoaded', function() {
    console.log('Add Row Fix Script loaded');
    
    // Wait a bit to ensure all other scripts have run
    setTimeout(function() {
        // Direct implementation for Product Add Row
        document.getElementById('addProductRowBtn').addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Product Add Row button clicked');
            
            var newRow = window.addProductRow();
            if (newRow) {
                alert('New product row added successfully!');
            } else {
                alert('Could not find the first row to clone');
            }
        });
        
        // Direct implementation for Fabric Add Row
        document.getElementById('addFabricRowBtn').addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Fabric Add Row button clicked');
            
            var newRow = window.addFabricRow();
            if (newRow) {
                alert('New fabric row added successfully!');
            } else {
                alert('Could not find the first row to clone');
            }
        });
        
        console.log('Add Row Fix Script initialized');
    }, 1000); // Wait 1 second to ensure all other scripts have run
});