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
    // Get current order details including payment method
    $order_query = "SELECT payment_method, payment_status FROM orders WHERE order_id = $order_id";
    $order_result = mysqli_query($connection, $order_query);
    
    if (!$order_result || mysqli_num_rows($order_result) === 0) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }
    
    $order_data = mysqli_fetch_assoc($order_result);
    $payment_method = $order_data['payment_method'];
    $current_payment_status = $order_data['payment_status'];
    
    // Determine payment status based on payment method and order status
    $new_payment_status = $current_payment_status; // Default: keep current status
    $payment_status_updated = false;
    
    if ($payment_method === 'cash') {
        // Cash payment: payment status follows order status (original behavior)
        $payment_status_map = [
            'pending' => 'pending',
            'confirmed' => 'pending',
            'preparing' => 'pending',
            'ready' => 'pending',
            'delivered' => 'paid',
            'cancelled' => ($current_payment_status === 'paid') ? 'refunded' : 'failed'
        ];
        
        if (isset($payment_status_map[$status])) {
            $new_payment_status = $payment_status_map[$status];
            $payment_status_updated = ($new_payment_status !== $current_payment_status);
        }
    } else {
        // Online/Card payment: payment is successful immediately unless cancelled
        if ($status === 'cancelled') {
            // If order is cancelled, refund the payment
            $new_payment_status = 'refunded';
            $payment_status_updated = ($new_payment_status !== $current_payment_status);
        } elseif ($current_payment_status === 'pending') {
            // For non-cash payments, mark as paid immediately when order is confirmed
            if (in_array($status, ['confirmed', 'preparing', 'ready', 'delivered'])) {
                $new_payment_status = 'paid';
                $payment_status_updated = true;
            }
        }
    }
    
    // Update order status and payment status
    $update_query = "UPDATE orders SET 
                        status = '$status',
                        payment_status = '$new_payment_status',
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
        'new_status' => $status,
        'new_payment_status' => $new_payment_status,
        'payment_status_updated' => $payment_status_updated,
        'payment_method' => $payment_method
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update order status: ' . $e->getMessage()
    ]);
}
?>