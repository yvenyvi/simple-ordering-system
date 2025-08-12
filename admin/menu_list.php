<?php
$page_title = "Menu Management";
include 'includes/header.php';
require_once "../models/db_Model.php";

// Handle delete request
if (isset($_GET['deleteid'])) {
    $delete_id = $_GET['deleteid'];
    
    // Get the image URL before deleting to remove the file
    global $connection;
    $get_image_sql = "SELECT image_url FROM menu WHERE menu_id = '$delete_id'";
    $image_result = mysqli_query($connection, $get_image_sql);
    $image_row = mysqli_fetch_array($image_result);
    
    // Delete the record
    $delete_sql = "DELETE FROM menu WHERE menu_id = '$delete_id'";
    mysqli_query($connection, $delete_sql);
    
    // Remove the image file if it exists and is not a placeholder
    if ($image_row && $image_row['image_url'] && file_exists($image_row['image_url'])) {
        unlink($image_row['image_url']);
    }
    
    redirect_to("menu_list.php");
}

if (isset($_POST['name'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $ingredients = $_POST['ingredients'];
    $preparation_time = $_POST['preparation_time'];
    $is_available = isset($_POST['is_available']) ? 1 : 0;

    $newMenuItem = "INSERT INTO menu (name, description, category, price, ingredients, preparation_time, is_available, created_at) 
                    VALUES ('$name', '$description', '$category', '$price', '$ingredients', '$preparation_time', '$is_available', NOW())";
    save($newMenuItem);
    redirect_to("menu_list.php");
}
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
                    <h3>Menu Items</h3>
                    <div class="items-list">
                        <?php
                        $sql = "SELECT * FROM menu ORDER BY created_at DESC";
                        $column_mappings = array(
                            'menu_id' => 'ID:',
                            'name' => 'Name:',
                            'category' => 'Category:',
                            'price' => 'Price: $',
                            'is_available' => 'Available:',
                            'created_at' => 'Created:'
                        );
                        display_all($sql, $column_mappings, 'menu_list.php');
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
        function showAddMenuForm() {
            document.getElementById('add-menu-form').style.display = 'block';
            document.getElementById('menu-name').focus();
        }

        function hideAddMenuForm() {
            document.getElementById('add-menu-form').style.display = 'none';
            // Reset form when hiding
            document.getElementById('menuForm').reset();
        }

        // Auto-hide form after page load if there's no error
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (!urlParams.has('error')) {
                hideAddMenuForm();
            }
        });
    </script>

    <?php include 'includes/footer.php'; ?>