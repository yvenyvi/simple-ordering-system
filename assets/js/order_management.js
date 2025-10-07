/**
 * Order Management JavaScript
 * Handles order filtering, status updates, and detail views
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeOrderManagement();
});

function initializeOrderManagement() {
    // Status update functionality
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
            
            fetch('../api/update_order_status.php', {
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
    fetch(`../api/get_order_details.php?order_id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayOrderDetails(data.order);
                document.getElementById('updateOrderId').value = orderId;
                new bootstrap.Modal(document.getElementById('orderDetailsModal')).show();
            } else {
                Swal.fire('Error', 'Failed to load order details', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Failed to load order details', 'error');
        });
}

/**
 * Display order details in modal
 */
function displayOrderDetails(order) {
    const content = `
        <div class="order-details">
            <div class="row">
                <div class="col-md-6">
                    <h6>Customer Information</h6>
                    <p><strong>Name:</strong> ${order.customer_name}</p>
                    <p><strong>Email:</strong> ${order.customer_email}</p>
                    <p><strong>Phone:</strong> ${order.phone}</p>
                    <p><strong>Address:</strong> ${order.delivery_address}</p>
                </div>
                <div class="col-md-6">
                    <h6>Order Information</h6>
                    <p><strong>Order #:</strong> ${order.order_id}</p>
                    <p><strong>Status:</strong> <span class="badge bg-${getStatusColor(order.status)}">${order.status}</span></p>
                    <p><strong>Payment:</strong> <span class="badge bg-${getPaymentColor(order.payment_status)}">${order.payment_status}</span></p>
                    <p><strong>Total:</strong> $${parseFloat(order.total_amount).toFixed(2)}</p>
                    <p><strong>Date:</strong> ${new Date(order.order_date).toLocaleString()}</p>
                </div>
            </div>
            
            ${order.special_instructions ? `
            <div class="mt-3">
                <h6>Special Instructions</h6>
                <p class="bg-light p-2 rounded">${order.special_instructions}</p>
            </div>
            ` : ''}
            
            <div class="mt-3">
                <h6>Order Items</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${order.items.map(item => `
                                <tr>
                                    <td>${item.name}</td>
                                    <td>${item.quantity}</td>
                                    <td>$${parseFloat(item.unit_price).toFixed(2)}</td>
                                    <td>$${parseFloat(item.total_price).toFixed(2)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
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
            
            fetch('../api/update_order_status.php', {
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