<?php
// Get current page to set active navigation
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Mobile Toggle Button -->
<button class="mobile-sidebar-toggle" id="mobileSidebarToggle">
    <i class="fas fa-bars"></i>
</button>

<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-header">
        <h2><i class="fas fa-utensils"></i> Admin Panel</h2>
        <div class="admin-subtitle">Delicious Eats Management</div>
    </div>
    <nav class="admin-nav">
        <ul>
            <li>
                <a href="index.php" class="nav-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> 
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="menu_list.php" class="nav-item <?php echo ($current_page == 'menu_list.php') ? 'active' : ''; ?>">
                    <i class="fas fa-utensils"></i> 
                    <span>Menu Management</span>
                </a>
            </li>
            <li>
                <a href="user_list.php" class="nav-item <?php echo ($current_page == 'user_list.php') ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> 
                    <span>User Management</span>
                </a>
            </li>
            <li>
                <a href="order_list.php" class="nav-item <?php echo ($current_page == 'order_list.php') ? 'active' : ''; ?>">
                    <i class="fas fa-receipt"></i> 
                    <span>Order Management</span>
                </a>
            </li>
            <li>
                <a href="event_list.php" class="nav-item <?php echo ($current_page == 'event_list.php') ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt"></i> 
                    <span>Event Management</span>
                </a>
            </li>
            <li class="nav-divider"></li>
            <li>
                <a href="../pages/index.php" class="nav-item">
                    <i class="fas fa-home"></i> 
                    <span>Back to Site</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>