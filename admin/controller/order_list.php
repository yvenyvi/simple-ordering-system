<?php

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
        
        $update_sql = "UPDATE orders SET status = ?, updated_at = NOW() WHERE order_id = ?";
        $stmt = mysqli_prepare($connection, $update_sql);
        mysqli_stmt_bind_param($stmt, "si", $status, $order_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Order status updated successfully!";
        } else {
            $error_message = "Failed to update order status. Please try again.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "Invalid order ID or status provided.";
    }
    
    redirect_to("order_list.php");
}

?>