/**
 * Menu Management JavaScript
 * Handles menu form display and filtering functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeMenuManagement();
});

function initializeMenuManagement() {
    // Auto-hide form after page load if there's no error
    const urlParams = new URLSearchParams(window.location.search);
    if (!urlParams.has('error')) {
        hideAddMenuForm();
    }
}

function showAddMenuForm() {
    document.getElementById('add-menu-form').style.display = 'block';
    document.getElementById('menu-name').focus();
}

function hideAddMenuForm() {
    document.getElementById('add-menu-form').style.display = 'none';
    // Reset form when hiding
    document.getElementById('menuForm').reset();
}

// Filter Functions
function applyFilters() {
    const categoryFilter = document.getElementById('category-filter').value.toLowerCase();
    const statusFilter = document.getElementById('status-filter').value;
    const searchFilter = document.getElementById('search-filter').value.toLowerCase();
    
    const tableRows = document.querySelectorAll('.table tbody tr');
    let visibleCount = 0;
    
    tableRows.forEach(row => {
        let shouldShow = true;
        
        // Category filter
        if (categoryFilter && shouldShow) {
            const categoryCell = row.cells[2]; // Category column
            if (categoryCell && !categoryCell.textContent.toLowerCase().includes(categoryFilter)) {
                shouldShow = false;
            }
        }
        
        // Status filter
        if (statusFilter !== '' && shouldShow) {
            const statusCell = row.cells[6]; // Availability column
            const isAvailable = statusCell && statusCell.textContent.includes('Available');
            if (statusFilter === '1' && !isAvailable) {
                shouldShow = false;
            } else if (statusFilter === '0' && isAvailable) {
                shouldShow = false;
            }
        }
        
        // Search filter
        if (searchFilter && shouldShow) {
            const nameCell = row.cells[1]; // Name column
            const descCell = row.cells[3]; // Description column
            const nameText = nameCell ? nameCell.textContent.toLowerCase() : '';
            const descText = descCell ? descCell.textContent.toLowerCase() : '';
            
            if (!nameText.includes(searchFilter) && !descText.includes(searchFilter)) {
                shouldShow = false;
            }
        }
        
        if (shouldShow) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update results count
    updateResultsCount(visibleCount, tableRows.length);
}

function clearFilters() {
    document.getElementById('category-filter').value = '';
    document.getElementById('status-filter').value = '';
    document.getElementById('search-filter').value = '';
    applyFilters();
}

function updateResultsCount(visible, total) {
    let countDisplay = document.getElementById('results-count');
    if (!countDisplay) {
        countDisplay = document.createElement('div');
        countDisplay.id = 'results-count';
        countDisplay.className = 'results-count';
        document.querySelector('.table-container').insertBefore(countDisplay, document.querySelector('.table'));
    }
    
    if (visible === total) {
        countDisplay.innerHTML = `<i class="fas fa-list"></i> Showing all ${total} menu items`;
    } else {
        countDisplay.innerHTML = `<i class="fas fa-filter"></i> Showing ${visible} of ${total} menu items`;
    }
}

// Function to handle showing messages (will be called by PHP)
function showMenuMessages(successMessage, errorMessage) {
    if (successMessage && typeof showBootstrapAlert === 'function') {
        showBootstrapAlert(successMessage, 'success', 4000);
    }
    if (errorMessage && typeof showBootstrapAlert === 'function') {
        showBootstrapAlert(errorMessage, 'error', 6000);
    }
}

/**
 * View detailed menu item information
 */
function viewMenuDetails(menuId) {
    // Show loading state
    document.getElementById('menuDetailsContent').innerHTML = `
        <div class="modal-loading">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    // Show modal immediately with loading state
    new bootstrap.Modal(document.getElementById('menuDetailsModal')).show();
    
    fetch(`controller/menu_list.php?action=get_menu_details&menu_id=${menuId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displayMenuDetails(data.menu);
            } else {
                document.getElementById('menuDetailsContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> ${data.message || 'Failed to load menu details'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Menu details error:', error);
            document.getElementById('menuDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Failed to load menu details: ${error.message}
                </div>
            `;
        });
}

/**
 * Display menu details in modal
 */
function displayMenuDetails(menu) {
    const content = `
        <div class="menu-details">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="menu-image">
                        ${menu.image_url ? 
                            `<img src="${menu.image_url}" alt="${menu.name}" class="img-fluid rounded">` :
                            `<div class="no-image-placeholder">
                                <i class="fas fa-utensils"></i>
                                <p>No Image Available</p>
                            </div>`
                        }
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="info-section">
                        <h4 class="menu-title">${menu.name}</h4>
                        <div class="info-item">
                            <strong>Category:</strong> <span class="ms-2 badge bg-secondary">${menu.category}</span>
                        </div>
                        <div class="info-item">
                            <strong>Price:</strong> <span class="ms-2 text-success fw-bold fs-5">$${parseFloat(menu.price).toFixed(2)}</span>
                        </div>
                        <div class="info-item">
                            <strong>Preparation Time:</strong> <span class="ms-2">${menu.preparation_time || 'Not specified'} minutes</span>
                        </div>
                        <div class="info-item">
                            <strong>Availability:</strong> 
                            <span class="ms-2">
                                <span class="badge bg-${menu.is_available == 1 ? 'success' : 'danger'}">
                                    ${menu.is_available == 1 ? 'Available' : 'Not Available'}
                                </span>
                            </span>
                        </div>
                        <div class="info-item">
                            <strong>Created:</strong> <span class="ms-2">${new Date(menu.created_at).toLocaleString()}</span>
                        </div>
                        ${menu.updated_at ? `
                        <div class="info-item">
                            <strong>Last Updated:</strong> <span class="ms-2">${new Date(menu.updated_at).toLocaleString()}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
            
            ${menu.description ? `
            <div class="mb-3">
                <h6 class="section-title"><i class="fas fa-info-circle"></i> Description</h6>
                <div class="description-text">
                    ${menu.description}
                </div>
            </div>
            ` : ''}
            
            ${menu.ingredients ? `
            <div class="mb-3">
                <h6 class="section-title"><i class="fas fa-list"></i> Ingredients</h6>
                <div class="ingredients-list">
                    ${menu.ingredients.split(',').map(ingredient => 
                        `<span class="badge bg-light text-dark me-1 mb-1">${ingredient.trim()}</span>`
                    ).join('')}
                </div>
            </div>
            ` : ''}
        </div>
    `;
    
    document.getElementById('menuDetailsContent').innerHTML = content;
}