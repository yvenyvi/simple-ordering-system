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

// Fetch user's orders
$user_id = $_SESSION['user_id'];
$orders_query = "SELECT o.*, 
                 COUNT(oi.order_item_id) as item_count
                 FROM orders o
                 LEFT JOIN order_items oi ON o.order_id = oi.order_id
                 WHERE o.user_id = ?
                 GROUP BY o.order_id
                 ORDER BY o.order_date DESC";

$stmt = mysqli_prepare($connection, $orders_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$orders_result = mysqli_stmt_get_result($stmt);
$orders = [];
while ($row = mysqli_fetch_assoc($orders_result)) {
    $orders[] = $row;
}
mysqli_stmt_close($stmt);

$page_title = "My Orders";
?>