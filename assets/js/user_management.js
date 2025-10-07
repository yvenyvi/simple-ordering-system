/**
 * User Management JavaScript
 * Handles user form display and functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeUserManagement();
});

function initializeUserManagement() {
    // Auto-hide form after page load if there's no error
    const urlParams = new URLSearchParams(window.location.search);
    if (!urlParams.has('error')) {
        hideAddUserForm();
    }
}

function showAddUserForm() {
    document.getElementById('add-user-form').style.display = 'block';
    document.getElementById('user-firstname').focus();
}

function hideAddUserForm() {
    document.getElementById('add-user-form').style.display = 'none';
}