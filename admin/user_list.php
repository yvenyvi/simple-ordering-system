<?php
$page_title = "User Management";
include 'includes/header.php';
require_once "../models/db_Model.php";

if (isset($_POST['first_name'])){
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip_code = $_POST['zip_code'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $newUser = "INSERT INTO users (first_name, last_name, email, password, phone, address, city, state, zip_code, is_active, created_at) 
                VALUES ('$first_name', '$last_name', '$email', '$password', '$phone', '$address', '$city', '$state', '$zip_code', '$is_active', NOW())";
    save($newUser);
    redirect_to("user_list.php");
}
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
                    <h3>Registered Users</h3>
                    <div class="items-list">
                        <?php
                        $sql = "SELECT * FROM users ORDER BY created_at DESC";
                        $column_mappings = array(
                            'user_id' => 'ID:',
                            'first_name' => 'First Name:',
                            'last_name' => 'Last Name:',
                            'email' => 'Email:',
                            'phone' => 'Phone:',
                            'is_active' => 'Status:',
                            'created_at' => 'Joined:'
                        );
                        display_all($sql, $column_mappings, 'user_list.php');
                        ?>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Existing Admin JS -->
    <script src="../assets/js/admin.js"></script>

    <!-- Enhanced Bootstrap functionality -->
    <script>
        function showAddUserForm() {
            document.getElementById('add-user-form').style.display = 'block';
            document.getElementById('user-firstname').focus();
        }

        function hideAddUserForm() {
            document.getElementById('add-user-form').style.display = 'none';
        }

        // Auto-hide form after page load if there's no error
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (!urlParams.has('error')) {
                hideAddUserForm();
            }
        });
    </script>

    <?php include 'includes/footer.php'; ?>
