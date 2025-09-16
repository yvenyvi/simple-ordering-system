<?php
$page_title = "Edit Record";
include 'includes/header.php';
?>

<body>
    <div class="admin-container">
        <!-- Admin Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="admin-main">
            <section class="admin-section active">
                <div class="section-header">
                    <h1><i class="fas fa-tools"></i> Update Functionality</h1>
                </div>

                <div class="admin-table-container">
                    <div class="empty-state">
                        <i class="fas fa-cogs" style="font-size: 4rem; color: #6c757d; margin-bottom: 20px;"></i>
                        <h3>Update Functionality Coming Soon...</h3>
                        <p>We're working hard to bring you the update functionality. This feature will be available in the next release.</p>
                        <p>For now, you can:</p>
                        <ul style="text-align: left; display: inline-block; margin: 20px 0;">
                            <li>Create new records using the "Add New" buttons</li>
                            <li>Delete existing records if needed</li>
                            <li>View all current records in the management pages</li>
                        </ul>
                        <div style="margin-top: 30px;">
                            <a href="index.php" class="btn btn-primary">
                                <i class="fas fa-tachometer-alt"></i> Admin Dashboard
                            </a>
                            <a href="user_list.php" class="btn btn-secondary ms-2">
                                <i class="fas fa-users"></i> User Management
                            </a>
                            <a href="menu_list.php" class="btn btn-secondary ms-2">
                                <i class="fas fa-utensils"></i> Menu Management
                            </a>
                            <a href="event_list.php" class="btn btn-secondary ms-2">
                                <i class="fas fa-calendar-alt"></i> Event Management
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Existing Admin JS -->
    <script src="../assets/js/admin.js"></script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
