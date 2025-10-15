<?php
$page_title = "User Management";
include 'includes/header.php';
require_once 'controller/user_list.php';

?>

<body>
    <div class="admin-container">
        <!-- Admin Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- User Management Section -->
            <section id="user-management" class="admin-section active">
                <div class="section-header">
                    <h1>User Management</h1>
                    <button class="btn btn-primary" onclick="showAddUserForm()">
                        <i class="fas fa-plus"></i> Add New User
                    </button>
                </div>

                <!-- Add User Form -->
                <div id="add-user-form" class="form-container" style="display: none;">
                    <h3>Add New User</h3>
                    <form action="user_list.php" method="post" id="userForm">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="user-firstname">First Name *</label>
                                <input type="text" id="user-firstname" name="first_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="user-lastname">Last Name *</label>
                                <input type="text" id="user-lastname" name="last_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="user-email">Email *</label>
                                <input type="email" id="user-email" name="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="user-phone">Phone</label>
                                <input type="tel" id="user-phone" name="phone" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="user-password">Password *</label>
                            <input type="password" id="user-password" name="password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="user-address">Address</label>
                            <textarea id="user-address" name="address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="user-city">City</label>
                                <input type="text" id="user-city" name="city" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="user-state">State</label>
                                <input type="text" id="user-state" name="state" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="user-zip">ZIP Code</label>
                                <input type="text" id="user-zip" name="zip_code" class="form-control">
                            </div>
                        </div>
                        <div class="form-group checkbox-group">
                            <label class="form-check-label">
                                <input type="checkbox" id="user-active" name="is_active" class="form-check-input" checked>
                                Active account
                            </label>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Save User
                            </button>
                            <button type="button" class="btn btn-secondary ms-2" onclick="hideAddUserForm()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Users List -->
                <div class="table-container">
                    <?php
                    $sql = "SELECT * FROM users ORDER BY created_at DESC";
                    display_users_table($sql);
                    ?>
                </div>
            </section>

        </main>
    </div>

    <!-- User View Details Modal -->
    <div class="modal fade" id="userViewModal" tabindex="-1" aria-labelledby="userViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userViewModalLabel">
                        <i class="fas fa-user"></i> User Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row" id="userViewContent">
                        <!-- User details will be loaded here -->
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- User Edit Modal -->
    <div class="modal fade" id="userEditModal" tabindex="-1" aria-labelledby="userEditModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userEditModalLabel">
                        <i class="fas fa-edit"></i> Edit User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editUserForm" method="POST" action="controller/user_list.php">
                    <div class="modal-body">
                        <input type="hidden" id="edit-user-id" name="edit_user_id" value="">
                        <input type="hidden" name="action" value="edit">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="edit-first-name" class="form-label">First Name *</label>
                                    <input type="text" class="form-control" id="edit-first-name" name="first_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="edit-last-name" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" id="edit-last-name" name="last_name" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="edit-email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="edit-email" name="email" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="edit-phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="edit-phone" name="phone">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="edit-address" class="form-label">Address</label>
                            <textarea class="form-control" id="edit-address" name="address" rows="2"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="edit-city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="edit-city" name="city">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="edit-state" class="form-label">State</label>
                                    <input type="text" class="form-control" id="edit-state" name="state">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="edit-zip-code" class="form-label">ZIP Code</label>
                                    <input type="text" class="form-control" id="edit-zip-code" name="zip_code">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit-is-active" name="is_active" value="1">
                                <label class="form-check-label" for="edit-is-active">
                                    Active User
                                </label>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            Password cannot be changed through this form for security reasons.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Existing Admin JS -->
    <script src="../assets/js/admin.js"></script>
    <!-- User Management JS -->
    <script src="../assets/js/user_management.js"></script>

    <?php include 'includes/footer.php'; ?>
