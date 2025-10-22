<?php
require_once '../controller/orders.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Delicious Eats</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/orders.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="../assets/js/header.js"></script>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <section class="orders-section">
            <div class="container">
                <div class="page-header">
                    <h1><i class="fas fa-shopping-bag"></i> My Orders</h1>
                    <p>Track your order history and current orders</p>
                </div>

                <div class="orders-content">
                    <?php if (empty($orders)): ?>
                        <div class="orders-empty">
                            <div class="empty-icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <h2>No Orders Yet</h2>
                            <p>You haven't placed any orders yet. Start browsing our delicious menu!</p>
                            <a href="products.php" class="btn btn-primary">
                                <i class="fas fa-utensils"></i> Browse Menu
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <?php
                            // Fetch order items for this order
                            $order_items_query = "SELECT oi.*, m.name as menu_name
                                                 FROM order_items oi
                                                 LEFT JOIN menu m ON oi.menu_id = m.menu_id
                                                 WHERE oi.order_id = ?";
                            $items_stmt = mysqli_prepare($connection, $order_items_query);
                            mysqli_stmt_bind_param($items_stmt, "i", $order['order_id']);
                            mysqli_stmt_execute($items_stmt);
                            $items_result = mysqli_stmt_get_result($items_stmt);
                            $order_items = [];
                            while ($item_row = mysqli_fetch_assoc($items_result)) {
                                $order_items[] = $item_row;
                            }
                            mysqli_stmt_close($items_stmt);
                            
                            // Determine status class
                            $status_class = 'status-' . strtolower($order['status']);
                            $status_icon = '';
                            $status_text = ucfirst($order['status']);
                            
                            switch (strtolower($order['status'])) {
                                case 'pending':
                                    $status_icon = 'fa-clock';
                                    break;
                                case 'confirmed':
                                    $status_icon = 'fa-check';
                                    break;
                                case 'preparing':
                                    $status_icon = 'fa-utensils';
                                    break;
                                case 'ready':
                                    $status_icon = 'fa-box';
                                    break;
                                case 'out_for_delivery':
                                    $status_icon = 'fa-truck';
                                    $status_text = 'Out for Delivery';
                                    break;
                                case 'delivered':
                                    $status_icon = 'fa-check-circle';
                                    break;
                                case 'cancelled':
                                    $status_icon = 'fa-times-circle';
                                    break;
                                default:
                                    $status_icon = 'fa-info-circle';
                            }
                            
                            $order_date = date('F d, Y \a\t g:i A', strtotime($order['order_date']));
                            ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div class="order-number">
                                        <strong>Order #<?php echo str_pad($order['order_id'], 5, '0', STR_PAD_LEFT); ?></strong>
                                        <span class="order-date"><?php echo $order_date; ?></span>
                                    </div>
                                    <div class="order-status <?php echo $status_class; ?>">
                                        <i class="fas <?php echo $status_icon; ?>"></i> <?php echo $status_text; ?>
                                    </div>
                                </div>
                                <div class="order-items">
                                    <?php foreach ($order_items as $item): ?>
                                        <div class="item">
                                            <span class="item-name"><?php echo htmlspecialchars($item['menu_name'] ?? 'Unknown Item'); ?></span>
                                            <span class="item-quantity">x<?php echo $item['quantity']; ?></span>
                                            <span class="item-price">$<?php echo number_format($item['total_price'], 2); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="order-details">
                                    <div class="detail-row">
                                        <span class="detail-label"><i class="fas fa-map-marker-alt"></i> Delivery Address:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($order['delivery_address']); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label"><i class="fas fa-credit-card"></i> Payment Method:</span>
                                        <span class="detail-value"><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label"><i class="fas fa-info-circle"></i> Payment Status:</span>
                                        <span class="detail-value payment-status-<?php echo $order['payment_status']; ?>">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($order['special_instructions'])): ?>
                                        <div class="detail-row">
                                            <span class="detail-label"><i class="fas fa-comment"></i> Special Instructions:</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($order['special_instructions']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="order-footer">
                                    <div class="order-total">
                                        <strong>Total: $<?php echo number_format($order['total_amount'], 2); ?></strong>
                                    </div>
                                    <div class="order-actions">
                                        <?php if (in_array(strtolower($order['status']), ['pending', 'confirmed'])): ?>
                                            <button class="btn btn-danger btn-sm" onclick="cancelOrder(<?php echo $order['order_id']; ?>)">
                                                <i class="fas fa-times"></i> Cancel Order
                                            </button>
                                        <?php endif; ?>
                                        <?php if (strtolower($order['status']) === 'delivered'): ?>
                                            <button class="btn btn-primary btn-sm" onclick="reorder(<?php echo $order['order_id']; ?>)">
                                                <i class="fas fa-redo"></i> Reorder
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
    
    <script>
    function cancelOrder(orderId) {
        if (confirm('Are you sure you want to cancel this order?')) {
            // Implement cancel order functionality
            alert('Cancel order functionality will be implemented. Order ID: ' + orderId);
            // You can add an AJAX call here to update the order status to 'cancelled'
        }
    }
    
    function reorder(orderId) {
        // Implement reorder functionality
        alert('Reorder functionality will be implemented. Order ID: ' + orderId);
        // You can add logic here to add the order items back to the cart
    }
    </script>
</body>
</html>