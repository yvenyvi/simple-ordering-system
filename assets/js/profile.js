/**
 * Profile Page JavaScript
 * Handles form validation and user experience enhancements
 */

document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.querySelector('.profile-edit-form');
    
    if (profileForm) {
        // Phone number formatting
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length >= 6) {
                    value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
                } else if (value.length >= 3) {
                    value = value.replace(/(\d{3})(\d{0,3})/, '($1) $2');
                }
                e.target.value = value;
            });
        }

        // ZIP code validation
        const zipInput = document.getElementById('zip_code');
        if (zipInput) {
            zipInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 5) {
                    value = value.substring(0, 5);
                }
                e.target.value = value;
            });
        }

        // Form validation before submit
        profileForm.addEventListener('submit', function(e) {
            const action = e.submitter ? e.submitter.value : 'update';
            
            if (action === 'update') {
                const firstName = document.getElementById('first_name').value.trim();
                const lastName = document.getElementById('last_name').value.trim();
                const email = document.getElementById('email').value.trim();
                
                // Basic validation
                if (!firstName || !lastName || !email) {
                    e.preventDefault();
                    showAlert('Please fill in all required fields (First Name, Last Name, Email).', 'error');
                    return;
                }
                
                // Email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    showAlert('Please enter a valid email address.', 'error');
                    return;
                }
                
                // Phone validation (if provided)
                const phone = document.getElementById('phone').value.trim();
                if (phone) {
                    const phoneRegex = /^\(\d{3}\)\s\d{3}-\d{4}$/;
                    if (!phoneRegex.test(phone)) {
                        e.preventDefault();
                        showAlert('Please enter a valid phone number in format: (123) 456-7890', 'error');
                        return;
                    }
                }
                
                // Show loading state
                const submitBtn = profileForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                    submitBtn.disabled = true;
                }
            }
        });
    }

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 5000);
    });
});

/**
 * Show alert message
 */
function showAlert(message, type = 'error') {
    const existingAlert = document.querySelector('.alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'}"></i>
        ${message}
    `;
    
    const profileContent = document.querySelector('.profile-content');
    if (profileContent) {
        profileContent.insertBefore(alertDiv, profileContent.firstChild);
    }
}

/**
 * Confirm cancellation of edit mode
 */
function confirmCancel() {
    return confirm('Are you sure you want to cancel? Any unsaved changes will be lost.');
}