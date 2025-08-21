<?php
$page_title = "Edit Record";
require_once "../models/db_Model.php";

// Handle edit operations
if (isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $editType = 'user'; // Set edit type for redirect
    $data = array(
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address'],
        'city' => $_POST['city'],
        'state' => $_POST['state'],
        'zip_code' => $_POST['zip_code'],
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    );
    
    if (save('users', $data, 'user_id', $user_id)) {
        $success_message = "User '{$_POST['first_name']} {$_POST['last_name']}' has been successfully updated!";
    } else {
        $error_message = "Failed to update user. Please try again.";
    }
}

if (isset($_POST['update_menu'])) {
    $menu_id = $_POST['menu_id'];
    $editType = 'menu'; // Set edit type for redirect
    $data = array(
        'name' => $_POST['name'],
        'description' => $_POST['description'],
        'category' => $_POST['category'],
        'price' => floatval($_POST['price']),
        'ingredients' => $_POST['ingredients'],
        'preparation_time' => intval($_POST['preparation_time']),
        'image_url' => $_POST['image_url'],
        'is_available' => isset($_POST['is_available']) ? 1 : 0
    );
    
    if (save('menu', $data, 'menu_id', $menu_id)) {
        $success_message = "Menu item '{$_POST['name']}' has been successfully updated!";
    } else {
        $error_message = "Failed to update menu item. Please try again.";
    }
}

if (isset($_POST['update_event'])) {
    $event_id = $_POST['event_id'];
    $editType = 'event'; // Set edit type for redirect
    $data = array(
        'event_name' => $_POST['event_name'],
        'description' => $_POST['description'],
        'event_date' => $_POST['event_date'],
        'event_time' => $_POST['event_time'],
        'location' => $_POST['location'],
        'capacity' => intval($_POST['capacity']),
        'price' => floatval($_POST['price']),
        'event_type' => $_POST['event_type'],
        'contact_email' => $_POST['contact_email'],
        'contact_phone' => $_POST['contact_phone'],
        'requirements' => $_POST['requirements'],
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    );
    
    if (save('events', $data, 'event_id', $event_id)) {
        $success_message = "Event '{$_POST['event_name']}' has been successfully updated!";
    } else {
        $error_message = "Failed to update event. Please try again.";
    }
}

// Get record to edit
$editData = null;
if (!isset($editType)) {
    $editType = '';
}

if (isset($_GET['editid'])) {
    $edit_id = mysqli_real_escape_string($connection, $_GET['editid']);
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    
    if (strpos($referer, 'user_list.php') !== false) {
        $editType = 'user';
        $sql = "SELECT * FROM users WHERE user_id = '$edit_id'";
    } elseif (strpos($referer, 'menu_list.php') !== false) {
        $editType = 'menu';
        $sql = "SELECT * FROM menu WHERE menu_id = '$edit_id'";
    } elseif (strpos($referer, 'event_list.php') !== false) {
        $editType = 'event';
        $sql = "SELECT * FROM events WHERE event_id = '$edit_id'";
    }
    
    if (isset($sql)) {
        $result = mysqli_query($connection, $sql);
        $editData = mysqli_fetch_array($result);
    }
}

include 'includes/header.php';
?>

<body>
    <div class="admin-container">
        <!-- Admin Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="admin-main">
            <?php if ($editType == 'user' && $editData): ?>
            <section class="admin-section active">
                <div class="section-header">
                    <h1><i class="fas fa-user-edit"></i> Edit User Account</h1>
                    <a href="user_list.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Users
                    </a>
                </div>

                <div class="form-container">
                    <form action="edit.php" method="post" id="userEditForm" novalidate>
                        <input type="hidden" name="user_id" value="<?php echo $editData['user_id']; ?>" />
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($editData['first_name']); ?>" 
                                       required minlength="2" maxlength="50">
                                <div class="invalid-feedback">Please enter a valid first name.</div>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($editData['last_name']); ?>" 
                                       required minlength="2" maxlength="50">
                                <div class="invalid-feedback">Please enter a valid last name.</div>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($editData['email']); ?>" 
                                       required maxlength="100">
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($editData['phone']); ?>" 
                                       maxlength="20">
                                <div class="invalid-feedback">Please enter a valid phone number.</div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" class="form-control" rows="3" 
                                      maxlength="255"><?php echo htmlspecialchars($editData['address']); ?></textarea>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" class="form-control" 
                                       value="<?php echo htmlspecialchars($editData['city']); ?>" 
                                       maxlength="100">
                            </div>
                            <div class="form-group">
                                <label for="state">State</label>
                                <input type="text" id="state" name="state" class="form-control" 
                                       value="<?php echo htmlspecialchars($editData['state']); ?>" 
                                       maxlength="50">
                            </div>
                            <div class="form-group">
                                <label for="zip_code">ZIP Code</label>
                                <input type="text" id="zip_code" name="zip_code" class="form-control" 
                                       value="<?php echo htmlspecialchars($editData['zip_code']); ?>" 
                                       pattern="[0-9]{5}(-[0-9]{4})?" maxlength="10">
                                <div class="invalid-feedback">Please enter a valid ZIP code.</div>
                            </div>
                        </div>

                        <div class="form-group checkbox-group">
                            <label class="form-check-label">
                                <input type="checkbox" name="is_active" class="form-check-input" 
                                       <?php echo $editData['is_active'] ? 'checked' : ''; ?>>
                                Active Account
                            </label>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="update_user" class="btn btn-success">
                                <i class="fas fa-save"></i> Update User
                            </button>
                            <a href="user_list.php" class="btn btn-secondary ms-2">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </section>
            <?php elseif ($editType == 'menu' && $editData): ?>
            <section class="admin-section active">
                <div class="section-header">
                    <h1><i class="fas fa-utensils"></i> Edit Menu Item</h1>
                    <a href="menu_list.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Menu
                    </a>
                </div>

                <div class="form-container">
                    <form action="edit.php" method="post" id="menuEditForm" novalidate>
                        <input type="hidden" name="menu_id" value="<?php echo $editData['menu_id']; ?>" />
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="name">Item Name *</label>
                                <input type="text" id="name" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($editData['name']); ?>" 
                                       required minlength="2" maxlength="100">
                                <div class="invalid-feedback">Please enter a valid item name.</div>
                            </div>
                            <div class="form-group">
                                <label for="category">Category *</label>
                                <select id="category" name="category" class="form-select" required>
                                    <option value="pizza" <?php echo ($editData['category'] == 'pizza') ? 'selected' : ''; ?>>Pizza</option>
                                    <option value="burgers" <?php echo ($editData['category'] == 'burgers') ? 'selected' : ''; ?>>Burgers</option>
                                    <option value="pasta" <?php echo ($editData['category'] == 'pasta') ? 'selected' : ''; ?>>Pasta</option>
                                    <option value="salads" <?php echo ($editData['category'] == 'salads') ? 'selected' : ''; ?>>Salads</option>
                                    <option value="desserts" <?php echo ($editData['category'] == 'desserts') ? 'selected' : ''; ?>>Desserts</option>
                                    <option value="beverages" <?php echo ($editData['category'] == 'beverages') ? 'selected' : ''; ?>>Beverages</option>
                                </select>
                                <div class="invalid-feedback">Please select a category.</div>
                            </div>
                            <div class="form-group">
                                <label for="price">Price *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" id="price" name="price" class="form-control" 
                                           value="<?php echo $editData['price']; ?>" 
                                           step="0.01" min="0.01" max="999.99" required>
                                </div>
                                <div class="invalid-feedback">Please enter a valid price.</div>
                            </div>
                            <div class="form-group">
                                <label for="preparation_time">Preparation Time</label>
                                <select id="preparation_time" name="preparation_time" class="form-select">
                                    <option value="5" <?php echo ($editData['preparation_time'] == 5) ? 'selected' : ''; ?>>5 minutes</option>
                                    <option value="10" <?php echo ($editData['preparation_time'] == 10) ? 'selected' : ''; ?>>10 minutes</option>
                                    <option value="15" <?php echo ($editData['preparation_time'] == 15) ? 'selected' : ''; ?>>15 minutes</option>
                                    <option value="20" <?php echo ($editData['preparation_time'] == 20) ? 'selected' : ''; ?>>20 minutes</option>
                                    <option value="25" <?php echo ($editData['preparation_time'] == 25) ? 'selected' : ''; ?>>25 minutes</option>
                                    <option value="30" <?php echo ($editData['preparation_time'] == 30) ? 'selected' : ''; ?>>30 minutes</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="4" 
                                      maxlength="500"><?php echo htmlspecialchars($editData['description']); ?></textarea>
                            <small class="form-text text-muted">Optional description (max 500 characters).</small>
                        </div>

                        <div class="form-group">
                            <label for="ingredients">Ingredients</label>
                            <textarea id="ingredients" name="ingredients" class="form-control" rows="3" 
                                      maxlength="300"><?php echo htmlspecialchars($editData['ingredients']); ?></textarea>
                            <small class="form-text text-muted">Comma-separated list of ingredients (max 300 characters).</small>
                        </div>

                        <div class="form-group">
                            <label for="image_url">Image URL</label>
                            <input type="url" id="image_url" name="image_url" class="form-control" 
                                   value="<?php echo htmlspecialchars($editData['image_url']); ?>" 
                                   maxlength="500">
                            <small class="form-text text-muted">Optional image URL for the menu item.</small>
                        </div>

                        <div class="form-group checkbox-group">
                            <label class="form-check-label">
                                <input type="checkbox" name="is_available" class="form-check-input" 
                                       <?php echo $editData['is_available'] ? 'checked' : ''; ?>>
                                Available for order
                            </label>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="update_menu" class="btn btn-success">
                                <i class="fas fa-save"></i> Update Menu Item
                            </button>
                            <a href="menu_list.php" class="btn btn-secondary ms-2">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </section>
            <?php elseif ($editType == 'event' && $editData): ?>
            <section class="admin-section active">
                <div class="section-header">
                    <h1><i class="fas fa-calendar-alt"></i> Edit Event</h1>
                    <a href="event_list.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Events
                    </a>
                </div>

                <div class="form-container">
                    <form action="edit.php" method="post" id="eventEditForm" novalidate>
                        <input type="hidden" name="event_id" value="<?php echo $editData['event_id']; ?>" />
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="event_name">Event Name *</label>
                                <input type="text" id="event_name" name="event_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($editData['event_name']); ?>" 
                                       required minlength="3" maxlength="100">
                                <div class="invalid-feedback">Please enter a valid event name.</div>
                            </div>
                            <div class="form-group">
                                <label for="event_type">Event Type *</label>
                                <select id="event_type" name="event_type" class="form-select" required>
                                    <option value="workshop" <?php echo ($editData['event_type'] == 'workshop') ? 'selected' : ''; ?>>Workshop</option>
                                    <option value="tasting" <?php echo ($editData['event_type'] == 'tasting') ? 'selected' : ''; ?>>Tasting</option>
                                    <option value="party" <?php echo ($editData['event_type'] == 'party') ? 'selected' : ''; ?>>Party</option>
                                    <option value="cooking_class" <?php echo ($editData['event_type'] == 'cooking_class') ? 'selected' : ''; ?>>Cooking Class</option>
                                    <option value="special_dinner" <?php echo ($editData['event_type'] == 'special_dinner') ? 'selected' : ''; ?>>Special Dinner</option>
                                    <option value="other" <?php echo ($editData['event_type'] == 'other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                                <div class="invalid-feedback">Please select an event type.</div>
                            </div>
                            <div class="form-group">
                                <label for="event_date">Event Date *</label>
                                <input type="date" id="event_date" name="event_date" class="form-control" 
                                       value="<?php echo $editData['event_date']; ?>" required>
                                <div class="invalid-feedback">Please select a valid date.</div>
                            </div>
                            <div class="form-group">
                                <label for="event_time">Event Time *</label>
                                <input type="time" id="event_time" name="event_time" class="form-control" 
                                       value="<?php echo $editData['event_time']; ?>" required>
                                <div class="invalid-feedback">Please select an event time.</div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="3" 
                                      maxlength="1000"><?php echo htmlspecialchars($editData['description']); ?></textarea>
                            <small class="form-text text-muted">Optional description (max 1000 characters).</small>
                        </div>

                        <div class="form-group">
                            <label for="location">Location *</label>
                            <input type="text" id="location" name="location" class="form-control" 
                                   value="<?php echo htmlspecialchars($editData['location']); ?>" 
                                   required maxlength="200">
                            <div class="invalid-feedback">Please enter the event location.</div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="capacity">Capacity *</label>
                                <input type="number" id="capacity" name="capacity" class="form-control" 
                                       value="<?php echo $editData['capacity']; ?>" 
                                       min="1" max="500" required>
                                <div class="invalid-feedback">Capacity must be between 1 and 500 people.</div>
                            </div>
                            <div class="form-group">
                                <label for="price">Price *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" id="price" name="price" class="form-control" 
                                           value="<?php echo $editData['price']; ?>" 
                                           step="0.01" min="0" max="9999.99" required>
                                </div>
                                <div class="invalid-feedback">Please enter a valid price.</div>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="contact_email">Contact Email</label>
                                <input type="email" id="contact_email" name="contact_email" class="form-control" 
                                       value="<?php echo htmlspecialchars($editData['contact_email']); ?>" 
                                       maxlength="100">
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>
                            <div class="form-group">
                                <label for="contact_phone">Contact Phone</label>
                                <input type="tel" id="contact_phone" name="contact_phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($editData['contact_phone']); ?>" 
                                       maxlength="20">
                                <div class="invalid-feedback">Please enter a valid phone number.</div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="requirements">Special Requirements</label>
                            <textarea id="requirements" name="requirements" class="form-control" rows="2" 
                                      maxlength="500"><?php echo htmlspecialchars($editData['requirements']); ?></textarea>
                            <small class="form-text text-muted">Optional requirements (max 500 characters).</small>
                        </div>

                        <div class="form-group checkbox-group">
                            <label class="form-check-label">
                                <input type="checkbox" name="is_active" class="form-check-input" 
                                       <?php echo $editData['is_active'] ? 'checked' : ''; ?>>
                                Active event (visible to customers)
                            </label>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="update_event" class="btn btn-success">
                                <i class="fas fa-save"></i> Update Event
                            </button>
                            <a href="event_list.php" class="btn btn-secondary ms-2">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </section>

            <?php else: ?>
            <section class="admin-section active">
                <div class="section-header">
                    <h1><i class="fas fa-exclamation-triangle"></i> Record Not Found</h1>
                </div>

                <div class="admin-table-container">
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <h4>Record Not Found</h4>
                        <p>The requested record could not be found or the request is invalid.</p>
                        <div style="margin-top: 20px;">
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
            <?php endif; ?>

        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.27/dist/sweetalert2.all.min.js"></script>
    <!-- Existing Admin JS -->
    <script src="../assets/js/admin.js"></script>

    <script>
        // Show success/error messages using Bootstrap alerts
        <?php if (isset($success_message)): ?>
            showBootstrapAlert('<?php echo addslashes($success_message); ?>', 'success', 4000);
            // Redirect back to the appropriate list page after a delay
            setTimeout(() => {
                <?php if ($editType == 'user'): ?>
                    window.location.href = 'user_list.php';
                <?php elseif ($editType == 'menu'): ?>
                    window.location.href = 'menu_list.php';
                <?php elseif ($editType == 'event'): ?>
                    window.location.href = 'event_list.php';
                <?php endif; ?>
            }, 2000);
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            showBootstrapAlert('<?php echo addslashes($error_message); ?>', 'error', 6000);
        <?php endif; ?>

        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            // Set minimum date for event date (only for event forms)
            const eventDateInput = document.getElementById('event_date');
            if (eventDateInput) {
                const today = new Date().toISOString().split('T')[0];
                eventDateInput.min = today;
            }

            // Add real-time validation
            const forms = document.querySelectorAll('form[novalidate]');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!form.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        showBootstrapAlert('Please fill in all required fields correctly.', 'error', 5000);
                    }
                    form.classList.add('was-validated');
                });
            });
        });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
