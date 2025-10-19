/**
 * Event Management JavaScript
 * Handles event form display and validation functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeEventManagement();
    setupEventValidation();
});

function initializeEventManagement() {
    // Auto-hide form after page load if there's no error
    const urlParams = new URLSearchParams(window.location.search);
    if (!urlParams.has('error')) {
        hideAddEventForm();
    }
    
    // Set minimum date to today
    const eventDateInput = document.getElementById('event-date');
    if (eventDateInput) {
        const today = new Date().toISOString().split('T')[0];
        eventDateInput.min = today;
    }
    
    // Initialize filters
    applyEventFilters();
}

function showAddEventForm() {
    document.getElementById('add-event-form').style.display = 'block';
    document.getElementById('event-name').focus();
}

function hideAddEventForm() {
    document.getElementById('add-event-form').style.display = 'none';
    // Reset form when hiding
    document.getElementById('eventForm').reset();
}

function setupEventValidation() {
    // Form validation
    const eventForm = document.getElementById('eventForm');
    if (eventForm) {
        eventForm.addEventListener('submit', function(e) {
            const eventDate = new Date(document.getElementById('event-date').value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (eventDate < today) {
                e.preventDefault();
                if (typeof showBootstrapAlert === 'function') {
                    showBootstrapAlert('Event date cannot be in the past. Please select a valid date.', 'error', 5000);
                }
                return false;
            }
        });
    }
}

// Function to handle showing messages (will be called by PHP)
function showEventMessages(successMessage, errorMessage) {
    if (successMessage && typeof showBootstrapAlert === 'function') {
        showBootstrapAlert(successMessage, 'success', 4000);
    }
    if (errorMessage && typeof showBootstrapAlert === 'function') {
        showBootstrapAlert(errorMessage, 'error', 6000);
    }
}

// Function to handle showing info messages (will be called by PHP)
function showEventInfoMessage(infoMessage) {
    if (infoMessage && typeof showBootstrapAlert === 'function') {
        showBootstrapAlert(infoMessage, 'info', 5000);
    }
}

/**
 * Edit event function
 */
function editEvent(eventId) {
    fetch('controller/event_list.php?action=get_event_for_edit&event_id=' + eventId, {
        method: 'GET'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.event) {
            const event = data.event;
            
            // Populate form fields
            document.getElementById('edit-event-id').value = event.event_id;
            document.getElementById('edit-event-name').value = event.event_name || '';
            document.getElementById('edit-event-type').value = event.event_type || '';
            document.getElementById('edit-event-date').value = event.event_date || '';
            document.getElementById('edit-event-time').value = event.event_time || '';
            document.getElementById('edit-event-description').value = event.description || '';
            document.getElementById('edit-event-location').value = event.location || '';
            document.getElementById('edit-event-capacity').value = event.capacity || '50';
            document.getElementById('edit-event-price').value = event.price || '0.00';
            document.getElementById('edit-contact-email').value = event.contact_email || '';
            document.getElementById('edit-contact-phone').value = event.contact_phone || '';
            document.getElementById('edit-event-requirements').value = event.requirements || '';
            
            // Update disable button text based on current status
            const disableBtn = document.getElementById('disableEventBtn');
            const disableText = document.getElementById('disableEventText');
            if (event.is_active == 1) {
                disableText.textContent = 'Disable Event';
                disableBtn.className = 'btn btn-danger';
                disableBtn.innerHTML = '<i class="fas fa-ban"></i> <span id="disableEventText">Disable Event</span>';
            } else {
                disableText.textContent = 'Enable Event';
                disableBtn.className = 'btn btn-success';
                disableBtn.innerHTML = '<i class="fas fa-check"></i> <span id="disableEventText">Enable Event</span>';
            }
            
            // Show current image if exists
            const imagePreview = document.getElementById('current-image-preview');
            const currentImage = document.getElementById('current-image');
            if (event.image_url) {
                currentImage.src = event.image_url;
                imagePreview.style.display = 'block';
            } else {
                imagePreview.style.display = 'none';
            }
            
            // Set minimum date to today for date validation
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('edit-event-date').min = today;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('editEventModal'));
            modal.show();
        } else {
            if (typeof showBootstrapAlert === 'function') {
                showBootstrapAlert('Error loading event for editing: ' + (data.message || 'Unknown error'), 'error', 5000);
            } else {
                alert('Error loading event for editing');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof showBootstrapAlert === 'function') {
            showBootstrapAlert('Error loading event for editing', 'error', 5000);
        } else {
            alert('Error loading event for editing');
        }
    });
}

/**
 * View event details in modal
 */
function viewEventDetails(eventId) {
    const modal = new bootstrap.Modal(document.getElementById('eventViewModal'));
    const contentDiv = document.getElementById('eventViewContent');
    
    // Show loading spinner
    contentDiv.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    // Fetch event details
    fetch(`controller/event_list.php?action=view&id=${eventId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displayEventDetails(data.event);
            } else {
                contentDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Error loading event details: ${data.message || 'Unknown error'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            contentDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Failed to load event details. Please try again.
                </div>
            `;
        });
}

/**
 * Display event details in the modal
 */
function displayEventDetails(event) {
    const contentDiv = document.getElementById('eventViewContent');
    
    // Format the date and time
    const eventDate = event.event_date ? new Date(event.event_date).toLocaleDateString() : 'N/A';
    const eventTime = event.event_time || 'N/A';
    const createdDate = event.created_at ? new Date(event.created_at).toLocaleDateString() : 'N/A';
    
    // Format event type
    const eventType = event.event_type ? 
        event.event_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A';
    
    // Format price
    const price = event.price && event.price > 0 ? `$${parseFloat(event.price).toFixed(2)}` : 'Free';
    
    // Status badge
    const statusBadge = (event.is_active == 1 || event.is_active === '1' || event.is_active === true || event.is_active === 'true') ? 
        '<span class="badge bg-success">Active</span>' : 
        '<span class="badge bg-danger">Inactive</span>';
    
    contentDiv.innerHTML = `
        <div class="event-details">
            <div class="event-details-grid">
                <div class="event-image-section">
                    <div class="event-image">
                        ${event.image_url ? 
                            `<img src="${escapeHtml(event.image_url)}" alt="${escapeHtml(event.event_name)}" class="img-fluid">` :
                            `<div class="no-image-placeholder">
                                <i class="fas fa-calendar-alt"></i>
                                <p>No Image Available</p>
                            </div>`
                        }
                    </div>
                </div>
                <div class="event-info-section">
                    <h4 class="event-title">${escapeHtml(event.event_name || 'Untitled Event')}</h4>
                    
                    <div class="info-item">
                        <strong>Event Type:</strong> <span class="ms-2 badge bg-secondary">${escapeHtml(eventType)}</span>
                    </div>
                    <div class="info-item">
                        <strong>Date:</strong> <span class="ms-2">${eventDate}</span>
                    </div>
                    <div class="info-item">
                        <strong>Time:</strong> <span class="ms-2">${escapeHtml(eventTime)}</span>
                    </div>
                    <div class="info-item">
                        <strong>Price:</strong> <span class="ms-2 text-success fw-bold fs-5">${price}</span>
                    </div>
                    <div class="info-item">
                        <strong>Location:</strong> <span class="ms-2">${escapeHtml(event.location || 'N/A')}</span>
                    </div>
                    <div class="info-item">
                        <strong>Capacity:</strong> <span class="ms-2">${event.capacity || 'N/A'} people</span>
                    </div>
                    <div class="info-item">
                        <strong>Status:</strong> 
                        <span class="ms-2">${statusBadge}</span>
                    </div>
                    <div class="info-item">
                        <strong>Created:</strong> <span class="ms-2">${createdDate}</span>
                    </div>
                </div>
            </div>
            
            ${event.description ? `
            <h6 class="section-title"><i class="fas fa-align-left"></i> Description</h6>
            <div class="description-text">
                ${escapeHtml(event.description).replace(/\n/g, '<br>')}
            </div>
            ` : ''}
            
            ${event.requirements ? `
            <h6 class="section-title"><i class="fas fa-exclamation-triangle"></i> Requirements</h6>
            <div class="description-text">
                ${escapeHtml(event.requirements).replace(/\n/g, '<br>')}
            </div>
            ` : ''}
            
            ${(event.contact_email || event.contact_phone) ? `
            <h6 class="section-title"><i class="fas fa-address-book"></i> Contact Information</h6>
            ${event.contact_email ? `
            <div class="info-item">
                <strong>Email:</strong> <span class="ms-2"><a href="mailto:${escapeHtml(event.contact_email)}">${escapeHtml(event.contact_email)}</a></span>
            </div>
            ` : ''}
            ${event.contact_phone ? `
            <div class="info-item">
                <strong>Phone:</strong> <span class="ms-2"><a href="tel:${escapeHtml(event.contact_phone)}">${escapeHtml(event.contact_phone)}</a></span>
            </div>
            ` : ''}
            ` : ''}
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
 * Enhanced Filter Functions for Events
 */
function applyEventFilters() {
    const eventTypeFilter = document.getElementById('event-type-filter').value.toLowerCase();
    const statusFilter = document.getElementById('status-filter').value;
    const searchFilter = document.getElementById('search-filter').value.toLowerCase();
    const dateFilter = document.getElementById('date-filter').value;
    
    const tableRows = document.querySelectorAll('.admin-enhanced-table tbody tr');
    let visibleCount = 0;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    tableRows.forEach(row => {
        let shouldShow = true;
        const cells = row.querySelectorAll('td');
        
        // Get all cell text for robust searching
        const rowText = Array.from(cells).map(cell => cell.textContent.toLowerCase()).join(' ');
        
        // Event Type filter - search for event type in any cell
        if (eventTypeFilter && shouldShow) {
            // Look for event type in all cells
            let typeFound = false;
            cells.forEach(cell => {
                const cellText = cell.textContent.toLowerCase();
                if (cellText.includes(eventTypeFilter)) {
                    typeFound = true;
                }
            });
            if (!typeFound) {
                shouldShow = false;
            }
        }
        
        // Status filter (active/inactive) - look for boolean values
        if (statusFilter !== '' && shouldShow) {
            let statusFound = false;
            cells.forEach(cell => {
                const booleanElement = cell.querySelector('.cell-boolean');
                if (booleanElement) {
                    const isActive = booleanElement.classList.contains('true') || cell.textContent.includes('Yes');
                    if ((statusFilter === '1' && isActive) || (statusFilter === '0' && !isActive)) {
                        statusFound = true;
                    }
                }
            });
            if (!statusFound) {
                shouldShow = false;
            }
        }
        
        // Date filter - look for date cells
        if (dateFilter && shouldShow) {
            let dateMatches = false;
            
            cells.forEach(cell => {
                const dateElement = cell.querySelector('.cell-date');
                if (dateElement) {
                    const eventDateText = dateElement.textContent.trim();
                    let eventDate;
                    
                    // Try different date parsing approaches
                    if (eventDateText.match(/^\w{3}\s\d{1,2},\s\d{4}$/)) {
                        // Format: "Oct 17, 2025"
                        eventDate = new Date(eventDateText);
                    } else if (eventDateText.match(/^\d{4}-\d{2}-\d{2}$/)) {
                        // Format: "2025-10-17"
                        eventDate = new Date(eventDateText);
                    } else {
                        // Fallback to direct parsing
                        eventDate = new Date(eventDateText);
                    }
                    
                    if (!isNaN(eventDate.getTime())) {
                        eventDate.setHours(0, 0, 0, 0);
                        
                        switch (dateFilter) {
                            case 'upcoming':
                                if (eventDate >= today) dateMatches = true;
                                break;
                            case 'past':
                                if (eventDate < today) dateMatches = true;
                                break;
                            case 'today':
                                if (eventDate.getTime() === today.getTime()) dateMatches = true;
                                break;
                            case 'this-week':
                                const nextWeek = new Date(today);
                                nextWeek.setDate(today.getDate() + 7);
                                if (eventDate >= today && eventDate <= nextWeek) dateMatches = true;
                                break;
                            case 'this-month':
                                const currentMonth = today.getMonth();
                                const currentYear = today.getFullYear();
                                if (eventDate.getMonth() === currentMonth && eventDate.getFullYear() === currentYear) {
                                    dateMatches = true;
                                }
                                break;
                        }
                    }
                }
            });
            
            if (!dateMatches) {
                shouldShow = false;
            }
        }
        
        // Search filter - search across all text content
        if (searchFilter && shouldShow) {
            if (!rowText.includes(searchFilter)) {
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
    updateEventResultsCount(visibleCount, tableRows.length);
}

function clearEventFilters() {
    document.getElementById('event-type-filter').value = '';
    document.getElementById('status-filter').value = '';
    document.getElementById('search-filter').value = '';
    document.getElementById('date-filter').value = '';
    applyEventFilters();
}

function updateEventResultsCount(visible, total) {
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
        countDisplay.innerHTML = `<i class="fas fa-calendar-alt"></i> Showing all ${total} events`;
        countDisplay.className = 'results-count text-muted';
    } else {
        countDisplay.innerHTML = `<i class="fas fa-filter"></i> Showing ${visible} of ${total} events`;
        countDisplay.className = 'results-count text-primary fw-bold';
    }
}

/**
 * Save event changes functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    const saveButton = document.getElementById('saveEventChanges');
    if (saveButton) {
        saveButton.addEventListener('click', function() {
            const formData = new FormData();
            
            // Manually collect all form data to ensure proper handling
            formData.append('action', 'update_event');
            formData.append('event_id', document.getElementById('edit-event-id').value);
            formData.append('event_name', document.getElementById('edit-event-name').value);
            formData.append('event_type', document.getElementById('edit-event-type').value);
            formData.append('event_date', document.getElementById('edit-event-date').value);
            formData.append('event_time', document.getElementById('edit-event-time').value);
            formData.append('description', document.getElementById('edit-event-description').value);
            formData.append('location', document.getElementById('edit-event-location').value);
            formData.append('capacity', document.getElementById('edit-event-capacity').value);
            formData.append('price', document.getElementById('edit-event-price').value);
            formData.append('contact_email', document.getElementById('edit-contact-email').value);
            formData.append('contact_phone', document.getElementById('edit-contact-phone').value);
            formData.append('requirements', document.getElementById('edit-event-requirements').value);
            
            // Add file field if there's a file selected
            const fileField = document.getElementById('edit-fileField');
            if (fileField.files.length > 0) {
                formData.append('fileField', fileField.files[0]);
            }
            
            // Convert checkbox to proper value
            formData.append('is_active', document.getElementById('edit-event-active').checked ? '1' : '0');
            
            // Validate required fields
            const eventName = document.getElementById('edit-event-name').value.trim();
            const eventDate = document.getElementById('edit-event-date').value;
            const eventTime = document.getElementById('edit-event-time').value;
            const location = document.getElementById('edit-event-location').value.trim();
            const capacity = document.getElementById('edit-event-capacity').value;
            const price = document.getElementById('edit-event-price').value;
            const eventType = document.getElementById('edit-event-type').value;
            
            if (!eventName || !eventDate || !eventTime || !location || !capacity || !price || !eventType) {
                if (typeof showBootstrapAlert === 'function') {
                    showBootstrapAlert('Please fill in all required fields', 'error', 5000);
                } else {
                    alert('Please fill in all required fields');
                }
                return;
            }
            
            // Validate date is not in the past
            const selectedDate = new Date(eventDate);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            selectedDate.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                if (typeof showBootstrapAlert === 'function') {
                    showBootstrapAlert('Event date cannot be in the past', 'error', 5000);
                } else {
                    alert('Event date cannot be in the past');
                }
                return;
            }
            
            fetch('controller/event_list.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editEventModal'));
                    modal.hide();
                    
                    if (typeof showBootstrapAlert === 'function') {
                        showBootstrapAlert(data.message || 'Event updated successfully!', 'success', 4000);
                    }
                    
                    // Reload the page to show updated data
                    window.location.reload();
                } else {
                    if (typeof showBootstrapAlert === 'function') {
                        showBootstrapAlert(data.message || 'Error updating event', 'error', 5000);
                    } else {
                        alert(data.message || 'Error updating event');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof showBootstrapAlert === 'function') {
                    showBootstrapAlert('Error updating event', 'error', 5000);
                } else {
                    alert('Error updating event');
                }
            });
        });
    }
});

/**
 * Toggle event active status
 */
function toggleEventStatus(eventId, currentStatus) {
    const formData = new FormData();
    formData.append('action', 'toggle');
    formData.append('id', eventId);
    
    fetch('controller/event_list.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof showBootstrapAlert === 'function') {
                showBootstrapAlert(data.message || 'Event status updated successfully!', 'success', 2000);
                // Give user time to see the success message, then fade out before reloading
                setTimeout(() => {
                    // Hide any visible alerts to prevent visual glitch
                    const alerts = document.querySelectorAll('.alert');
                    alerts.forEach(alert => {
                        alert.style.transition = 'opacity 0.3s ease';
                        alert.style.opacity = '0';
                    });
                    
                    // Reload after fade out
                    setTimeout(() => {
                        window.location.reload();
                    }, 300);
                }, 1200);
            } else {
                alert(data.message || 'Event status updated successfully!');
                window.location.reload();
            }
        } else {
            if (typeof showBootstrapAlert === 'function') {
                showBootstrapAlert(data.message || 'Error updating event status', 'error', 5000);
            } else {
                alert(data.message || 'Error updating event status');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof showBootstrapAlert === 'function') {
            showBootstrapAlert('Error updating event status', 'error', 5000);
        } else {
            alert('Error updating event status');
        }
    });
}

/**
 * Disable/Enable event from modal
 */
function disableEventFromModal() {
    const eventId = document.getElementById('edit-event-id').value;
    const disableBtn = document.getElementById('disableEventBtn');
    
    // Determine current status from button text
    const currentStatus = disableBtn.innerHTML.includes('Disable Event') ? 1 : 0;
    
    if (!eventId) {
        if (typeof showBootstrapAlert === 'function') {
            showBootstrapAlert('No event selected', 'error', 5000);
        } else {
            alert('No event selected');
        }
        return;
    }
    
    // Call the existing toggle function
    toggleEventStatus(eventId, currentStatus);
}