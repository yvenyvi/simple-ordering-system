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