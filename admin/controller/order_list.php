<?php
// Prevent any output before JSON response
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output

require_once __DIR__ . "/../../models/db_Model.php";
// Handle AJAX requests
if (isset($_GET['action'])) {
    ob_clean(); // Clear any output buffer before sending JSON
    header('Content-Type: application/json');
    
    try {
        switch ($_GET['action']) {
            case 'get_order_details':
                handleGetOrderDetails();
                break;
            case 'update_status':
                handleUpdateStatus();
                break;
            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        error_log("Controller exception: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server error occurred']);
    }
    exit;
}

// Handle get order details AJAX request
function handleGetOrderDetails() {
    global $connection;
    
    // Clear any previous output
    if (ob_get_level()) {
        ob_clean();
    }
    
    try {
        if (!isset($_GET['order_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Order ID required']);
            return;
        }
        
        $order_id = intval($_GET['order_id']);
        
        // Get order details
        $order_query = "SELECT 
                            o.*,
                            CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                            u.email as customer_email
                        FROM orders o
                        LEFT JOIN users u ON o.user_id = u.user_id
                        WHERE o.order_id = ?";
        
        $stmt = mysqli_prepare($connection, $order_query);
        if (!$stmt) {
            throw new Exception("Failed to prepare order query: " . mysqli_error($connection));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);
        $order_result = mysqli_stmt_get_result($stmt);
        
        if (!$order_result || mysqli_num_rows($order_result) === 0) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            mysqli_stmt_close($stmt);
            return;
        }
        
        $order = mysqli_fetch_assoc($order_result);
        mysqli_stmt_close($stmt);
        
        // Get order items
        $items_query = "SELECT 
                            oi.*,
                            m.name,
                            m.description
                        FROM order_items oi
                        LEFT JOIN menu m ON oi.menu_id = m.menu_id
                        WHERE oi.order_id = ?";
        
        $items_stmt = mysqli_prepare($connection, $items_query);
        if (!$items_stmt) {
            throw new Exception("Failed to prepare items query: " . mysqli_error($connection));
        }
        
        mysqli_stmt_bind_param($items_stmt, "i", $order_id);
        mysqli_stmt_execute($items_stmt);
        $items_result = mysqli_stmt_get_result($items_stmt);
        
        $items = [];
        while ($item = mysqli_fetch_assoc($items_result)) {
            $items[] = $item;
        }
        mysqli_stmt_close($items_stmt);
        
        $order['items'] = $items;
        
        echo json_encode([
            'success' => true,
            'order' => $order
        ]);
        
    } catch (Exception $e) {
        error_log("Error in handleGetOrderDetails: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to get order details'
        ]);
    }
}

// Handle update status AJAX request
function handleUpdateStatus() {
    global $connection;
    
    // Clear any previous output
    if (ob_get_level()) {
        ob_clean();
    }
    
    if (!isset($_POST['order_id']) || !isset($_POST['status'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Order ID and status required']);
        return;
    }
    
    $order_id = intval($_POST['order_id']);
    $status = mysqli_real_escape_string($connection, $_POST['status']);
    $notes = isset($_POST['notes']) ? mysqli_real_escape_string($connection, $_POST['notes']) : '';
    
    // Validate status
    $valid_statuses = ['pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'];
    if (!in_array($status, $valid_statuses)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        return;
    }
    
    try {
        // Get current order details including payment method
        $order_query = "SELECT payment_method, payment_status FROM orders WHERE order_id = ?";
        $order_stmt = mysqli_prepare($connection, $order_query);
        mysqli_stmt_bind_param($order_stmt, "i", $order_id);
        mysqli_stmt_execute($order_stmt);
        $order_result = mysqli_stmt_get_result($order_stmt);
        
        if (!$order_result || mysqli_num_rows($order_result) === 0) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }
        
        $order_data = mysqli_fetch_assoc($order_result);
        $payment_method = $order_data['payment_method'];
        $current_payment_status = $order_data['payment_status'];
        mysqli_stmt_close($order_stmt);
        
        // Determine payment status based on payment method and order status
        $new_payment_status = $current_payment_status; // Default: keep current status
        $payment_status_updated = false;
        
        if ($payment_method === 'cash') {
            // Cash payment: payment status follows order status
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
                            status = ?,
                            payment_status = ?,
                            updated_at = NOW()
                         WHERE order_id = ?";
        
        $update_stmt = mysqli_prepare($connection, $update_query);
        mysqli_stmt_bind_param($update_stmt, "ssi", $status, $new_payment_status, $order_id);
        $result = mysqli_stmt_execute($update_stmt);
        
        if (!$result) {
            throw new Exception('Database update failed');
        }
        
        if (mysqli_stmt_affected_rows($update_stmt) === 0) {
            echo json_encode(['success' => false, 'message' => 'Order not found or no changes made']);
            return;
        }
        mysqli_stmt_close($update_stmt);
        
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
}

// Handle delete request
if (isset($_GET['deleteid'])) {
    $delete_id = intval($_GET['deleteid']); // Sanitize input
    
    if ($delete_id > 0) {
        // Use centralized delete function
        $result = delete_record('orders', $delete_id);
        
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
    } else {
        $error_message = "Invalid order ID provided.";
    }
    
    redirect_to("order_list.php");
}

// Handle status update request
if (isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $status = trim($_POST['status']);
    
    $allowed_statuses = ['pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'];
    
    if ($order_id > 0 && in_array($status, $allowed_statuses)) {
        global $connection;
        
        // Determine payment status based on order status
        $payment_status_map = [
            'pending' => 'pending',
            'confirmed' => 'pending',
            'preparing' => 'pending',
            'ready' => 'pending',
            'delivered' => 'paid',
            'cancelled' => 'refunded'
        ];
        
        $new_payment_status = $payment_status_map[$status];
        
        // Special handling for cancelled orders
        if ($status === 'cancelled') {
            // Get current payment status
            $check_sql = "SELECT payment_status FROM orders WHERE order_id = ?";
            $check_stmt = mysqli_prepare($connection, $check_sql);
            mysqli_stmt_bind_param($check_stmt, "i", $order_id);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            
            if ($row = mysqli_fetch_assoc($check_result)) {
                $current_payment_status = $row['payment_status'];
                
                // If payment was already made (paid), mark as refunded
                // If payment was pending or failed, mark as failed
                if ($current_payment_status === 'paid') {
                    $new_payment_status = 'refunded';
                } else {
                    $new_payment_status = 'failed';
                }
            }
            mysqli_stmt_close($check_stmt);
        }
        
        // Update both order status and payment status
        $update_sql = "UPDATE orders SET status = ?, payment_status = ?, updated_at = NOW() WHERE order_id = ?";
        $stmt = mysqli_prepare($connection, $update_sql);
        mysqli_stmt_bind_param($stmt, "ssi", $status, $new_payment_status, $order_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Order status updated successfully! Payment status automatically set to '{$new_payment_status}'.";
        } else {
            $error_message = "Failed to update order status. Please try again.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "Invalid order ID or status provided.";
    }
    
    redirect_to("order_list.php");
}

// Prepare SQL query for displaying orders
$orders_sql = "SELECT 
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
    $orders_sql .= " WHERE " . implode(' AND ', $where_conditions);
}

$orders_sql .= " GROUP BY o.order_id ORDER BY o.order_date DESC";

?>