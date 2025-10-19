/**
 * User Management JavaScript
 * Handles user form display and functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize user management
    initializeUserManagement();
    
    // Initialize results count
    const tableRows = document.querySelectorAll('.admin-enhanced-table tbody tr, table tbody tr');
    updateResultsCount(tableRows.length, tableRows.length);
    
    // Setup edit user form AJAX submission
    const editUserForm = document.getElementById('editUserForm');
    if (editUserForm) {
        editUserForm.addEventListener('submit', handleEditUserSubmit);
    }
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

/**
 * Apply user filters and search
 */
function applyUserFilters() {
    const searchTerm = document.getElementById('search-filter').value.toLowerCase();
    const statusFilter = document.getElementById('status-filter').value;
    const dateFilter = document.getElementById('date-filter').value;
    const locationFilter = document.getElementById('location-filter').value;
    
    const tableRows = document.querySelectorAll('.admin-enhanced-table tbody tr, table tbody tr');
    let visibleCount = 0;
    
    tableRows.forEach((row, index) => {
        let showRow = true;
        
        // Search filter
        if (searchTerm) {
            const searchableText = row.textContent.toLowerCase();
            if (!searchableText.includes(searchTerm)) {
                showRow = false;
            }
        }
        
        // Status filter
        if (statusFilter && showRow) {
            const statusCell = row.querySelector('.cell-boolean');
            if (statusCell) {
                const isActive = statusCell.textContent.trim().toLowerCase() === 'yes';
                if (statusFilter === 'active' && !isActive) {
                    showRow = false;
                } else if (statusFilter === 'inactive' && isActive) {
                    showRow = false;
                }
            }
        }
        
        // Date filter
        if (dateFilter && showRow) {
            const dateCell = row.querySelector('.cell-date');
            if (dateCell) {
                const dateText = dateCell.textContent.trim();
                if (dateText && dateText !== '-' && dateText !== 'N/A') {
                    // Parse the date from the displayed text
                    const rowDate = new Date(dateText);
                    
                    // Check if date is valid
                    if (!isNaN(rowDate.getTime())) {
                        const now = new Date();
                        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                        
                        let dateMatch = false;
                        switch (dateFilter) {
                            case 'today':
                                const rowDateOnly = new Date(rowDate.getFullYear(), rowDate.getMonth(), rowDate.getDate());
                                dateMatch = rowDateOnly.getTime() === today.getTime();
                                break;
                            case 'yesterday':
                                const yesterday = new Date(today);
                                yesterday.setDate(yesterday.getDate() - 1);
                                const rowDateYesterday = new Date(rowDate.getFullYear(), rowDate.getMonth(), rowDate.getDate());
                                dateMatch = rowDateYesterday.getTime() === yesterday.getTime();
                                break;
                            case 'week':
                                const weekAgo = new Date(today);
                                weekAgo.setDate(weekAgo.getDate() - 7);
                                dateMatch = rowDate >= weekAgo;
                                break;
                            case 'month':
                                const monthAgo = new Date(today);
                                monthAgo.setMonth(monthAgo.getMonth() - 1);
                                dateMatch = rowDate >= monthAgo;
                                break;
                            case 'year':
                                const yearAgo = new Date(today);
                                yearAgo.setFullYear(yearAgo.getFullYear() - 1);
                                dateMatch = rowDate >= yearAgo;
                                break;
                        }
                        
                        if (!dateMatch) {
                            showRow = false;
                        }
                    }
                }
            }
        }
        
        // Location filter - simplified approach
        if (locationFilter && showRow) {
            const rowText = row.textContent.toLowerCase();
            // Simple check for presence of address-like content
            const hasAddressInfo = rowText.includes('st ') || rowText.includes('ave ') || 
                                   rowText.includes('rd ') || rowText.includes('blvd ') ||
                                   rowText.includes('dr ') || rowText.includes('ln ') ||
                                   /\d{5}/.test(rowText); // ZIP code pattern
            
            if (locationFilter === 'has-address' && !hasAddressInfo) {
                showRow = false;
            } else if (locationFilter === 'no-address' && hasAddressInfo) {
                showRow = false;
            }
        }
        
        // Show/hide row
        if (showRow) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update results count
    updateResultsCount(visibleCount, tableRows.length);
}

/**
 * Clear all user filters
 */
function clearUserFilters() {
    document.getElementById('search-filter').value = '';
    document.getElementById('status-filter').value = '';
    document.getElementById('date-filter').value = '';
    document.getElementById('location-filter').value = '';
    
    // Show all rows
    const tableRows = document.querySelectorAll('.admin-enhanced-table tbody tr, table tbody tr');
    tableRows.forEach(row => {
        row.style.display = '';
    });
    
    // Update results count
    updateResultsCount(tableRows.length, tableRows.length);
}

/**
 * Update results count display
 */
function updateResultsCount(visible, total) {
    const resultsCount = document.getElementById('results-count');
    if (resultsCount) {
        if (visible === total) {
            resultsCount.textContent = `Showing ${total} users`;
        } else {
            resultsCount.textContent = `Showing ${visible} of ${total} users`;
        }
    }
}

/**
 * Show user management messages using Bootstrap alerts
 */
function showUserMessages(successMessage, errorMessage) {
    if (successMessage && typeof showBootstrapAlert === 'function') {
        showBootstrapAlert(successMessage, 'success', 4000);
    }
    if (errorMessage && typeof showBootstrapAlert === 'function') {
        showBootstrapAlert(errorMessage, 'error', 6000);
    }
}

/**
 * Show informational user messages
 */
function showUserInfoMessage(message, duration = 5000) {
    if (message && typeof showBootstrapAlert === 'function') {
        showBootstrapAlert(message, 'info', duration);
    }
}

/**
 * Handle edit user form submission via AJAX
 */
function handleEditUserSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    
    // Disable submit button during request
    const originalText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    
    fetch('controller/user_list.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Show success message
            showUserMessages(data.message, null);
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('userEditModal'));
            if (modal) {
                modal.hide();
            }
            
            // Reload page to show updated data
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            // Show error message
            showUserMessages(null, data.message || 'Failed to update user');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showUserMessages(null, 'Network error occurred. Please try again.');
    })
    .finally(() => {
        // Re-enable submit button
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
}