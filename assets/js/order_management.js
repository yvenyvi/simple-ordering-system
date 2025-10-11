/**
 * Order Management JavaScript
 * Handles order filtering, status updates, and detail views
 */

document.addEventListener('DOMContentLoaded', function() {
    // Check if SweetAlert is available
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 is not loaded! Order management will not work properly.');
        return;
    }
    initializeOrderManagement();
});

function initializeOrderManagement() {
    // Status dropdown change handler
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('order-status-dropdown')) {
            const orderId = e.target.dataset.orderId;
            const currentStatus = e.target.dataset.currentStatus;
            const newStatus = e.target.value;
            
            // If status hasn't changed, do nothing
            if (currentStatus === newStatus) {
                return;
            }
            
            // Confirm status change
            Swal.fire({
                title: 'Update Order Status?',
                html: `Change order #${orderId} status from <strong>${currentStatus}</strong> to <strong>${newStatus}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check"></i> Yes, update it!',
                cancelButtonText: '<i class="fas fa-times"></i> Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('order_id', orderId);
                    formData.append('status', newStatus);
                    
                    fetch('controller/order_list.php?action=update_status', {
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
                            let message = 'Order status updated successfully';
                            if (data.payment_status_updated) {
                                message += '<br><small class="text-muted">Payment status automatically updated to: ' + data.new_payment_status + '</small>';
                            }
                            Swal.fire({
                                title: 'Success',
                                html: message,
                                icon: 'success'
                            }).then(() => window.location.reload());
                        } else {
                            Swal.fire('Error', data.message || 'Failed to update status', 'error');
                            // Reset dropdown to original value
                            e.target.value = currentStatus;
                        }
                    })
                    .catch(error => {
                        console.error('Status update error:', error);
                        Swal.fire('Error', 'Failed to update status: ' + error.message, 'error');
                        // Reset dropdown to original value
                        e.target.value = currentStatus;
                    });
                } else {
                    // User cancelled, reset dropdown to original value
                    e.target.value = currentStatus;
                }
            });
        }
    });
    
    // Status update functionality (legacy modal support if needed)
    const updateStatusBtn = document.getElementById('updateStatusBtn');
    const confirmStatusUpdate = document.getElementById('confirmStatusUpdate');
    
    if (updateStatusBtn) {
        updateStatusBtn.addEventListener('click', function() {
            new bootstrap.Modal(document.getElementById('statusUpdateModal')).show();
        });
    }

    if (confirmStatusUpdate) {
        confirmStatusUpdate.addEventListener('click', function() {
            const formData = new FormData(document.getElementById('statusUpdateForm'));
            
            fetch('controller/order_list.php?action=update_status', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success', 'Order status updated successfully', 'success')
                        .then(() => window.location.reload());
                } else {
                    Swal.fire('Error', data.message || 'Failed to update status', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Failed to update status', 'error');
            });
        });
    }
}

/**
 * View detailed order information
 */
function viewOrderDetails(orderId) {
    // Show loading state
    document.getElementById('orderDetailsContent').innerHTML = `
        <div class="modal-loading">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    // Show modal immediately with loading state
    new bootstrap.Modal(document.getElementById('orderDetailsModal')).show();
    
    fetch(`controller/order_list.php?action=get_order_details&order_id=${orderId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displayOrderDetails(data.order);
                document.getElementById('updateOrderId').value = orderId;
            } else {
                document.getElementById('orderDetailsContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> ${data.message || 'Failed to load order details'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Order details error:', error);
            document.getElementById('orderDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Failed to load order details: ${error.message}
                </div>
            `;
        });
}

/**
 * Open status update modal directly
 */
function openStatusModal(orderId) {
    document.getElementById('updateOrderId').value = orderId;
    new bootstrap.Modal(document.getElementById('statusUpdateModal')).show();
}

/**
 * Display order details in modal
 */
function displayOrderDetails(order) {
    const content = `
        <div class="order-details">
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="info-section">
                        <h6 class="section-title"><i class="fas fa-user"></i> Customer Information</h6>
                        <div class="info-item">
                            <strong>Name:</strong> <span class="ms-2">${order.customer_name || 'N/A'}</span>
                        </div>
                        <div class="info-item">
                            <strong>Email:</strong> <span class="ms-2">${order.customer_email || 'N/A'}</span>
                        </div>
                        <div class="info-item">
                            <strong>Phone:</strong> <span class="ms-2">${order.phone || 'N/A'}</span>
                        </div>
                        <div class="info-item">
                            <strong>Address:</strong> <span class="ms-2">${order.delivery_address || 'N/A'}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-section">
                        <h6 class="section-title"><i class="fas fa-receipt"></i> Order Information</h6>
                        <div class="info-item">
                            <strong>Order #:</strong> <span class="ms-2">${order.order_id}</span>
                        </div>
                        <div class="info-item">
                            <strong>Status:</strong> <span class="ms-2"><span class="badge bg-${getStatusColor(order.status)}">${order.status}</span></span>
                        </div>
                        <div class="info-item">
                            <strong>Payment:</strong> <span class="ms-2"><span class="badge bg-${getPaymentColor(order.payment_status)}">${order.payment_status}</span></span>
                        </div>
                        <div class="info-item">
                            <strong>Total:</strong> <span class="ms-2 text-success fw-bold">$${parseFloat(order.total_amount).toFixed(2)}</span>
                        </div>
                        <div class="info-item">
                            <strong>Date:</strong> <span class="ms-2">${new Date(order.order_date).toLocaleString()}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            ${order.special_instructions ? `
            <div class="mb-4">
                <h6 class="section-title"><i class="fas fa-sticky-note"></i> Special Instructions</h6>
                <div class="special-instructions">
                    ${order.special_instructions}
                </div>
            </div>
            ` : ''}
            
            <div class="order-items-section">
                <h6 class="section-title"><i class="fas fa-shopping-cart"></i> Order Items</h6>
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col" class="text-start">Item</th>
                                <th scope="col" class="text-center">Qty</th>
                                <th scope="col" class="text-end">Price</th>
                                <th scope="col" class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${order.items.map(item => `
                                <tr>
                                    <td class="text-start">
                                        <div class="fw-bold">${item.name}</div>
                                        ${item.description ? `<small class="text-muted">${item.description}</small>` : ''}
                                    </td>
                                    <td class="text-center">${item.quantity}</td>
                                    <td class="text-end">$${parseFloat(item.unit_price).toFixed(2)}</td>
                                    <td class="text-end fw-bold">$${parseFloat(item.total_price).toFixed(2)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="3" class="text-end py-3">Grand Total:</th>
                                <th class="text-end text-success fs-5 py-3">$${parseFloat(order.total_amount).toFixed(2)}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('orderDetailsContent').innerHTML = content;
}

/**
 * Get Bootstrap color class for order status
 */
function getStatusColor(status) {
    const colors = {
        'pending': 'warning',
        'confirmed': 'info',
        'preparing': 'primary',
        'ready': 'success',
        'delivered': 'success',
        'cancelled': 'danger'
    };
    return colors[status] || 'secondary';
}

/**
 * Get Bootstrap color class for payment status
 */
function getPaymentColor(status) {
    const colors = {
        'pending': 'warning',
        'paid': 'success',
        'failed': 'danger',
        'refunded': 'info'
    };
    return colors[status] || 'secondary';
}

/**
 * Custom delete confirmation for orders
 */
function confirmDelete(orderId, orderName, redirectPage) {
    Swal.fire({
        title: 'Delete Order?',
        html: `Are you sure you want to delete <strong>${orderName}</strong>?<br><br>
               <span class="text-danger"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone!</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash"></i> Yes, delete it!',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `${redirectPage}?deleteid=${orderId}`;
        }
    });
}

/**
 * Quick status update function (can be called from action buttons)
 */
function quickStatusUpdate(orderId, newStatus) {
    Swal.fire({
        title: `Update to ${newStatus}?`,
        text: `Change order #${orderId} status to ${newStatus}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, update it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('status', newStatus);
            
            fetch('controller/order_list.php?action=update_status', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success', 'Order status updated successfully', 'success')
                        .then(() => window.location.reload());
                } else {
                    Swal.fire('Error', data.message || 'Failed to update status', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Failed to update status', 'error');
            });
        }
    });
}