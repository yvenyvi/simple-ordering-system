<?php
require_once __DIR__ . '/../../models/db_Model.php';

// Handle view request (AJAX)
if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    
    $user_id = intval($_GET['id']);
    
    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        exit;
    }
    
    // Use the global connection or create one
    global $connection;
    if (!$connection) {
        $connection = mysqli_connect("localhost", "root", "password", "delicious_eats");
        if (mysqli_connect_errno()) {
            echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . mysqli_connect_error()]);
            exit;
        }
    }
    
    // Fetch user details (excluding password)
    $sql = "SELECT user_id, first_name, last_name, email, phone, address, city, state, zip_code, is_active, created_at 
            FROM users WHERE user_id = ?";
    
    $stmt = mysqli_prepare($connection, $sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database prepare error: ' . mysqli_error($connection)]);
        exit;
    }
    
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => false, 'message' => 'Database execute error: ' . mysqli_stmt_error($stmt)]);
        mysqli_stmt_close($stmt);
        exit;
    }
    
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found with ID: ' . $user_id]);
    }
    
    mysqli_stmt_close($stmt);
    exit;
}

// Handle get user for edit request (AJAX)
if (isset($_GET['action']) && $_GET['action'] === 'get_for_edit' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    
    $user_id = intval($_GET['id']);
    
    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        exit;
    }
    
    global $connection;
    if (!$connection) {
        $connection = mysqli_connect("localhost", "root", "password", "delicious_eats");
        if (mysqli_connect_errno()) {
            echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . mysqli_connect_error()]);
            exit;
        }
    }
    
    // Fetch user details for editing (excluding password)
    $sql = "SELECT user_id, first_name, last_name, email, phone, address, city, state, zip_code, is_active 
            FROM users WHERE user_id = ?";
    
    $stmt = mysqli_prepare($connection, $sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database prepare error: ' . mysqli_error($connection)]);
        exit;
    }
    
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => false, 'message' => 'Database execute error: ' . mysqli_stmt_error($stmt)]);
        mysqli_stmt_close($stmt);
        exit;
    }
    
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found with ID: ' . $user_id]);
    }
    
    mysqli_stmt_close($stmt);
    exit;
}

// Handle edit user form submission
if (isset($_POST['action']) && $_POST['action'] === 'edit' && isset($_POST['edit_user_id'])) {
    $user_id = intval($_POST['edit_user_id']);
    
    if ($user_id <= 0) {
        $error_message = "Invalid user ID provided.";
        redirect_to("../user_list.php?error=" . urlencode($error_message));
    }
    
    // Validate input data
    $errors = [];
    
    if (empty(trim($_POST['first_name']))) {
        $errors[] = "First name is required";
    }
    if (empty(trim($_POST['last_name']))) {
        $errors[] = "Last name is required";
    }
    if (empty(trim($_POST['email']))) {
        $errors[] = "Email is required";
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Check if email already exists for other users
    global $connection;
    $email_check_sql = "SELECT COUNT(*) as count FROM users WHERE email = ? AND user_id != ?";
    $stmt = mysqli_prepare($connection, $email_check_sql);
    mysqli_stmt_bind_param($stmt, "si", $_POST['email'], $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['count'] > 0) {
        $errors[] = "Email address already exists for another user";
    }
    mysqli_stmt_close($stmt);
    
    if (empty($errors)) {
        // Prepare update data
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $city = trim($_POST['city']);
        $state = trim($_POST['state']);
        $zip_code = trim($_POST['zip_code']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $sql = "UPDATE users SET 
                first_name = ?, 
                last_name = ?, 
                email = ?, 
                phone = ?, 
                address = ?, 
                city = ?, 
                state = ?, 
                zip_code = ?, 
                is_active = ?,
                updated_at = CURRENT_TIMESTAMP
                WHERE user_id = ?";
        
        $stmt = mysqli_prepare($connection, $sql);
        if (!$stmt) {
            $error_message = "Database prepare error: " . mysqli_error($connection);
            redirect_to("../user_list.php?error=" . urlencode($error_message));
        }
        
        mysqli_stmt_bind_param($stmt, "ssssssssii", 
            $first_name,
            $last_name,
            $email,
            $phone,
            $address,
            $city,
            $state,
            $zip_code,
            $is_active,
            $user_id
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "User '{$first_name} {$last_name}' has been successfully updated!";
            redirect_to("../user_list.php?success=" . urlencode($success_message));
        } else {
            $error_message = "Failed to update user: " . mysqli_stmt_error($stmt);
            redirect_to("../user_list.php?error=" . urlencode($error_message));
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "Please fix the following errors: " . implode(", ", $errors);
        redirect_to("../user_list.php?error=" . urlencode($error_message));
    }
}

// Handle delete request
if (isset($_GET['deleteid'])) {
    $delete_id = intval($_GET['deleteid']); // Sanitize input
    
    if ($delete_id > 0) {
        // Use centralized delete function
        $result = delete_record('users', $delete_id);
        
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
    } else {
        $error_message = "Invalid user ID provided.";
    }
    
    // Redirect to prevent resubmission
    redirect_to("../user_list.php");
}

if (isset($_POST['first_name'])) {
    // Validate input data
    $errors = [];
    
    if (empty(trim($_POST['first_name']))) {
        $errors[] = "First name is required";
    }
    if (empty(trim($_POST['last_name']))) {
        $errors[] = "Last name is required";
    }
    if (empty(trim($_POST['email']))) {
        $errors[] = "Email is required";
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    if (empty($_POST['password'])) {
        $errors[] = "Password is required";
    } elseif (strlen($_POST['password']) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    // Check if email already exists
    global $connection;
    $email_check_sql = "SELECT COUNT(*) as count FROM users WHERE email = ?";
    $stmt = mysqli_prepare($connection, $email_check_sql);
    mysqli_stmt_bind_param($stmt, "s", $_POST['email']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['count'] > 0) {
        $errors[] = "Email address already exists";
    }
    mysqli_stmt_close($stmt);
    
    if (empty($errors)) {
        // Prepare secure data
        $data = array(
            'first_name' => trim($_POST['first_name']),
            'last_name' => trim($_POST['last_name']),
            'email' => trim($_POST['email']),
            'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'phone' => trim($_POST['phone']),
            'address' => trim($_POST['address']),
            'city' => trim($_POST['city']),
            'state' => trim($_POST['state']),
            'zip_code' => trim($_POST['zip_code']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        );

        if (save('users', $data)) {
            $success_message = "User '{$data['first_name']} {$data['last_name']}' has been successfully added!";
            redirect_to("../user_list.php?success=" . urlencode($success_message));
        } else {
            $error_message = "Failed to add user. Please try again.";
            redirect_to("../user_list.php?error=" . urlencode($error_message));
        }
    } else {
        $error_message = "Please fix the following errors: " . implode(", ", $errors);
        redirect_to("../user_list.php?error=" . urlencode($error_message));
    }
}
?>