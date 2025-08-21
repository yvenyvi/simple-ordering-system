<?php
define("DB_SERVER", "localhost");
define("DB_USER", "root");
define("DB_PASS", "password");
define("DB_NAME", "delicious_eats");

$connection = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
if (mysqli_connect_errno()) {
    die("Database connection failed: " .
        mysqli_connect_error() .
        "(" . mysqli_connect_errno() . ")"
    );
}       

function redirect_to($new_location) {
    header("Location: ".$new_location);
    exit();
}

function confirm_query($result_set){
    if(!$result_set){
        die("Database query failed!");
    }
}

function save($table, $data, $id_field = null, $id_value = null){
    global $connection;
    
    // Escape all data values
    $escaped_data = array();
    foreach ($data as $field => $value) {
        if ($value === null) {
            $escaped_data[$field] = 'NULL';
        } else {
            $escaped_data[$field] = "'" . mysqli_real_escape_string($connection, $value) . "'";
        }
    }
    
    // Determine if this is an INSERT or UPDATE operation
    if ($id_field && $id_value) {
        // UPDATE operation
        $set_clauses = array();
        foreach ($escaped_data as $field => $value) {
            $set_clauses[] = "$field = $value";
        }
        $set_string = implode(", ", $set_clauses);
        $escaped_id = mysqli_real_escape_string($connection, $id_value);
        
        $sql_query = "UPDATE $table SET $set_string, updated_at = NOW() WHERE $id_field = '$escaped_id'";
        $result = mysqli_query($connection, $sql_query) or die(mysqli_error($connection));
        confirm_query($result);
        return $id_value; // Return the existing ID for updates
        
    } else {
        // INSERT operation
        $fields = implode(", ", array_keys($escaped_data));
        $values = implode(", ", array_values($escaped_data));
        
        $sql_query = "INSERT INTO $table ($fields, created_at, updated_at) VALUES ($values, NOW(), NOW())";
        $result = mysqli_query($connection, $sql_query) or die(mysqli_error($connection));
        $new_id = mysqli_insert_id($connection);
        
        // Handle file upload if exists (for INSERT operations)
        if(isset($_FILES['fileField']) && $_FILES['fileField']['tmp_name']) {
            $newname = "$new_id.jpg";
            move_uploaded_file($_FILES['fileField']['tmp_name'], "../assets/images/products/$newname");
        }
        
        confirm_query($result);
        return $new_id; // Return the new ID for inserts
    }
}

function display_all($sql, $column_mappings = null, $url = null, $display_mode = 'admin'){
    global $connection;
    $output_list = "";
    $result = mysqli_query($connection, $sql);

    $rowCount = mysqli_num_rows($result);

    if ($rowCount > 0) {
        while($row = mysqli_fetch_array($result)){ 
            
            // User-side product card display
            if ($display_mode === 'user' || $display_mode === 'products') {
                // Determine the image source
                $image_src = '../assets/images/products/placeholder.jpg'; // Default fallback
                
                if (!empty($row['image_url'])) {
                    if (file_exists($row['image_url'])) {
                        $image_src = $row['image_url'];
                    } elseif (file_exists('../' . preg_replace('/^(\.\.\/)+/', '', $row['image_url']))) {
                        $image_src = '../' . preg_replace('/^(\.\.\/)+/', '', $row['image_url']);
                    }
                } elseif (file_exists("../assets/images/products/{$row['menu_id']}.jpg")) {
                    $image_src = "../assets/images/products/{$row['menu_id']}.jpg";
                }
                
                echo '<div class="product-card" data-category="' . htmlspecialchars($row['category']) . '">';
                echo '<img src="' . htmlspecialchars($image_src) . '" alt="' . htmlspecialchars($row['name']) . '">';
                echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
                echo '<p>' . htmlspecialchars($row['description']) . '</p>';
                
                if (!empty($row['preparation_time'])) {
                    echo '<p class="prep-time"><i class="fas fa-clock"></i> ' . $row['preparation_time'] . ' min</p>';
                }
                
                echo '<p class="price">$' . number_format($row['price'], 2) . '</p>';
                echo '<a href="#" class="btn" data-menu-id="' . $row['menu_id'] . '">Add to Cart</a>';
                echo '</div>';
                
            } else {
                // Admin-side list display
                if ($column_mappings) {
                    foreach ($column_mappings as $column_name => $label) {
                        $value = $row[$column_name];
                        if (strpos($column_name, 'date') !== false) {
                            $value = date("M d, Y", strtotime($value));
                        }
                        $output_list .= "<strong>$label </strong> $value &nbsp; ";
                    }
                    
                    $id = $row['user_id'] ?? $row['menu_id'] ?? $row['event_id'] ?? $row['id'];
                    $output_list .= "<a href='edit.php?editid=$id'>edit</a> &bull; <a href='$url?deleteid=$id'>delete</a><br />";
                }
            }
        }
        
        // For admin display, echo the accumulated output
        if ($display_mode === 'admin' && $column_mappings !== null) {
            echo $output_list;
        }
        
    } else {
        if ($display_mode === 'user' || $display_mode === 'products') {
            // User-side no items message
            echo '<div class="no-items">';
            echo '<p>No menu items found.</p>';
            echo '</div>';
        } else {
            // Admin-side no records message
            echo "No records found.";
        }
    }
}

function delete_record($table, $id_field, $id_value) {
    global $connection;
    $sql = "DELETE FROM $table WHERE $id_field = '$id_value'";
    $result = mysqli_query($connection, $sql) or die(mysqli_error($connection));
    confirm_query($result);
    return $result;
}

// Helper functions for user-side display with column mapping support
function display_menu_products($category = null) {
    global $connection;
    
    $sql = "SELECT * FROM menu WHERE is_available = 1";
    
    if ($category && $category !== 'all') {
        $escaped_category = mysqli_real_escape_string($connection, $category);
        $sql .= " AND category = '$escaped_category'";
    }
    
    $sql .= " ORDER BY name ASC";
    
    // Use display_all with 'products' mode for user display
    display_all($sql, null, null, 'products');
}

function display_featured_products($limit = 4) {
    $sql = "SELECT * FROM menu WHERE is_available = 1 ORDER BY created_at DESC LIMIT $limit";
    display_all($sql, null, null, 'products');
}

// Admin functions with column mapping
function display_admin_menu($sql = null) {
    if (!$sql) {
        $sql = "SELECT * FROM menu ORDER BY created_at DESC";
    }
    
    $column_mappings = array(
        'name' => 'Menu Item',
        'category' => 'Category',
        'price' => 'Price',
        'is_available' => 'Status',
        'created_at' => 'Added'
    );
    
    display_all($sql, $column_mappings, 'menu_list.php', 'admin');
}

function display_admin_users($sql = null) {
    if (!$sql) {
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
    }
    
    $column_mappings = array(
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'created_at' => 'Joined'
    );
    
    display_all($sql, $column_mappings, 'user_list.php', 'admin');
}

if (isset($_GET['deleteid'])) {
    $delete_id = $_GET['deleteid'];
    $current_page = basename($_SERVER['PHP_SELF']);
    
    if($current_page == 'user_list.php') {
        delete_record('users', 'user_id', $delete_id);
        redirect_to("user_list.php");
    } elseif($current_page == 'menu_list.php') {
        delete_record('menu', 'menu_id', $delete_id);
        redirect_to("menu_list.php");
    } elseif($current_page == 'event_list.php') {
        delete_record('events', 'event_id', $delete_id);
        redirect_to("event_list.php");
    }
}
?>
