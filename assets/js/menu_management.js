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