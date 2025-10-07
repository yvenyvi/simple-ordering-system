<?php require_once("../models/db_Model.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Delicious Eats Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/admin_enhanced.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include("includes/header.php"); ?>
    
    <div class="admin-container">
        <?php include("includes/sidebar.php"); ?>
        
        <main class="admin-main">
            <div class="admin-header">
                <div class="header-content">
                    <h1> Order Management</h1>
                </div>
            </div>

            <div class="admin-content">
                <div class="admin-table-container">
                    <?php
                    // Build SQL query based on filters
                    $sql = "SELECT 
                                o.order_id,
                                o.order_date,
                                o.total_amount,
                                o.status,
                                o.delivery_address,
                                o.phone,
                                o.payment_method,
                                o.payment_status,
                                o.special_instructions,
                                CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                                u.email as customer_email,
                                COUNT(oi.order_item_id) as item_count
                            FROM orders o
                            LEFT JOIN users u ON o.user_id = u.user_id
                            LEFT JOIN order_items oi ON o.order_id = oi.order_id";
                    
                    // Apply filters if set
                    $where_conditions = [];
                    
                    if (isset($_GET['status']) && !empty($_GET['status'])) {
                        $status = mysqli_real_escape_string($connection, $_GET['status']);
                        $where_conditions[] = "o.status = '$status'";
                    }
                    
                    if (isset($_GET['date_filter']) && !empty($_GET['date_filter'])) {
                        $date_filter = $_GET['date_filter'];
                        switch ($date_filter) {
                            case 'today':
                                $where_conditions[] = "DATE(o.order_date) = CURDATE()";
                                break;
                            case 'yesterday':
                                $where_conditions[] = "DATE(o.order_date) = CURDATE() - INTERVAL 1 DAY";
                                break;
                            case 'week':
                                $where_conditions[] = "o.order_date >= CURDATE() - INTERVAL 7 DAY";
                                break;
                            case 'month':
                                $where_conditions[] = "o.order_date >= CURDATE() - INTERVAL 30 DAY";
                                break;
                        }
                    }
                    
                    if (!empty($where_conditions)) {
                        $sql .= " WHERE " . implode(' AND ', $where_conditions);
                    }
                    
                    $sql .= " GROUP BY o.order_id ORDER BY o.order_date DESC";
                    
                    // Custom column configuration for orders display
                    $options = [
                        'columns' => [
                            'order_id' => [
                                'label' => 'Order #',
                                'type' => 'string'
                            ],
                            'customer_name' => [
                                'label' => 'Customer',
                                'type' => 'string'
                            ],
                            'customer_email' => [
                                'label' => 'Email',
                                'type' => 'email'
                            ],
                            'phone' => [
                                'label' => 'Phone',
                                'type' => 'phone'
                            ],
                            'item_count' => [
                                'label' => 'Items',
                                'type' => 'string'
                            ],
                            'total_amount' => [
                                'label' => 'Total',
                                'type' => 'price'
                            ],
                            'status' => [
                                'label' => 'Status',
                                'type' => 'status'
                            ],
                            'payment_status' => [
                                'label' => 'Payment',
                                'type' => 'status'
                            ],
                            'order_date' => [
                                'label' => 'Order Date',
                                'type' => 'datetime'
                            ]
                        ],
                        'actions' => ['view', 'update_status', 'delete']
                    ];
                    
                    display_table('orders', $sql, $options);
                    ?>
                </div>
            </div>
        </main>
    </div>

    <?php include("includes/footer.php"); ?>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <!-- Order details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="updateStatusBtn">Update Status</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusUpdateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Order Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="statusUpdateForm">
                        <input type="hidden" id="updateOrderId" name="order_id">
                        <div class="mb-3">
                            <label for="newStatus" class="form-label">New Status</label>
                            <select class="form-control" id="newStatus" name="status" required>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="preparing">Preparing</option>
                                <option value="ready">Ready for Delivery</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="statusNotes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="statusNotes" name="notes" rows="3" placeholder="Add any notes about this status change..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmStatusUpdate">Update Status</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script src="../assets/js/order_management.js"></script>
</body>
</html>