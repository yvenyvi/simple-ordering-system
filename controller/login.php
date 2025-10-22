<?php
require_once '../models/db_Model.php';

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// If user is already logged in, redirect to home page
if (is_user_logged_in()) {
    redirect_to('index.php');
    exit;
}

$error_message = '';
$success_message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error_message = 'Please enter both email and password';
    } else {
        $auth_result = authenticate_user($email, $password);
        
        if ($auth_result['success']) {
            // Start user session
            start_user_session($auth_result['user']);
            
            // Redirect to original page or home
            $redirect_url = $_GET['redirect'] ?? 'index.php';
            redirect_to($redirect_url);
            exit;
        } else {
            $error_message = $auth_result['message'];
        }
    }
}

// Handle success message from registration
if (isset($_GET['registered']) && $_GET['registered'] === '1') {
    $success_message = 'Account created successfully! Please log in.';
}
?>