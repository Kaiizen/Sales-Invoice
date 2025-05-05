// Function to show success message
function showSuccessMessage() {
    // Show an alert
    alert("ORDER SUCCESSFULLY CREATED!");
    
    // Create a Bootstrap modal for the success message
    const modalHtml = `
        <div class="modal fade show" id="orderSuccessModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false" style="display: block; padding-right: 17px; background-color: rgba(0,0,0,0.5); z-index: 9999;">
            <div class="modal-dialog modal-lg" role="document" style="margin-top: 100px;">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="successModalLabel"><i class="fa fa-check-circle"></i> ORDER SUCCESSFULLY CREATED!</h5>
                    </div>
                    <div class="modal-body text-center">
                        <i class="fa fa-check-circle text-success" style="font-size: 100px;"></i>
                        <h2 class="mt-4 mb-3">Order Created Successfully!</h2>
                        <p class="mb-4" style="font-size: 20px;">Redirecting to orders list in <span id="countdown-timer">5</span> seconds...</p>
                        <div class="progress mb-4" style="height: 25px;">
                            <div id="countdown-progress" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Append the modal to the body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show the modal
    const modal = document.getElementById('orderSuccessModal');
    if (modal) {
        modal.style.display = 'block';
        document.body.classList.add('modal-open');
    }
    
    // Start countdown for redirect
    let count = 5;
    const countdownInterval = setInterval(function() {
        count--;
        
        // Update the countdown display
        const countdownElement = document.getElementById('countdown-timer');
        if (countdownElement) {
            countdownElement.textContent = count;
        }
        
        // Update the progress bar
        const progressBar = document.getElementById('countdown-progress');
        if (progressBar) {
            progressBar.style.width = (count/5 * 100) + '%';
        }
        
        // When countdown reaches zero, redirect
        if (count <= 0) {
            clearInterval(countdownInterval);
            
            // Redirect to the orders index page
            window.location.href = '/custom-orders'; // Go to the orders list
        }
    }, 1000);
}

// Custom Order Form Submission Handler
document.addEventListener('DOMContentLoaded', function() {
    console.log('Custom order submit handler loaded');
    
    // Get the form element
    const form = document.getElementById('custom-order-form');
    
    if (form) {
        // Override the form submission
        form.addEventListener('submit', function(event) {
            // Prevent the default form submission
            event.preventDefault();
            
            console.log('Form submission intercepted');
            
            // Check if customer is selected (only required validation)
            const customerId = document.getElementById('customer_id').value;
            if (!customerId) {
                alert('Please select a customer before submitting the form.');
                document.getElementById('customer_id').focus();
                return false;
            }
            
            // Make sure fabric height and breadth are not required
            document.querySelectorAll('input[name="fabric_height[]"], input[name="fabric_breadth[]"]').forEach(function(input) {
                input.required = false;
            });
            
            // Prepare form data - ensure hidden fields are created
            if (typeof prepareFormData === 'function') {
                console.log('Calling prepareFormData function');
                const result = prepareFormData();
                if (result === false) {
                    console.log('Form preparation failed');
                    return false;
                }
                console.log('Form preparation successful');
            } else {
                console.warn('prepareFormData function not found');
            }
            
            // Debug log all form data
            const formData = new FormData(form);
            console.log('Form data before submission:');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
            
            // Show loading state
            const submitButton = document.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Creating Order...';
                submitButton.disabled = true;
            }
            
            // Show success message immediately
            showSuccessMessage();
            
            // Submit the form after a delay to allow the success message to be shown
            setTimeout(function() {
                form.submit();
            }, 500);
            
            return false;
        });
    }
    
    // Check if there's a success parameter in the URL
    if (window.location.search.includes('success=1')) {
        // Show success message
        showSuccessMessage();
    }
});