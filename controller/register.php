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
$form_data = [];

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Store form data for repopulation
    $form_data = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'state' => trim($_POST['state'] ?? ''),
        'zip_code' => trim($_POST['zip_code'] ?? '')
    ];
    
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate form
    $errors = [];
    
    if (empty($form_data['first_name'])) {
        $errors[] = 'First name is required';
    }
    
    if (empty($form_data['last_name'])) {
        $errors[] = 'Last name is required';
    }
    
    if (empty($form_data['email'])) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    if (empty($errors)) {
        // Add password to form data
        $form_data['password'] = $password;
        
        // Attempt registration
        $register_result = register_user($form_data);
        
        if ($register_result['success']) {
            // Redirect to login page with success message
            redirect_to('login.php?registered=1');
            exit;
        } else {
            $error_message = $register_result['message'];
        }
    } else {
        $error_message = implode(', ', $errors);
    }
}
?>