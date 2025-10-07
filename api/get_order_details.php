<?php
// API to get detailed order information
header('Content-Type: application/json');
require_once '../models/db_Model.php';

if (!isset($_GET['order_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Order ID required']);
    exit;
}

$order_id = intval($_GET['order_id']);

try {
    // Get order details
    $order_query = "SELECT 
                        o.*,
                        CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                        u.email as customer_email
                    FROM orders o
                    LEFT JOIN users u ON o.user_id = u.user_id
                    WHERE o.order_id = $order_id";
    
    $order_result = mysqli_query($connection, $order_query);
    
    if (!$order_result || mysqli_num_rows($order_result) === 0) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }
    
    $order = mysqli_fetch_assoc($order_result);
    
    // Get order items
    $items_query = "SELECT 
                        oi.*,
                        m.name,
                        m.description
                    FROM order_items oi
                    LEFT JOIN menu m ON oi.menu_id = m.menu_id
                    WHERE oi.order_id = $order_id";
    
    $items_result = mysqli_query($connection, $items_query);
    $items = [];
    
    while ($item = mysqli_fetch_assoc($items_result)) {
        $items[] = $item;
    }
    
    $order['items'] = $items;
    
    echo json_encode([
        'success' => true,
        'order' => $order
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to get order details: ' . $e->getMessage()
    ]);
}
?>