// Admin Forms JavaScript - Enhanced User Experience

document.addEventListener('DOMContentLoaded', function() {
    
    // Form validation and enhancement
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        // Add loading state to submit buttons
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('.submit-btn');
            if (submitBtn) {
                submitBtn.value = 'Processing...';
                submitBtn.disabled = true;
                form.classList.add('loading');
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('.form-input[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', validateField);
            input.addEventListener('input', clearErrors);
        });
    });
    
    // Field validation function
    function validateField(e) {
        const field = e.target;
        const value = field.value.trim();
        
        // Remove existing error styling
        clearFieldError(field);
        
        if (field.hasAttribute('required') && !value) {
            showFieldError(field, 'This field is required');
            return false;
        }
        
        // Email validation
        if (field.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                showFieldError(field, 'Please enter a valid email address');
                return false;
            }
        }
        
        // Phone validation
        if (field.type === 'tel' && value) {
            const phoneRegex = /^[\d\s\-\+\(\)]+$/;
            if (!phoneRegex.test(value)) {
                showFieldError(field, 'Please enter a valid phone number');
                return false;
            }
        }
        
        // Price validation
        if (field.type === 'number' && field.name === 'price' && value) {
            if (parseFloat(value) < 0) {
                showFieldError(field, 'Price cannot be negative');
                return false;
            }
        }
        
        return true;
    }
    
    // Show field error
    function showFieldError(field, message) {
        field.style.borderColor = '#e74c3c';
        field.style.backgroundColor = '#fdf2f2';
        
        // Remove existing error message
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
        
        // Add error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.style.color = '#e74c3c';
        errorDiv.style.fontSize = '12px';
        errorDiv.style.marginTop = '5px';
        errorDiv.style.fontWeight = '500';
        errorDiv.textContent = message;
        
        field.parentNode.appendChild(errorDiv);
    }
    
    // Clear field error
    function clearFieldError(field) {
        field.style.borderColor = '#e9ecef';
        field.style.backgroundColor = '#fff';
        
        const errorMsg = field.parentNode.querySelector('.field-error');
        if (errorMsg) {
            errorMsg.remove();
        }
    }
    
    // Clear errors on input
    function clearErrors(e) {
        clearFieldError(e.target);
    }
    
    // Smooth scroll to form
    const addLinks = document.querySelectorAll('a[href*="#"]');
    addLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').split('#')[1];
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Focus on first input
                setTimeout(() => {
                    const firstInput = targetElement.querySelector('.form-input');
                    if (firstInput) {
                        firstInput.focus();
                    }
                }, 500);
            }
        });
    });
    
    // Auto-format price inputs
    const priceInputs = document.querySelectorAll('input[name="price"]');
    priceInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value) {
                this.value = parseFloat(this.value).toFixed(2);
            }
        });
    });
    
    // Auto-capitalize name fields
    const nameInputs = document.querySelectorAll('input[name="first_name"], input[name="last_name"], input[name="name"]');
    nameInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value) {
                this.value = this.value.split(' ')
                    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                    .join(' ');
            }
        });
    });
    
    // Phone number formatting
    const phoneInputs = document.querySelectorAll('input[name="phone"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length >= 6) {
                value = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6, 10);
            } else if (value.length >= 3) {
                value = value.slice(0, 3) + '-' + value.slice(3);
            }
            this.value = value;
        });
    });
    
    // File upload preview
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Remove existing preview
                    const existingPreview = input.parentNode.querySelector('.image-preview');
                    if (existingPreview) {
                        existingPreview.remove();
                    }
                    
                    // Create preview
                    const preview = document.createElement('div');
                    preview.className = 'image-preview';
                    preview.style.marginTop = '10px';
                    preview.innerHTML = `
                        <img src="${e.target.result}" style="max-width: 200px; max-height: 150px; border-radius: 8px; border: 2px solid #e9ecef;" />
                        <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">${file.name}</p>
                    `;
                    
                    input.parentNode.appendChild(preview);
                };
                reader.readAsDataURL(file);
            }
        });
    });
    
    // Add confirmation to delete links
    const deleteLinks = document.querySelectorAll('a[href*="deleteid"]');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const isConfirmed = confirm('Are you sure you want to delete this record? This action cannot be undone.');
            if (isConfirmed) {
                window.location.href = this.href;
            }
        });
    });
    
    // Add success message fade out
    const messages = document.querySelectorAll('.message');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => {
                message.style.display = 'none';
            }, 300);
        }, 5000);
    });
    
});
