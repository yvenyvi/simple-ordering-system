<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu | Delicious Eats</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>

<body>
    <?php
    include '../includes/header.php';
    include '../includes/sidebar.php';
    ?>

    <main>
        <section class="products">
            <div class="container">
                <h2>Our Menu</h2>
                <div class="product-grid">
                    <!-- Margherita Pizza -->
                    <div class="product-card" data-category="pizza">
                        <img src="../assets/images/products/placeholder.jpg" alt="Margherita Pizza">
                        <h3>Margherita Pizza</h3>
                        <p>Classic pizza with tomato sauce, fresh mozzarella, basil leaves, and olive oil.</p>
                        <p class="price">$14.99</p>
                        <a href="#" class="btn">Add to Cart</a>
                    </div>

                    <!-- Grilled Salmon -->
                    <div class="product-card" data-category="salads">
                        <img src="../assets/images/products/placeholder.jpg" alt="Grilled Salmon">
                        <h3>Grilled Salmon</h3>
                        <p>Perfectly grilled salmon fillet with lemon herb butter and seasonal vegetables.</p>
                        <p class="price">$22.99</p>
                        <a href="#" class="btn">Add to Cart</a>
                    </div>

                    <!-- Beef Burger -->
                    <div class="product-card" data-category="burgers">
                        <img src="../assets/images/products/placeholder.jpg" alt="Beef Burger">
                        <h3>Beef Burger</h3>
                        <p>Juicy beef patty with lettuce, tomato, cheese, and our special sauce on a brioche bun.</p>
                        <p class="price">$12.99</p>
                        <a href="#" class="btn">Add to Cart</a>
                    </div>

                    <!-- Caesar Salad -->
                    <div class="product-card" data-category="salads">
                        <img src="../assets/images/products/placeholder.jpg" alt="Caesar Salad">
                        <h3>Caesar Salad</h3>
                        <p>Fresh romaine lettuce with parmesan cheese, croutons, and our house Caesar dressing.</p>
                        <p class="price">$9.99</p>
                        <a href="#" class="btn">Add to Cart</a>
                    </div>

                    <!-- Pasta Carbonara -->
                    <div class="product-card" data-category="pasta">
                        <img src="../assets/images/products/placeholder.jpg" alt="Pasta Carbonara">
                        <h3>Pasta Carbonara</h3>
                        <p>Al dente spaghetti with crispy bacon, creamy egg sauce, and freshly ground black pepper.</p>
                        <p class="price">$16.99</p>
                        <a href="#" class="btn">Add to Cart</a>
                    </div>

                    <!-- Chocolate Cake -->
                    <div class="product-card" data-category="desserts">
                        <img src="../assets/images/products/placeholder.jpg" alt="Chocolate Cake">
                        <h3>Chocolate Cake</h3>
                        <p>Rich, moist chocolate cake with ganache frosting and fresh berries on top.</p>
                        <p class="price">$8.99</p>
                        <a href="#" class="btn">Add to Cart</a>
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