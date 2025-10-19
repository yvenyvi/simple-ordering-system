<?php

/**
 * User Display Model
 * Handles user-facing display functions for products, menus, and frontend components
 * Focused on customer-facing functionality and product presentation
 */

/**
 * =============================================================================
 * PRODUCT DISPLAY FUNCTIONS
 * =============================================================================
 */

/**
 * Display product cards from SQL query
 */
function display_product_cards($sql) {
    global $connection;
    $result = mysqli_query($connection, $sql);
    $rowCount = mysqli_num_rows($result);
    
    if ($rowCount === 0) {
        echo '<div class="no-items"><p>No menu items found.</p></div>';
        return;
    }
    
    while ($row = mysqli_fetch_array($result)) {
        $image_src = get_image_path($row, 'menu');
        $price = number_format($row['price'], 2);
        
        echo '<div class="product-card" data-category="' . htmlspecialchars($row['category'] ?? 'general') . '">';
        echo '<img src="' . htmlspecialchars($image_src) . '" alt="' . htmlspecialchars($row['name']) . '">';
        echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
        echo '<p>' . htmlspecialchars($row['description']) . '</p>';
        
        if (!empty($row['preparation_time'])) {
            echo '<p class="prep-time"><i class="fas fa-clock"></i> ' . $row['preparation_time'] . ' min</p>';
        }
        
        echo '<p class="price">$' . $price . '</p>';
        echo '<a href="#" class="btn" data-menu-id="' . $row['menu_id'] . '" data-name="' . htmlspecialchars($row['name']) . '" data-price="' . $row['price'] . '" data-image="' . htmlspecialchars($image_src) . '">Add to Cart</a>';
        echo '</div>';
    }
}

/**
 * Display menu items with filtering and sorting options
 */
function display_menu_items($category = null, $limit = null, $order_by = 'name ASC') {
    global $connection;
    
    $sql = "SELECT * FROM menu WHERE is_available = 1";
    
    if ($category && $category !== 'all') {
        $escaped_category = mysqli_real_escape_string($connection, $category);
        $sql .= " AND category = '$escaped_category'";
    }
    
    $sql .= " ORDER BY $order_by";
    
    if ($limit) {
        $sql .= " LIMIT $limit";
    }
    
    display_product_cards($sql);
}

/**
 * =============================================================================
 * CONVENIENCE FUNCTIONS FOR COMMON USE CASES
 * =============================================================================
 */

/**
 * Display featured products (recent items)
 */
function display_featured_products($limit = 4) {
    display_menu_items(null, $limit, 'created_at DESC');
}

/**
 * Display menu products with optional category filter
 */
function display_menu_products($category = null) {
    display_menu_items($category);
}

/**
 * Display products by specific category
 */
function display_products_by_category($category) {
    display_menu_items($category);
}

?>