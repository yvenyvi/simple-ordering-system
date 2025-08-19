<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Delicious Eats - Online Food Ordering</title>
  <link rel="stylesheet" href="../assets/css/main.css" />

  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />

</head>

<body>

  <?php
  include '../includes/header.php';
  include '../includes/sidebar.php';
  ?>

  <!-- Main Content -->
  <main> <!-- Showcase Section -->
    <section class="showcase">
      <div class="container">
        <h2>Delicious Food Delivered to Your Door</h2>
        <p>Experience the finest culinary delights with our easy online ordering system. From comfort food to gourmet dishes!</p>
        <a href="products.php" class="btn">View Our Menu</a>
      </div>
    </section>

    <!-- Featured Products Section -->
    <section class="products">
      <div class="container">
        <h2>Featured Dishes</h2>
        <div class="product-grid">
          <?php
          require_once '../models/db_Model.php';
          
          // Display featured products from database using the enhanced db_model
          display_featured_products(4);
          ?>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="features">
      <div class="container">
        <h2>Why Choose Us</h2>
        <div class="features-grid">
          <div class="feature">
            <i class="fas fa-utensils"></i>
            <h3>Quality Ingredients</h3>
            <p>
              We use only the freshest, highest-quality ingredients in all our dishes.
            </p>
          </div>

          <div class="feature">
            <i class="fas fa-shipping-fast"></i>
            <h3>Hot & Fresh Delivery</h3>
            <p>Our special packaging ensures your food arrives hot and fresh every time.</p>
          </div>

          <div class="feature">
            <i class="fas fa-mobile-alt"></i>
            <h3>Easy Online Ordering</h3>
            <p>Order your favorite meals with just a few clicks on any device.</p>
          </div>

          <div class="feature">
            <i class="fas fa-clock"></i>
            <h3>Quick Preparation</h3>
            <p>Our expert chefs prepare your orders quickly without compromising quality.</p>
          </div>
        </div>
      </div>
    </section>
  </main>

  <?php
  include '../includes/footer.php';
  ?>

  <script src="../assets/js/sidebar.js"></script>
</body>

</html>