<?php
// Order processing endpoint - replaces API functionality
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once __DIR__ . "/../../models/db_Model.php";

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate required data
if (!$data || !isset($data['items']) || !isset($data['customer_info'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$items = $data['items'];
$customer_info = $data['customer_info'];

// Validate items array
if (empty($items) || !is_array($items)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No items in order']);
    exit;
}

// Validate customer info
$required_fields = ['first_name', 'last_name', 'email', 'phone', 'address'];
foreach ($required_fields as $field) {
    if (empty($customer_info[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

try {
    // Start transaction
    mysqli_autocommit($connection, false);
    
    // Check if user exists, if not create one
    $user_id = findOrCreateUser($customer_info);
    
    // Calculate total amount
    $total_amount = 0;
    foreach ($items as $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }
    
    // Create order record
    $payment_method = $customer_info['payment_method'] ?? 'cash';
    
    // Set initial payment status based on payment method
    $initial_payment_status = 'pending';
    if (in_array($payment_method, ['credit_card', 'debit_card', 'online'])) {
        // For non-cash payments, assume payment is processed immediately
        $initial_payment_status = 'paid';
    }
    
    $order_data = [
        'user_id' => $user_id,
        'total_amount' => $total_amount,
        'status' => 'pending',
        'delivery_address' => $customer_info['address'] . ', ' . ($customer_info['city'] ?? '') . ', ' . ($customer_info['state'] ?? '') . ' ' . ($customer_info['zip_code'] ?? ''),
        'phone' => $customer_info['phone'],
        'special_instructions' => $customer_info['special_instructions'] ?? null,
        'payment_method' => $payment_method,
        'payment_status' => $initial_payment_status
    ];
    
    $order_id = save('orders', $order_data);
    
    // Create order items
    foreach ($items as $item) {
        $order_item_data = [
            'order_id' => $order_id,
            'menu_id' => $item['id'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['price'],
            'total_price' => $item['price'] * $item['quantity'],
            'special_requests' => $item['special_requests'] ?? null
        ];
        
        save('order_items', $order_item_data);
    }
    
    // Commit transaction
    mysqli_commit($connection);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully!',
        'order_id' => $order_id,
        'total_amount' => $total_amount,
        'estimated_delivery' => date('Y-m-d H:i:s', strtotime('+30 minutes'))
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($connection);
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to process order: ' . $e->getMessage()
    ]);
}

/**
 * Find existing user or create new one
 */
function findOrCreateUser($customer_info) {
    global $connection;
    
    // Check if user exists by email
    $email = mysqli_real_escape_string($connection, $customer_info['email']);
    $query = "SELECT user_id FROM users WHERE email = '$email'";
    $result = mysqli_query($connection, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        return $user['user_id'];
    }
    
    // Create new user
    $user_data = [
        'first_name' => $customer_info['first_name'],
        'last_name' => $customer_info['last_name'],
        'email' => $customer_info['email'],
        'phone' => $customer_info['phone'],
        'address' => $customer_info['address'],
        'city' => $customer_info['city'] ?? '',
        'state' => $customer_info['state'] ?? '',
        'zip_code' => $customer_info['zip_code'] ?? '',
        'password' => password_hash('temp_password_' . time(), PASSWORD_DEFAULT), // Temporary password
        'is_active' => 1
    ];
    
    return save('users', $user_data);
}
?>