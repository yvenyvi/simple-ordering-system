<?php
require_once '../controller/profile.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Delicious Eats</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="../assets/js/header.js"></script>
    <script src="../assets/js/profile.js"></script>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <section class="profile-section">
            <div class="container">
                <div class="page-header">
                    <h1><i class="fas fa-user-circle"></i> My Profile</h1>
                    <p><?php echo $edit_mode ? 'Edit your account information' : 'Manage your account information and preferences'; ?></p>
                </div>

                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <div class="profile-content">
                    <div class="profile-card">
                        <div class="profile-header">
                            <div class="profile-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="profile-info">
                                <h2><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></h2>
                                <p><?php echo htmlspecialchars($current_user['email']); ?></p>
                                <span class="member-since">
                                    <i class="fas fa-calendar"></i>
                                    Member since <?php echo date('F Y', strtotime($current_user['created_at'] ?? 'now')); ?>
                                </span>
                            </div>
                        </div>

                        <?php if (!$edit_mode): ?>
                        <!-- View Mode -->
                        <div class="profile-details">
                            <h3><i class="fas fa-user"></i> Personal Information</h3>
                            <div class="details-grid">
                                <div class="detail-item">
                                    <label>First Name:</label>
                                    <span><?php echo htmlspecialchars($current_user['first_name']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Last Name:</label>
                                    <span><?php echo htmlspecialchars($current_user['last_name']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Email Address:</label>
                                    <span><?php echo htmlspecialchars($current_user['email']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Phone Number:</label>
                                    <span><?php echo htmlspecialchars($current_user['phone'] ?? 'Not provided'); ?></span>
                                </div>
                            </div>

                            <h3><i class="fas fa-map-marker-alt"></i> Address Information</h3>
                            <div class="details-grid">
                                <div class="detail-item">
                                    <label>Street Address:</label>
                                    <span><?php echo htmlspecialchars($current_user['address'] ?? 'Not provided'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>City:</label>
                                    <span><?php echo htmlspecialchars($current_user['city'] ?? 'Not provided'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>State:</label>
                                    <span><?php echo htmlspecialchars($current_user['state'] ?? 'Not provided'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>ZIP Code:</label>
                                    <span><?php echo htmlspecialchars($current_user['zip_code'] ?? 'Not provided'); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="profile-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="edit">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Edit Profile
                                </button>
                            </form>
                            <a href="orders.php" class="btn btn-secondary">
                                <i class="fas fa-shopping-bag"></i> View Orders
                            </a>
                        </div>

                        <?php else: ?>
                        <!-- Edit Mode -->
                        <form method="POST" class="profile-edit-form">
                            <input type="hidden" name="action" value="update">
                            
                            <div class="profile-details">
                                <h3><i class="fas fa-user"></i> Personal Information</h3>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="first_name">First Name *</label>
                                        <input type="text" id="first_name" name="first_name" 
                                               value="<?php echo htmlspecialchars($current_user['first_name']); ?>" 
                                               required maxlength="50">
                                    </div>
                                    <div class="form-group">
                                        <label for="last_name">Last Name *</label>
                                        <input type="text" id="last_name" name="last_name" 
                                               value="<?php echo htmlspecialchars($current_user['last_name']); ?>" 
                                               required maxlength="50">
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email Address *</label>
                                        <input type="email" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($current_user['email']); ?>" 
                                               required maxlength="100">
                                    </div>
                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <input type="tel" id="phone" name="phone" 
                                               value="<?php echo htmlspecialchars($current_user['phone'] ?? ''); ?>" 
                                               maxlength="15" placeholder="(123) 456-7890">
                                    </div>
                                </div>

                                <h3><i class="fas fa-map-marker-alt"></i> Address Information</h3>
                                <div class="form-grid">
                                    <div class="form-group full-width">
                                        <label for="address">Street Address</label>
                                        <input type="text" id="address" name="address" 
                                               value="<?php echo htmlspecialchars($current_user['address'] ?? ''); ?>" 
                                               maxlength="255" placeholder="123 Main Street">
                                    </div>
                                    <div class="form-group">
                                        <label for="city">City</label>
                                        <input type="text" id="city" name="city" 
                                               value="<?php echo htmlspecialchars($current_user['city'] ?? ''); ?>" 
                                               maxlength="50" placeholder="City">
                                    </div>
                                    <div class="form-group">
                                        <label for="state">State</label>
                                        <input type="text" id="state" name="state" 
                                               value="<?php echo htmlspecialchars($current_user['state'] ?? ''); ?>" 
                                               maxlength="50" placeholder="State">
                                    </div>
                                    <div class="form-group">
                                        <label for="zip_code">ZIP Code</label>
                                        <input type="text" id="zip_code" name="zip_code" 
                                               value="<?php echo htmlspecialchars($current_user['zip_code'] ?? ''); ?>" 
                                               maxlength="10" placeholder="12345">
                                    </div>
                                </div>
                            </div>

                            <div class="profile-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                                <button type="submit" name="action" value="cancel" class="btn btn-secondary" onclick="return confirmCancel()">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </div>
                        </form>
                        <?php endif; ?>

                        <!-- Logout link -->
                        <div class="profile-footer">
                            <a href="logout.php" class="logout-link">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>