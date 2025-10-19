<?php
require_once '../models/db_Model.php';

// Start session and require login
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Require user to be logged in
require_user_login();

// Get current user data
$current_user = get_logged_in_user();

$page_title = "My Orders";
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

                    <!-- This would be populated with actual orders when the ordering system is implemented -->
                    <!--
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-number">
                                <strong>Order #12345</strong>
                                <span class="order-date">March 15, 2024</span>
                            </div>
                            <div class="order-status status-delivered">
                                <i class="fas fa-check-circle"></i> Delivered
                            </div>
                        </div>
                        <div class="order-items">
                            <div class="item">
                                <span class="item-name">Margherita Pizza</span>
                                <span class="item-quantity">x2</span>
                                <span class="item-price">$24.98</span>
                            </div>
                        </div>
                        <div class="order-footer">
                            <div class="order-total">
                                <strong>Total: $26.48</strong>
                            </div>
                            <div class="order-actions">
                                <a href="#" class="btn btn-secondary">View Details</a>
                                <a href="#" class="btn btn-primary">Reorder</a>
                            </div>
                        </div>
                    </div>
                    -->
                </div>
            </div>
        </section>
    </main>
</body>
</html>