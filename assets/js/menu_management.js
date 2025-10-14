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
    
    // Initialize filters
    applyFilters();
}

function showAddMenuForm() {
    document.getElementById('add-menu-form').style.display = 'block';
    document.getElementById('menu-name').focus();
}

function hideAddMenuForm() {
    modalElement.style.display = 'none';
}

// Edit menu item function
function editMenuItem(id) {
    fetch('controller/menu_list.php?action=get_menu_for_edit&menu_id=' + id, {
        method: 'GET'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.menu) {
            const menu = data.menu;
            
            // Populate form fields
            document.getElementById('edit-menu-id').value = menu.menu_id;
            document.getElementById('edit-menu-name').value = menu.name;
            document.getElementById('edit-menu-category').value = menu.category;
            document.getElementById('edit-menu-price').value = menu.price;
            document.getElementById('edit-menu-prep-time').value = menu.preparation_time || '15';
            document.getElementById('edit-menu-description').value = menu.description || '';
            document.getElementById('edit-menu-ingredients').value = menu.ingredients || '';
            document.getElementById('edit-menu-nutritional-info').value = menu.nutritional_info || '';
            document.getElementById('edit-menu-available').checked = menu.is_available == 1;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('editMenuModal'));
            modal.show();
        } else {
            alert('Error loading menu item for editing');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading menu item');
    });
}

// Toggle availability function
function toggleAvailability(id) {
    fetch('controller/menu_list.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=toggle_availability&menu_id=' + id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the table to reflect changes
            location.reload();
        } else {
            alert(data.message || 'Error toggling availability');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error toggling availability');
    });
}

// Save menu changes
document.addEventListener('DOMContentLoaded', function() {
    const saveButton = document.getElementById('saveMenuChanges');
    if (saveButton) {
        saveButton.addEventListener('click', function() {
            const form = document.getElementById('editMenuForm');
            const formData = new FormData(form);
            formData.append('action', 'update_menu');
            
            // Convert checkbox to proper value
            formData.set('is_available', document.getElementById('edit-menu-available').checked ? '1' : '0');
            
            fetch('controller/menu_list.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editMenuModal'));
                    modal.hide();
                    location.reload(); // Reload to show updated data
                } else {
                    alert(data.message || 'Error updating menu item');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating menu item');
            });
        });
    }
});

// Enhanced Filter Functions
function applyFilters() {
    const categoryFilter = document.getElementById('category-filter').value.toLowerCase();
    const statusFilter = document.getElementById('status-filter').value;
    const searchFilter = document.getElementById('search-filter').value.toLowerCase();
    const priceFilter = document.getElementById('price-filter').value;
    
    const tableRows = document.querySelectorAll('.admin-enhanced-table tbody tr');
    let visibleCount = 0;
    
    tableRows.forEach(row => {
        let shouldShow = true;
        
        // Category filter
        if (categoryFilter && shouldShow) {
            const categoryCell = row.querySelector('td:nth-child(3)'); // Category column (adjusted for image column)
            if (categoryCell && !categoryCell.textContent.toLowerCase().includes(categoryFilter)) {
                shouldShow = false;
            }
        }
        
        // Status filter (availability)
        if (statusFilter !== '' && shouldShow) {
            const statusCell = row.querySelector('td:nth-child(6)'); // Availability column
            if (statusCell) {
                const isAvailable = statusCell.textContent.includes('Yes') || statusCell.querySelector('.badge.bg-success');
                if (statusFilter === '1' && !isAvailable) {
                    shouldShow = false;
                } else if (statusFilter === '0' && isAvailable) {
                    shouldShow = false;
                }
            }
        }
        
        // Price filter
        if (priceFilter && shouldShow) {
            const priceCell = row.querySelector('td:nth-child(4) .cell-price'); // Price column
            if (priceCell) {
                const priceText = priceCell.textContent.replace('$', '').replace(',', '');
                const price = parseFloat(priceText);
                
                if (!isNaN(price)) {
                    switch (priceFilter) {
                        case '0-10':
                            if (price > 10) shouldShow = false;
                            break;
                        case '10-20':
                            if (price < 10 || price > 20) shouldShow = false;
                            break;
                        case '20-30':
                            if (price < 20 || price > 30) shouldShow = false;
                            break;
                        case '30+':
                            if (price < 30) shouldShow = false;
                            break;
                    }
                }
            }
        }
        
        // Search filter (name and potentially ingredients/description if available)
        if (searchFilter && shouldShow) {
            const nameCell = row.querySelector('td:nth-child(2)'); // Name column
            const nameText = nameCell ? nameCell.textContent.toLowerCase() : '';
            
            // Check if the search term matches the name
            if (!nameText.includes(searchFilter)) {
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
    document.getElementById('price-filter').value = '';
    applyFilters();
}

function updateResultsCount(visible, total) {
    let countDisplay = document.getElementById('results-count');
    if (!countDisplay) {
        countDisplay = document.createElement('div');
        countDisplay.id = 'results-count';
        countDisplay.className = 'results-count';
        const filterActions = document.querySelector('.filter-actions');
        if (filterActions) {
            filterActions.appendChild(countDisplay);
        } else {
            document.querySelector('.table-container').insertBefore(countDisplay, document.querySelector('.admin-enhanced-table'));
        }
    }
    
    if (visible === total) {
        countDisplay.innerHTML = `<i class="fas fa-list"></i> Showing all ${total} menu items`;
        countDisplay.className = 'results-count text-muted';
    } else {
        countDisplay.innerHTML = `<i class="fas fa-filter"></i> Showing ${visible} of ${total} menu items`;
        countDisplay.className = 'results-count text-primary fw-bold';
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
            
            ${menu.nutritional_info ? `
            <div class="mb-3">
                <h6 class="section-title"><i class="fas fa-chart-pie"></i> Nutritional Information</h6>
                <div class="nutritional-info">
                    ${menu.nutritional_info}
                </div>
            </div>
            ` : ''}
        </div>
    `;
    
    document.getElementById('menuDetailsContent').innerHTML = content;
}