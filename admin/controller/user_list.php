<?php
require_once "../models/db_Model.php"; // Now includes universal table display function

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
    redirect_to("user_list.php");
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
        } else {
            $error_message = "Failed to add user. Please try again.";
        }
    } else {
        $error_message = "Please fix the following errors: " . implode(", ", $errors);
    }
}
?>