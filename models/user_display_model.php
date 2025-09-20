<?php
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
        
        echo '<div class="product-card" data-category="' . htmlspecialchars($row['category'] ?? 'general') . '">';
        echo '<img src="' . htmlspecialchars($image_src) . '" alt="' . htmlspecialchars($row['name']) . '">';
        echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
        echo '<p>' . htmlspecialchars($row['description']) . '</p>';
        
        if (!empty($row['preparation_time'])) {
            echo '<p class="prep-time"><i class="fas fa-clock"></i> ' . $row['preparation_time'] . ' min</p>';
        }
        
        echo '<p class="price">$' . number_format($row['price'], 2) . '</p>';
        echo '<a href="#" class="btn" data-menu-id="' . $row['menu_id'] . '">Add to Cart</a>';
        echo '</div>';
    }
}

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


// Convenience functions for common use cases
function display_featured_products($limit = 4) {
    display_menu_items(null, $limit, 'created_at DESC');
}

function display_menu_products($category = null) {
    display_menu_items($category);
}

function display_products_by_category($category) {
    display_menu_items($category);
}

?>