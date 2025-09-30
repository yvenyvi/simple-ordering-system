<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu | Delicious Eats</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>

<body>
    <?php
    include '../includes/header.php';
    include '../includes/sidebar.php';
    require_once '../models/db_Model.php';
    require_once '../models/user_display_model.php';
    
    // Get category filter from URL
    $selected_category = isset($_GET['category']) ? $_GET['category'] : 'all';
    ?>

    <main>
        <section class="products">
            <div class="container">
                <h2>Our Menu</h2>
                
                <!-- Category Filter -->
                <div class="category-filter" style="margin-bottom: 30px; text-align: center;">
                    <a href="products.php?category=all" class="filter-btn <?php echo $selected_category == 'all' ? 'active' : ''; ?>">All</a>
                    <a href="products.php?category=pizza" class="filter-btn <?php echo $selected_category == 'pizza' ? 'active' : ''; ?>">Pizza</a>
                    <a href="products.php?category=burgers" class="filter-btn <?php echo $selected_category == 'burgers' ? 'active' : ''; ?>">Burgers</a>
                    <a href="products.php?category=pasta" class="filter-btn <?php echo $selected_category == 'pasta' ? 'active' : ''; ?>">Pasta</a>
                    <a href="products.php?category=salads" class="filter-btn <?php echo $selected_category == 'salads' ? 'active' : ''; ?>">Salads</a>
                    <a href="products.php?category=desserts" class="filter-btn <?php echo $selected_category == 'desserts' ? 'active' : ''; ?>">Desserts</a>
                    <a href="products.php?category=beverages" class="filter-btn <?php echo $selected_category == 'beverages' ? 'active' : ''; ?>">Beverages</a>
                </div>
                
                <div class="product-grid">
                    <?php
                    // Display real menu items from database using the enhanced db_model
                    display_menu_products($selected_category);
                    ?>
                </div>
            </div>
        </section>
    </main>

    <?php
    include '../includes/footer.php';
    ?>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/cart.js"></script>
</body>

</html>