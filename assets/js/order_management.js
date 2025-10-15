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
    // Initialize filters
    applyOrderFilters();
    
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
                            <strong>Payment Method:</strong> <span class="ms-2">${formatPaymentMethod(order.payment_method)}</span>
                        </div>
                        <div class="info-item">
                            <strong>Payment Status:</strong> <span class="ms-2"><span class="badge bg-${getPaymentColor(order.payment_status)}">${order.payment_status}</span></span>
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
 * Format payment method for display
 */
function formatPaymentMethod(method) {
    const methods = {
        'cash': 'Cash',
        'credit_card': 'Credit Card',
        'debit_card': 'Debit Card',
        'online': 'Online Payment',
        'paypal': 'PayPal',
        'bank_transfer': 'Bank Transfer'
    };
    return methods[method] || method || 'Not specified';
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

// Enhanced Order Filter Functions
function applyOrderFilters() {
    const statusFilter = document.getElementById('status-filter').value.toLowerCase();
    const paymentFilter = document.getElementById('payment-filter').value.toLowerCase();
    const searchFilter = document.getElementById('search-filter').value.toLowerCase();
    const amountFilter = document.getElementById('amount-filter').value;
    const dateFilter = document.getElementById('date-filter').value;
    
    const tableRows = document.querySelectorAll('.admin-enhanced-table tbody tr');
    let visibleCount = 0;
    
    tableRows.forEach(row => {
        let shouldShow = true;
        
        // Status filter
        if (statusFilter && shouldShow) {
            const statusCell = row.querySelector('td:nth-child(7)'); // Status column
            if (statusCell) {
                const statusElement = statusCell.querySelector('select') || statusCell.querySelector('.badge');
                let statusText = '';
                
                if (statusElement) {
                    if (statusElement.tagName === 'SELECT') {
                        statusText = statusElement.value.toLowerCase();
                    } else {
                        statusText = statusElement.textContent.toLowerCase();
                    }
                }
                
                if (!statusText.includes(statusFilter)) {
                    shouldShow = false;
                }
            }
        }
        
        // Payment status filter
        if (paymentFilter && shouldShow) {
            const paymentCell = row.querySelector('td:nth-child(8)'); // Payment Status column
            if (paymentCell) {
                const paymentBadge = paymentCell.querySelector('.badge');
                const paymentText = paymentBadge ? paymentBadge.textContent.toLowerCase() : '';
                
                if (!paymentText.includes(paymentFilter)) {
                    shouldShow = false;
                }
            }
        }
        
        // Amount filter
        if (amountFilter && shouldShow) {
            const amountCell = row.querySelector('td:nth-child(6) .cell-price'); // Total Amount column
            if (amountCell) {
                const amountText = amountCell.textContent.replace('$', '').replace(',', '');
                const amount = parseFloat(amountText);
                
                if (!isNaN(amount)) {
                    switch (amountFilter) {
                        case '0-25':
                            if (amount > 25) shouldShow = false;
                            break;
                        case '25-50':
                            if (amount < 25 || amount > 50) shouldShow = false;
                            break;
                        case '50-100':
                            if (amount < 50 || amount > 100) shouldShow = false;
                            break;
                        case '100+':
                            if (amount < 100) shouldShow = false;
                            break;
                    }
                }
            }
        }
        
        // Date filter
        if (dateFilter && shouldShow) {
            const dateCell = row.querySelector('td:nth-child(9) .cell-date'); // Order Date column
            if (dateCell) {
                const dateText = dateCell.textContent;
                const orderDate = new Date(dateText);
                const today = new Date();
                const yesterday = new Date(today);
                yesterday.setDate(yesterday.getDate() - 1);
                
                switch (dateFilter) {
                    case 'today':
                        if (orderDate.toDateString() !== today.toDateString()) {
                            shouldShow = false;
                        }
                        break;
                    case 'yesterday':
                        if (orderDate.toDateString() !== yesterday.toDateString()) {
                            shouldShow = false;
                        }
                        break;
                    case 'week':
                        const weekAgo = new Date(today);
                        weekAgo.setDate(weekAgo.getDate() - 7);
                        if (orderDate < weekAgo) {
                            shouldShow = false;
                        }
                        break;
                    case 'month':
                        const monthAgo = new Date(today);
                        monthAgo.setMonth(monthAgo.getMonth() - 1);
                        if (orderDate < monthAgo) {
                            shouldShow = false;
                        }
                        break;
                }
            }
        }
        
        // Search filter (customer name, email, order ID)
        if (searchFilter && shouldShow) {
            const orderIdCell = row.querySelector('td:nth-child(1)'); // Order #
            const customerCell = row.querySelector('td:nth-child(2)'); // Customer
            const emailCell = row.querySelector('td:nth-child(3)'); // Email
            
            const orderIdText = orderIdCell ? orderIdCell.textContent.toLowerCase() : '';
            const customerText = customerCell ? customerCell.textContent.toLowerCase() : '';
            const emailText = emailCell ? emailCell.textContent.toLowerCase() : '';
            
            const searchText = orderIdText + ' ' + customerText + ' ' + emailText;
            
            if (!searchText.includes(searchFilter)) {
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
    updateOrderResultsCount(visibleCount, tableRows.length);
}

function clearOrderFilters() {
    document.getElementById('status-filter').value = '';
    document.getElementById('payment-filter').value = '';
    document.getElementById('search-filter').value = '';
    document.getElementById('amount-filter').value = '';
    document.getElementById('date-filter').value = '';
    applyOrderFilters();
}

function updateOrderResultsCount(visible, total) {
    let countDisplay = document.getElementById('results-count');
    if (!countDisplay) {
        countDisplay = document.createElement('div');
        countDisplay.id = 'results-count';
        countDisplay.className = 'results-count';
        const filterActions = document.querySelector('.filter-actions');
        if (filterActions) {
            filterActions.appendChild(countDisplay);
        }
    }
    
    if (visible === total) {
        countDisplay.innerHTML = `<span class="text-muted">Showing all ${total} orders</span>`;
    } else {
        countDisplay.innerHTML = `<span class="text-primary">Showing ${visible} of ${total} orders</span>`;
    }
}

// Initialize filters when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Apply filters after a short delay to ensure table is rendered
    setTimeout(() => {
        if (typeof applyOrderFilters === 'function') {
            applyOrderFilters();
        }
    }, 100);
});