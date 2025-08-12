<?php
$page_title = "Admin Dashboard";
include 'includes/header.php';
require_once "../models/db_Model.php";

// Get stats using existing db_Model
global $connection;

// Count menu items
$menuResult = mysqli_query($connection, "SELECT COUNT(*) as count FROM menu");
$menuCount = mysqli_fetch_array($menuResult)['count'];

// Count users
$userResult = mysqli_query($connection, "SELECT COUNT(*) as count FROM users");
$userCount = mysqli_fetch_array($userResult)['count'];

// Count available menu items
$availableResult = mysqli_query($connection, "SELECT COUNT(*) as count FROM menu WHERE is_available = 1");
$availableCount = mysqli_fetch_array($availableResult)['count'];
?>

<body>
    <div class="admin-container">
        <!-- Admin Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Dashboard Section -->
            <section id="dashboard" class="admin-section active">
                <div class="section-header">
                    <h1>Dashboard</h1>
                </div>
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fas fa-utensils"></i>
                        <div class="stat-info">
                            <h3 id="total-menu-items"><?php echo $menuCount; ?></h3>
                            <p>Menu Items</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-users"></i>
                        <div class="stat-info">
                            <h3 id="total-users"><?php echo $userCount; ?></h3>
                            <p>Registered Users</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-chart-line"></i>
                        <div class="stat-info">
                            <h3 id="total-categories"><?php echo $availableCount; ?></h3>
                            <p>Available Items</p>
                        </div>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <?php include 'includes/footer.php'; ?>