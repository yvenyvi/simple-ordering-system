<?php
// Get current page to set active navigation
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside class="admin-sidebar">
    <div class="admin-header">
        <h2><i class="fas fa-cogs"></i> Admin Panel</h2>
    </div>
    <nav class="admin-nav">
        <ul>
            <li>
                <a href="index.php" class="nav-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="menu_list.php" class="nav-item <?php echo ($current_page == 'menu_list.php') ? 'active' : ''; ?>">
                    <i class="fas fa-utensils"></i> Menu Management
                </a>
            </li>
            <li>
                <a href="user_list.php" class="nav-item <?php echo ($current_page == 'user_list.php') ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> User Management
                </a>
            </li>
            <li>
                <a href="event_list.php" class="nav-item <?php echo ($current_page == 'event_list.php') ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Event Management
                </a>
            </li>
            <li>
                <a href="../pages/index.php" class="nav-item">
                    <i class="fas fa-home"></i> Back to Site
                </a>
            </li>
        </ul>
    </nav>
</aside>