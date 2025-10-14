<?php
$page_title = "Menu Management";
include 'includes/header.php';
require_once 'controller/menu_list.php';
?>

<body>
    <div class="admin-container">
        <!-- Admin Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Menu Management Section -->
            <section id="menu-management" class="admin-section active">
                <div class="section-header">
                    <h1>Menu Management</h1>
                    <button class="btn btn-primary" onclick="showAddMenuForm()">
                        <i class="fas fa-plus"></i> Add New Item
                    </button>
                </div>

                <!-- Add Menu Form -->
                <div id="add-menu-form" class="form-container" style="display: none;">
                    <h3>Add New Menu Item</h3>
                    <form action="menu_list.php" method="post" enctype="multipart/form-data" id="menuForm">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="menu-name">Name *</label>
                                <input type="text" id="menu-name" name="name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="menu-category">Category *</label>
                                <select id="menu-category" name="category" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <option value="pizza">Pizza</option>
                                    <option value="burgers">Burgers</option>
                                    <option value="pasta">Pasta</option>
                                    <option value="salads">Salads</option>
                                    <option value="desserts">Desserts</option>
                                    <option value="beverages">Beverages</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="menu-price">Price *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" id="menu-price" name="price" class="form-control" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="menu-prep-time">Preparation Time (minutes)</label>
                                <select id="menu-prep-time" name="preparation_time" class="form-select">
                                    <option value="5">5 minutes</option>
                                    <option value="10">10 minutes</option>
                                    <option value="15" selected>15 minutes</option>
                                    <option value="20">20 minutes</option>
                                    <option value="25">25 minutes</option>
                                    <option value="30">30 minutes</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="menu-description">Description</label>
                            <textarea id="menu-description" name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="menu-ingredients">Ingredients</label>
                            <textarea id="menu-ingredients" name="ingredients" class="form-control" rows="2" placeholder="Comma-separated list of ingredients"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="fileField">Upload Image (Optional)</label>
                            <input type="file" id="fileField" name="fileField" class="form-control" accept="image/*">
                            <small class="form-text text-muted">Supported formats: JPG, JPEG, PNG, GIF, WEBP. Leave empty for no image.</small>
                        </div>
                        <div class="form-group checkbox-group">
                            <label class="form-check-label">
                                <input type="checkbox" id="menu-available" name="is_available" class="form-check-input" checked>
                                Available for order
                            </label>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Save Item
                            </button>
                            <button type="button" class="btn btn-secondary ms-2" onclick="hideAddMenuForm()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Menu Items List -->
                <div class="table-container">
                    <?php
                    $sql = "SELECT * FROM menu ORDER BY created_at DESC";
                    display_menu_table($sql);
                    ?>
                </div>
            </section>

        </main>
    </div>

    <!-- Menu Details Modal -->
    <div class="modal fade" id="menuDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-utensils"></i> Menu Item Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="menuDetailsContent">
                    <!-- Menu details will be loaded here -->
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Existing Admin JS -->
    <script src="../assets/js/admin.js"></script>
    <!-- Menu Management JS -->
    <script src="../assets/js/menu_management.js"></script>

    <!-- Handle PHP messages -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show success/error messages using Bootstrap alerts
            <?php if (isset($success_message)): ?>
                showMenuMessages('<?php echo addslashes($success_message); ?>', null);
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                showMenuMessages(null, '<?php echo addslashes($error_message); ?>');
            <?php endif; ?>
        });
    </script>

    <?php include 'includes/footer.php'; ?>