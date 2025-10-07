<?php
// API to update order status
header('Content-Type: application/json');
require_once '../models/db_Model.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_POST['order_id']) || !isset($_POST['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Order ID and status required']);
    exit;
}

$order_id = intval($_POST['order_id']);
$status = mysqli_real_escape_string($connection, $_POST['status']);
$notes = isset($_POST['notes']) ? mysqli_real_escape_string($connection, $_POST['notes']) : '';

// Validate status
$valid_statuses = ['pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'];
if (!in_array($status, $valid_statuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    // Update order status
    $update_query = "UPDATE orders SET 
                        status = '$status',
                        updated_at = NOW()
                     WHERE order_id = $order_id";
    
    $result = mysqli_query($connection, $update_query);
    
    if (!$result) {
        throw new Exception('Database update failed');
    }
    
    if (mysqli_affected_rows($connection) === 0) {
        echo json_encode(['success' => false, 'message' => 'Order not found or no changes made']);
        exit;
    }
    
    // Log status change if notes were provided
    if (!empty($notes)) {
        // You could create a order_status_log table for tracking status changes
        // For now, we'll just include it in the response
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Order status updated successfully',
        'new_status' => $status
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update order status: ' . $e->getMessage()
    ]);
}
?>