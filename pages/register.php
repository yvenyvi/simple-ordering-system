<?php
require_once '../controller/register.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Delicious Eats</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="auth-main register-layout">
        <div class="auth-split-container">
            <!-- Brand Side (Left) -->
            <div class="auth-brand-side">
                <div class="auth-brand-content">
                    <h1 class="auth-brand-title">Delicious Eats</h1>
                    <h2 class="auth-brand-subtitle">Join Our Culinary Community</h2>
                    <p class="auth-brand-tagline">
                        Discover a world of flavors, connect with fellow food enthusiasts, 
                        and enjoy exclusive access to our premium dining experiences.
                    </p>
                    
                    <div class="auth-brand-features">
                        <div class="brand-feature">
                            <i class="fas fa-crown"></i>
                            <span>VIP Access</span>
                        </div>
                        <div class="brand-feature">
                            <i class="fas fa-gift"></i>
                            <span>Special Offers</span>
                        </div>
                        <div class="brand-feature">
                            <i class="fas fa-star"></i>
                            <span>Rewards Program</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Form Side (Right) -->
            <div class="auth-form-side">
                <div class="auth-form-container">
                    <div class="auth-header">
                        <h2><i class="fas fa-user-plus"></i> Create Account</h2>
                        <p>Join Delicious Eats and start ordering your favorite meals!</p>
                    </div>

                    <!-- Display Error Messages -->
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                <form class="auth-form" method="POST" action="register.php">
                    <div class="auth-form-grid">
                    <!-- Personal Information -->
                    <div class="form-section">
                        <h3><i class="fas fa-user"></i> Personal Information</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">
                                    <i class="fas fa-user"></i> First Name *
                                </label>
                                <input 
                                    type="text" 
                                    id="first_name" 
                                    name="first_name" 
                                    required 
                                    placeholder="First name"
                                    value="<?php echo htmlspecialchars($form_data['first_name'] ?? ''); ?>"
                                    autocomplete="given-name"
                                >
                            </div>

                            <div class="form-group">
                                <label for="last_name">
                                    <i class="fas fa-user"></i> Last Name *
                                </label>
                                <input 
                                    type="text" 
                                    id="last_name" 
                                    name="last_name" 
                                    required 
                                    placeholder="Last name"
                                    value="<?php echo htmlspecialchars($form_data['last_name'] ?? ''); ?>"
                                    autocomplete="family-name"
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Email Address *
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                required 
                                placeholder="your.email@example.com"
                                value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                                autocomplete="email"
                            >
                        </div>

                        <div class="form-group">
                            <label for="phone">
                                <i class="fas fa-phone"></i> Phone Number
                            </label>
                            <input 
                                type="tel" 
                                id="phone" 
                                name="phone" 
                                placeholder="(555) 123-4567"
                                value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>"
                                autocomplete="tel"
                            >
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="form-section">
                        <h3><i class="fas fa-map-marker-alt"></i> Address Information</h3>
                        
                        <div class="form-group">
                            <label for="address">
                                <i class="fas fa-home"></i> Street Address
                            </label>
                            <input 
                                type="text" 
                                id="address" 
                                name="address" 
                                placeholder="123 Main Street"
                                value="<?php echo htmlspecialchars($form_data['address'] ?? ''); ?>"
                                autocomplete="street-address"
                            >
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">
                                    <i class="fas fa-city"></i> City
                                </label>
                                <input 
                                    type="text" 
                                    id="city" 
                                    name="city" 
                                    placeholder="City"
                                    value="<?php echo htmlspecialchars($form_data['city'] ?? ''); ?>"
                                    autocomplete="address-level2"
                                >
                            </div>

                            <div class="form-group">
                                <label for="state">
                                    <i class="fas fa-flag"></i> State
                                </label>
                                <input 
                                    type="text" 
                                    id="state" 
                                    name="state" 
                                    placeholder="State"
                                    value="<?php echo htmlspecialchars($form_data['state'] ?? ''); ?>"
                                    autocomplete="address-level1"
                                >
                            </div>

                            <div class="form-group">
                                <label for="zip_code">
                                    <i class="fas fa-mail-bulk"></i> ZIP Code
                                </label>
                                <input 
                                    type="text" 
                                    id="zip_code" 
                                    name="zip_code" 
                                    placeholder="12345"
                                    value="<?php echo htmlspecialchars($form_data['zip_code'] ?? ''); ?>"
                                    autocomplete="postal-code"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Security Information -->
                    <div class="form-section form-section-full">
                        <h3><i class="fas fa-lock"></i> Security Information</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">
                                    <i class="fas fa-lock"></i> Password *
                                </label>
                                <div class="password-input">
                                    <input 
                                        type="password" 
                                        id="password" 
                                        name="password" 
                                        required 
                                        placeholder="At least 6 characters"
                                        autocomplete="new-password"
                                        minlength="6"
                                    >
                                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                        <i class="fas fa-eye" id="password-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="confirm_password">
                                    <i class="fas fa-lock"></i> Confirm Password *
                                </label>
                                <div class="password-input">
                                    <input 
                                        type="password" 
                                        id="confirm_password" 
                                        name="confirm_password" 
                                        required 
                                        placeholder="Confirm your password"
                                        autocomplete="new-password"
                                        minlength="6"
                                    >
                                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                        <i class="fas fa-eye" id="confirm_password-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="password-requirements">
                            <p><i class="fas fa-info-circle"></i> Password Requirements:</p>
                            <ul>
                                <li>At least 6 characters long</li>
                                <li>Mix of letters and numbers recommended</li>
                            </ul>
                        </div>
                    </div>
                    </div> <!-- End auth-form-grid -->

                    <div class="auth-actions">
                        <button type="submit" class="auth-btn">
                            <span><i class="fas fa-user-plus"></i> Create Account</span>
                        </button>
                    </div>
                </form>

                <div class="auth-link">
                    <p>Already have an account? <a href="login.php">Sign in here</a></p>
                    <p><a href="index.php">← Back to Home</a></p>
                </div>
            </div>
        </div>
        </div>
    </main>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const eye = document.getElementById(inputId + '-eye');
            
            if (input.type === 'password') {
                input.type = 'text';
                eye.classList.remove('fa-eye');
                eye.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                eye.classList.remove('fa-eye-slash');
                eye.classList.add('fa-eye');
            }
        }

        // Password confirmation validation
        document.addEventListener('DOMContentLoaded', function() {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            function validatePasswordMatch() {
                if (confirmPassword.value && password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
            
            password.addEventListener('input', validatePasswordMatch);
            confirmPassword.addEventListener('input', validatePasswordMatch);
            
            // Focus on first name field when page loads
            const firstNameInput = document.getElementById('first_name');
            if (firstNameInput && !firstNameInput.value) {
                firstNameInput.focus();
            }
        });
    </script>
</body>
</html>