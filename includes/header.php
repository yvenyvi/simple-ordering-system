<?php
// Include db_Model for authentication functions
require_once __DIR__ . '/../models/db_Model.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get current user if logged in
$current_user = is_user_logged_in() ? get_logged_in_user() : null;
?>

<header class="main-header">
    <div class="container">
        <div class="logo">
            <h1><a href="../pages/index.php">Delicious Eats</a></h1>
        </div>
        <nav class="main-nav">
            <ul>
                <li><a href="../pages/index.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="../pages/products.php"><i class="fas fa-utensils"></i> Menu</a></li>
                <li><a href="../pages/events.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
                <li><a href="..."><i class="fas fa-info-circle"></i> About Us</a></li>
                <li><a href="..."><i class="fas fa-envelope"></i> Contact</a></li>
            </ul>
        </nav>
        <div class="user-nav">
            <?php if ($current_user): ?>
                <!-- User is logged in -->
                <div class="user-menu">
                    <a href="../pages/profile.php" class="user-greeting">
                        <i class="fas fa-user-circle"></i>
                        Hello, <?php echo htmlspecialchars($current_user['first_name']); ?>!
                    </a>
                </div>
            <?php else: ?>
                <!-- User is not logged in -->
                <div class="auth-links">
                    <a href="../pages/login.php" class="auth-link">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="../pages/register.php" class="auth-link auth-link-primary">
                        <i class="fas fa-user-plus"></i> Sign Up
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>