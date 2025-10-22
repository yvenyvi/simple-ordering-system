<?php
require_once '../models/db_Model.php';

// Start session and require login
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Require user to be logged in
require_user_login();

// Initialize variables
$edit_mode = false;
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'edit') {
            $edit_mode = true;
        } elseif ($_POST['action'] === 'update') {
            // Process profile update
            $user_id = $_SESSION['user_id'];
            
            // Validate and sanitize input data
            $update_data = [
                'first_name' => trim($_POST['first_name'] ?? ''),
                'last_name' => trim($_POST['last_name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'city' => trim($_POST['city'] ?? ''),
                'state' => trim($_POST['state'] ?? ''),
                'zip_code' => trim($_POST['zip_code'] ?? '')
            ];
            
            // Basic validation
            if (empty($update_data['first_name']) || empty($update_data['last_name']) || empty($update_data['email'])) {
                $error_message = 'First name, last name, and email are required.';
                $edit_mode = true;
            } elseif (!filter_var($update_data['email'], FILTER_VALIDATE_EMAIL)) {
                $error_message = 'Please enter a valid email address.';
                $edit_mode = true;
            } else {
                // Check if email is being changed and if new email already exists
                $current_user_data = get_logged_in_user();
                if ($update_data['email'] !== $current_user_data['email']) {
                    // Email is being changed, check if new email exists
                    global $connection;
                    $email_check_query = "SELECT user_id FROM users WHERE email = ? AND user_id != ? AND is_active = 1";
                    $stmt = mysqli_prepare($connection, $email_check_query);
                    mysqli_stmt_bind_param($stmt, "si", $update_data['email'], $user_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    
                    if (mysqli_fetch_assoc($result)) {
                        $error_message = 'Email address is already taken by another user.';
                        $edit_mode = true;
                        mysqli_stmt_close($stmt);
                    } else {
                        mysqli_stmt_close($stmt);
                        // Proceed with update
                        $result = update('users', $update_data, $user_id);
                        
                        if ($result['success']) {
                            // Update session data for immediate reflection
                            $_SESSION['user_first_name'] = $update_data['first_name'];
                            $_SESSION['user_last_name'] = $update_data['last_name'];
                            $_SESSION['user_email'] = $update_data['email'];
                            
                            $success_message = 'Profile updated successfully!';
                            $edit_mode = false;
                        } else {
                            $error_message = $result['message'] ?? 'Failed to update profile. Please try again.';
                            $edit_mode = true;
                        }
                    }
                } else {
                    // Email not changed, proceed with update
                    $result = update('users', $update_data, $user_id);
                    
                    if ($result['success']) {
                        // Update session data for immediate reflection
                        $_SESSION['user_first_name'] = $update_data['first_name'];
                        $_SESSION['user_last_name'] = $update_data['last_name'];
                        $_SESSION['user_email'] = $update_data['email'];
                        
                        $success_message = 'Profile updated successfully!';
                        $edit_mode = false;
                    } else {
                        $error_message = $result['message'] ?? 'Failed to update profile. Please try again.';
                        $edit_mode = true;
                    }
                }
            }
        } elseif ($_POST['action'] === 'cancel') {
            $edit_mode = false;
        }
    }
}

// Check if edit mode is requested via GET
if (isset($_GET['edit']) && $_GET['edit'] === '1') {
    $edit_mode = true;
}

// Get current user data (fresh from database)
$current_user = get_logged_in_user();

$page_title = "My Profile";
?>