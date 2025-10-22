/**
 * Event List Page JavaScript
 * Handles PHP message display and page-specific functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // This will be populated with PHP variables when the page loads
    // The actual message handling will be injected by PHP
});

/**
 * Show event messages using Bootstrap alerts
 * @param {string|null} successMessage - Success message to display
 * @param {string|null} errorMessage - Error message to display
 */
function showEventMessages(successMessage, errorMessage) {
    const container = document.querySelector('.admin-main');
    if (!container) return;

    // Remove existing alerts
    const existingAlerts = container.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());

    // Create alert HTML
    let alertHTML = '';
    
    if (successMessage) {
        alertHTML += `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> ${successMessage}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    }
    
    if (errorMessage) {
        alertHTML += `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> ${errorMessage}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    }
    
    if (alertHTML) {
        // Insert alert at the beginning of the main content
        const sectionHeader = container.querySelector('.section-header');
        if (sectionHeader) {
            sectionHeader.insertAdjacentHTML('afterend', alertHTML);
        } else {
            container.insertAdjacentHTML('afterbegin', alertHTML);
        }

        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const alerts = container.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    }
}

/**
 * Show info message using Bootstrap alerts
 * @param {string} infoMessage - Info message to display
 */
function showEventInfoMessage(infoMessage) {
    const container = document.querySelector('.admin-main');
    if (!container) return;

    const alertHTML = `
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle"></i> ${infoMessage}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Insert alert at the beginning of the main content
    const sectionHeader = container.querySelector('.section-header');
    if (sectionHeader) {
        sectionHeader.insertAdjacentHTML('afterend', alertHTML);
    } else {
        container.insertAdjacentHTML('afterbegin', alertHTML);
    }

    // Auto-dismiss after 4 seconds
    setTimeout(() => {
        const alert = container.querySelector('.alert-info');
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 4000);
}

/**
 * Initialize PHP message handling
 * This function will be called with PHP-generated parameters
 */
function initializeEventMessages(messages) {
    if (messages.success) {
        showEventMessages(messages.success, null);
    }
    if (messages.error) {
        showEventMessages(null, messages.error);
    }
    if (messages.info) {
        showEventInfoMessage(messages.info);
    }
}