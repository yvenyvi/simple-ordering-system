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

/**
 * View user details in modal
 */
function viewUserDetails(userId) {
    const modal = new bootstrap.Modal(document.getElementById('userViewModal'));
    const contentDiv = document.getElementById('userViewContent');
    
    // Show loading spinner
    contentDiv.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    // Fetch user details
    fetch(`controller/user_list.php?action=view&id=${userId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displayUserDetails(data.user);
            } else {
                contentDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Error loading user details: ${data.message || 'Unknown error'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            contentDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Failed to load user details. Please try again.
                </div>
            `;
        });
}

/**
 * Display user details in the modal
 */
function displayUserDetails(user) {
    const contentDiv = document.getElementById('userViewContent');
    
    // Format the date
    const createdDate = user.created_at ? new Date(user.created_at).toLocaleDateString() : 'N/A';
    
    // Handle boolean is_active field properly (can be 1, "1", true, "true", etc.)
    const isActive = (user.is_active == 1 || user.is_active === '1' || user.is_active === true || user.is_active === 'true');
    const statusBadge = isActive ? 
        '<span class="badge bg-success">Active</span>' : 
        '<span class="badge bg-danger">Inactive</span>';
    
    contentDiv.innerHTML = `
        <div class="col-md-6">
            <h6 class="text-primary mb-3"><i class="fas fa-user"></i> Personal Information</h6>
            <div class="detail-item">
                <strong>First Name:</strong>
                <span>${escapeHtml(user.first_name || 'N/A')}</span>
            </div>
            <div class="detail-item">
                <strong>Last Name:</strong>
                <span>${escapeHtml(user.last_name || 'N/A')}</span>
            </div>
            <div class="detail-item">
                <strong>Email:</strong>
                <span>${escapeHtml(user.email || 'N/A')}</span>
            </div>
            <div class="detail-item">
                <strong>Phone:</strong>
                <span>${escapeHtml(user.phone || 'N/A')}</span>
            </div>
            <div class="detail-item">
                <strong>Status:</strong>
                <span>${statusBadge}</span>
            </div>
        </div>
        <div class="col-md-6">
            <h6 class="text-primary mb-3"><i class="fas fa-map-marker-alt"></i> Address Information</h6>
            <div class="detail-item">
                <strong>Address:</strong>
                <span>${escapeHtml(user.address || 'N/A')}</span>
            </div>
            <div class="detail-item">
                <strong>City:</strong>
                <span>${escapeHtml(user.city || 'N/A')}</span>
            </div>
            <div class="detail-item">
                <strong>State:</strong>
                <span>${escapeHtml(user.state || 'N/A')}</span>
            </div>
            <div class="detail-item">
                <strong>ZIP Code:</strong>
                <span>${escapeHtml(user.zip_code || 'N/A')}</span>
            </div>
            <div class="detail-item">
                <strong>Member Since:</strong>
                <span>${createdDate}</span>
            </div>
        </div>
    `;
}

/**
 * Helper function to escape HTML
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Edit user - load user data into edit modal
 */
function editUser(userId) {
    const modal = new bootstrap.Modal(document.getElementById('userEditModal'));
    
    // Clear previous form data
    document.getElementById('editUserForm').reset();
    document.getElementById('edit-user-id').value = userId;
    
    // Fetch user details for editing
    fetch(`controller/user_list.php?action=get_for_edit&id=${userId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                populateEditForm(data.user);
                modal.show();
            } else {
                alert('Error loading user data: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load user data for editing. Please try again.');
        });
}

/**
 * Populate the edit form with user data
 */
function populateEditForm(user) {
    document.getElementById('edit-first-name').value = user.first_name || '';
    document.getElementById('edit-last-name').value = user.last_name || '';
    document.getElementById('edit-email').value = user.email || '';
    document.getElementById('edit-phone').value = user.phone || '';
    document.getElementById('edit-address').value = user.address || '';
    document.getElementById('edit-city').value = user.city || '';
    document.getElementById('edit-state').value = user.state || '';
    document.getElementById('edit-zip-code').value = user.zip_code || '';
    
    // Handle is_active checkbox
    const isActive = (user.is_active == 1 || user.is_active === '1' || user.is_active === true || user.is_active === 'true');
    document.getElementById('edit-is-active').checked = isActive;
}