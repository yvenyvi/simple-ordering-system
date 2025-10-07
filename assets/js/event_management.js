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