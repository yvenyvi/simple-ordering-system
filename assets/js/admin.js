// Admin Panel JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize admin panel
    initAdminPanel();
    loadDashboardStatsSimple();
});

// Initialize admin panel functionality
function initAdminPanel() {
    // No longer needed - navigation now uses separate pages
    // Handle form submissions - remove AJAX form handlers since we're using traditional form submission
}

// Load dashboard statistics using simple counting
function loadDashboardStatsSimple() {
    // Get stats from PHP that's already rendered on page load
    try {
        // Count menu items from table if it exists
        const menuRows = document.querySelectorAll('#menu-table-body tr');
        if (menuRows.length > 0 && !menuRows[0].textContent.includes('No menu items found')) {
            document.getElementById('total-menu-items').textContent = menuRows.length;
        }
        
        // Count users from table if it exists  
        const userRows = document.querySelectorAll('#users-table-body tr');
        if (userRows.length > 0 && !userRows[0].textContent.includes('No users found')) {
            document.getElementById('total-users').textContent = userRows.length;
        }
    } catch (error) {
        console.log('Stats will be loaded from server-side PHP');
    }
}

// Menu Management Functions
function showAddMenuForm() {
    document.getElementById('add-menu-form').style.display = 'block';
    document.getElementById('menu-name').focus();
}

function hideAddMenuForm() {
    document.getElementById('add-menu-form').style.display = 'none';
}

// User Management Functions  
function showAddUserForm() {
    document.getElementById('add-user-form').style.display = 'block';
    document.getElementById('user-firstname').focus();
}

function hideAddUserForm() {
    document.getElementById('add-user-form').style.display = 'none';
}

// Utility Functions
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

// Bootstrap Alert Functions (for simple notifications)
function showBootstrapAlert(message, type, duration = 5000) {
    // Remove existing messages
    const existingMessages = document.querySelectorAll('.bootstrap-alert');
    existingMessages.forEach(msg => msg.remove());
    
    // Create new Bootstrap alert
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show bootstrap-alert`;
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '80px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.maxWidth = '400px';
    alertDiv.innerHTML = `
        <i class="fas fa-${getAlertIcon(type)} me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add to page
    document.body.appendChild(alertDiv);
    
    // Auto-remove after specified duration
    if (duration > 0) {
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, duration);
    }
}

function getAlertIcon(type) {
    const icons = {
        'success': 'check-circle',
        'error': 'exclamation-circle',
        'danger': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// SweetAlert Functions (for confirmations and decisions)
function showSweetConfirmation(title, text, confirmCallback, options = {}) {
    const defaultOptions = {
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, proceed!',
        cancelButtonText: 'Cancel'
    };
    
    const finalOptions = { ...defaultOptions, ...options };
    
    Swal.fire(finalOptions).then((result) => {
        if (result.isConfirmed && typeof confirmCallback === 'function') {
            confirmCallback();
        }
    });
}

function showSweetSuccess(title, text, callback = null) {
    Swal.fire({
        icon: 'success',
        title: title,
        text: text,
        timer: 3000,
        showConfirmButton: false
    }).then(() => {
        if (typeof callback === 'function') {
            callback();
        }
    });
}

function showSweetError(title, text) {
    Swal.fire({
        icon: 'error',
        title: title,
        text: text
    });
}

// Legacy function for backward compatibility
function showMessage(message, type) {
    // Use Bootstrap alerts for simple notifications
    showBootstrapAlert(message, type);
}
