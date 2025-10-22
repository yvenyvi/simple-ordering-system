<?php
require_once '../controller/login.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Delicious Eats</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="auth-main login-layout">
        <div class="auth-split-container">
            <!-- Form Side (Left) -->
            <div class="auth-form-side">
                <div class="auth-form-container">
                    <div class="auth-header">
                        <h2><i class="fas fa-sign-in-alt"></i> Sign In</h2>
                        <p>Welcome back! Please sign in to your account.</p>
                    </div>

                    <!-- Display Messages -->
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>

                    <form class="auth-form" method="POST" action="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>">
                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Email Address
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                required 
                                placeholder="Enter your email"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                autocomplete="email"
                            >
                        </div>

                        <div class="form-group">
                            <label for="password">
                                <i class="fas fa-lock"></i> Password
                            </label>
                            <div class="password-input">
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    required 
                                    placeholder="Enter your password"
                                    autocomplete="current-password"
                                >
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="fas fa-eye" id="password-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="auth-actions">
                            <button type="submit" class="auth-btn">
                                <span><i class="fas fa-sign-in-alt"></i> Sign In</span>
                            </button>
                        </div>
                    </form>

                    <div class="auth-link">
                        <p>Don't have an account? <a href="register.php">Create one here</a></p>
                        <p><a href="index.php">← Back to Home</a></p>
                    </div>
                </div>
            </div>
            
            <!-- Brand Side (Right) -->
            <div class="auth-brand-side">
                <div class="auth-brand-content">
                    <h1 class="auth-brand-title">Delicious Eats</h1>
                    <h2 class="auth-brand-subtitle">Your Culinary Journey Awaits</h2>
                    <p class="auth-brand-tagline">
                        Experience the finest flavors, crafted with passion and served with love. 
                        Join thousands of food lovers who trust us for their dining adventures.
                    </p>
                    
                    <div class="auth-brand-features">
                        <div class="brand-feature">
                            <i class="fas fa-utensils"></i>
                            <span>Premium Quality</span>
                        </div>
                        <div class="brand-feature">
                            <i class="fas fa-shipping-fast"></i>
                            <span>Fast Delivery</span>
                        </div>
                        <div class="brand-feature">
                            <i class="fas fa-heart"></i>
                            <span>Made with Love</span>
                        </div>
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

        // Focus on email field when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            if (emailInput && !emailInput.value) {
                emailInput.focus();
            }
        });
    </script>
</body>
</html>