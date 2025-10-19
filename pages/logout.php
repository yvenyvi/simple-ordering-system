<?php
require_once '../models/db_Model.php';

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Logout user
logout_user();

// Redirect to home page
redirect_to('index.php');
exit;
?>