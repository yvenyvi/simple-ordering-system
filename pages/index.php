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
          <!-- Beef Burger -->
          <div class="product-card">
            <img
              src="../assets/images/products/placeholder.jpg"
              alt="Beef Burger" />
            <h3>Beef Burger</h3>
            <p>Juicy beef patty with lettuce, tomato, cheese, and our special sauce on a brioche bun.</p>
            <p class="price">$12.99</p>
            <a href="#" class="btn">Add to Cart</a>
          </div>

          <!-- Margherita Pizza -->
          <div class="product-card">
            <img
              src="../assets/images/products/placeholder.jpg"
              alt="Margherita Pizza" />
            <h3>Margherita Pizza</h3>
            <p>Classic pizza with tomato sauce, fresh mozzarella, basil leaves, and olive oil.</p>
            <p class="price">$14.99</p>
            <a href="#" class="btn">Add to Cart</a>
          </div>

          <!-- Pasta Carbonara -->
          <div class="product-card">
            <img
              src="../assets/images/products/placeholder.jpg"
              alt="Pasta Carbonara" />
            <h3>Pasta Carbonara</h3>
            <p>Al dente spaghetti with crispy bacon, creamy egg sauce, and freshly ground black pepper.</p>
            <p class="price">$16.99</p>
            <a href="#" class="btn">Add to Cart</a>
          </div>

          <!-- Chocolate Cake -->
          <div class="product-card">
            <img
              src="../assets/images/products/placeholder.jpg"
              alt="Chocolate Cake" />
            <h3>Chocolate Cake</h3>
            <p>Rich, moist chocolate cake with ganache frosting and fresh berries on top.</p>
            <p class="price">$8.99</p>
            <a href="#" class="btn">Add to Cart</a>
          </div>
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