<?php

require_once '../models/db_Model.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set JSON header
header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!is_user_logged_in()) {
        // User not logged in - return empty data
        echo json_encode([
            'loggedIn' => false,
            'firstName' => '',
            'lastName' => '',
            'email' => '',
            'phone' => '',
            'address' => '',
            'city' => '',
            'state' => '',
            'zipCode' => ''
        ]);
        exit;
    }
    
    // Get complete user data from database
    $user = get_logged_in_user();
    
    if ($user) {
        // Return user details
        echo json_encode([
            'loggedIn' => true,
            'firstName' => $user['first_name'] ?? '',
            'lastName' => $user['last_name'] ?? '',
            'email' => $user['email'] ?? '',
            'phone' => $user['phone'] ?? '',
            'address' => $user['address'] ?? '',
            'city' => $user['city'] ?? '',
            'state' => $user['state'] ?? '',
            'zipCode' => $user['zip_code'] ?? ''
        ]);
    } else {
        // User data not found
        echo json_encode([
            'loggedIn' => false,
            'firstName' => '',
            'lastName' => '',
            'email' => '',
            'phone' => '',
            'address' => '',
            'city' => '',
            'state' => '',
            'zipCode' => ''
        ]);
    }
} catch (Exception $e) {
    // Error occurred
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'An error occurred while fetching user details'
    ]);
}
