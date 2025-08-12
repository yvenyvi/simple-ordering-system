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

function showMessage(message, type) {
    // Remove existing messages
    const existingMessages = document.querySelectorAll('.message');
    existingMessages.forEach(msg => msg.remove());
    
    // Create new message
    const messageDiv = document.createElement('div');
    messageDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
    messageDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at the top of the main content
    const adminMain = document.querySelector('.admin-main');
    adminMain.insertBefore(messageDiv, adminMain.firstChild);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (messageDiv.parentNode) {
            messageDiv.remove();
        }
    }, 5000);
}
