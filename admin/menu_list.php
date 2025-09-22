<?php
$page_title = "Menu Management";
include 'includes/header.php';
require_once "../models/db_Model.php"; // Now includes universal table display function

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
    if ($image_row && $image_row['image_url'] && strpos($image_row['image_url'], 'placeholder.jpg') === false) {
        $image_path = $image_row['image_url'];
        
        // Handle both relative and absolute paths
        if (!file_exists($image_path)) {
            // Try relative path from current directory
            $image_path = "../" . $image_row['image_url'];
        }
        
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    redirect_to("menu_list.php");
}

if (isset($_POST['name'])) {
    $data = array(
        'name' => $_POST['name'],
        'description' => $_POST['description'],
        'category' => $_POST['category'],
        'price' => floatval($_POST['price']),
        'ingredients' => $_POST['ingredients'],
        'preparation_time' => intval($_POST['preparation_time']),
        'is_available' => isset($_POST['is_available']) ? 1 : 0
    );

    $new_id = save('menu', $data);
    
    if ($new_id) {
        // If a file was uploaded, update the database with the image URL
        if (isset($_FILES['fileField']) && $_FILES['fileField']['tmp_name']) {
            $image_url = "../assets/images/products/{$new_id}.jpg";
            
            // Update the menu record with the image URL
            global $connection;
            $update_sql = "UPDATE menu SET image_url = '" . mysqli_real_escape_string($connection, $image_url) . "' WHERE menu_id = '$new_id'";
            mysqli_query($connection, $update_sql);
            
            $success_message = "Menu item '{$_POST['name']}' has been successfully added with image!";
        } else {
            $success_message = "Menu item '{$_POST['name']}' has been successfully added!";
        }
    } else {
        $error_message = "Failed to add menu item. Please try again.";
    }
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
                    <?php
                    $sql = "SELECT * FROM menu ORDER BY created_at DESC";
                    display_menu_table($sql);
                    ?>
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

            // Show success/error messages using Bootstrap alerts
            <?php if (isset($success_message)): ?>
                showBootstrapAlert('<?php echo addslashes($success_message); ?>', 'success', 4000);
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                showBootstrapAlert('<?php echo addslashes($error_message); ?>', 'error', 6000);
            <?php endif; ?>
        });

        // Filter Functions
        function applyFilters() {
            const categoryFilter = document.getElementById('category-filter').value.toLowerCase();
            const statusFilter = document.getElementById('status-filter').value;
            const searchFilter = document.getElementById('search-filter').value.toLowerCase();
            
            const tableRows = document.querySelectorAll('.table tbody tr');
            let visibleCount = 0;
            
            tableRows.forEach(row => {
                let shouldShow = true;
                
                // Category filter
                if (categoryFilter && shouldShow) {
                    const categoryCell = row.cells[2]; // Category column
                    if (categoryCell && !categoryCell.textContent.toLowerCase().includes(categoryFilter)) {
                        shouldShow = false;
                    }
                }
                
                // Status filter
                if (statusFilter !== '' && shouldShow) {
                    const statusCell = row.cells[6]; // Availability column
                    const isAvailable = statusCell && statusCell.textContent.includes('Available');
                    if (statusFilter === '1' && !isAvailable) {
                        shouldShow = false;
                    } else if (statusFilter === '0' && isAvailable) {
                        shouldShow = false;
                    }
                }
                
                // Search filter
                if (searchFilter && shouldShow) {
                    const nameCell = row.cells[1]; // Name column
                    const descCell = row.cells[3]; // Description column
                    const nameText = nameCell ? nameCell.textContent.toLowerCase() : '';
                    const descText = descCell ? descCell.textContent.toLowerCase() : '';
                    
                    if (!nameText.includes(searchFilter) && !descText.includes(searchFilter)) {
                        shouldShow = false;
                    }
                }
                
                if (shouldShow) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update results count
            updateResultsCount(visibleCount, tableRows.length);
        }
        
        function clearFilters() {
            document.getElementById('category-filter').value = '';
            document.getElementById('status-filter').value = '';
            document.getElementById('search-filter').value = '';
            applyFilters();
        }
        
        function updateResultsCount(visible, total) {
            let countDisplay = document.getElementById('results-count');
            if (!countDisplay) {
                countDisplay = document.createElement('div');
                countDisplay.id = 'results-count';
                countDisplay.className = 'results-count';
                document.querySelector('.table-container').insertBefore(countDisplay, document.querySelector('.table'));
            }
            
            if (visible === total) {
                countDisplay.innerHTML = `<i class="fas fa-list"></i> Showing all ${total} menu items`;
            } else {
                countDisplay.innerHTML = `<i class="fas fa-filter"></i> Showing ${visible} of ${total} menu items`;
            }
        }
    </script>

    <?php include 'includes/footer.php'; ?>