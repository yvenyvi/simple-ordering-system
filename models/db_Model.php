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
            
            // Determine upload directory based on table
            $upload_dir = "../assets/images/";
            switch($table) {
                case 'menu':
                    $upload_dir .= "products/";
                    break;
                case 'events':
                    $upload_dir .= "events/";
                    break;
                case 'users':
                    $upload_dir .= "users/";
                    break;
                default:
                    $upload_dir .= "general/";
                    break;
            }
            
            $upload_path = $upload_dir . $newname;
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            if(move_uploaded_file($_FILES['fileField']['tmp_name'], $upload_path)) {
                // Determine the correct ID field name
                $id_field_name = $table . '_id';
                if ($table === 'users') {
                    $id_field_name = 'user_id';
                } elseif ($table === 'menu') {
                    $id_field_name = 'menu_id';
                } elseif ($table === 'events') {
                    $id_field_name = 'event_id';
                }
                
                // Update the database with the image URL (relative path from web root)
                $image_url = str_replace("../", "", $upload_path);
                $update_sql = "UPDATE $table SET image_url = '" . mysqli_real_escape_string($connection, $image_url) . "' WHERE $id_field_name = '$new_id'";
                mysqli_query($connection, $update_sql);
            }
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
                // Determine the image source based on content type
                $image_src = '../assets/images/products/placeholder.jpg'; // Default fallback
                
                // Determine if this is a menu item or event based on available fields
                $is_event = isset($row['event_id']);
                $is_menu = isset($row['menu_id']);
                
                if ($is_event) {
                    $image_src = '../assets/images/events/placeholder.jpg';
                } elseif ($is_menu) {
                    $image_src = '../assets/images/products/placeholder.jpg';
                }
                
                if (!empty($row['image_url'])) {
                    if (file_exists($row['image_url'])) {
                        $image_src = $row['image_url'];
                    } elseif (file_exists('../' . preg_replace('/^(\.\.\/)+/', '', $row['image_url']))) {
                        $image_src = '../' . preg_replace('/^(\.\.\/)+/', '', $row['image_url']);
                    }
                } else {
                    // Fallback to ID-based image naming
                    if ($is_event && file_exists("../assets/images/events/{$row['event_id']}.jpg")) {
                        $image_src = "../assets/images/events/{$row['event_id']}.jpg";
                    } elseif ($is_menu && file_exists("../assets/images/products/{$row['menu_id']}.jpg")) {
                        $image_src = "../assets/images/products/{$row['menu_id']}.jpg";
                    }
                }
                
                echo '<div class="product-card" data-category="' . htmlspecialchars($row['category'] ?? 'general') . '">';
                echo '<img src="' . htmlspecialchars($image_src) . '" alt="' . htmlspecialchars($row['name'] ?? $row['event_name']) . '">';
                echo '<h3>' . htmlspecialchars($row['name'] ?? $row['event_name']) . '</h3>';
                echo '<p>' . htmlspecialchars($row['description']) . '</p>';
                
                if (!empty($row['preparation_time'])) {
                    echo '<p class="prep-time"><i class="fas fa-clock"></i> ' . $row['preparation_time'] . ' min</p>';
                }
                
                echo '<p class="price">$' . number_format($row['price'], 2) . '</p>';
                
                if ($is_menu) {
                    echo '<a href="#" class="btn" data-menu-id="' . $row['menu_id'] . '">Add to Cart</a>';
                } elseif ($is_event) {
                    echo '<a href="#" class="btn" data-event-id="' . $row['event_id'] . '">Reserve Spot</a>';
                }
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

// Helper function for user-side event display
function display_upcoming_events($limit = null) {
    global $connection;
    
    $sql = "SELECT * FROM events WHERE is_active = 1 AND event_date >= CURDATE() ORDER BY event_date ASC, event_time ASC";
    
    if ($limit) {
        $sql .= " LIMIT $limit";
    }
    
    $result = mysqli_query($connection, $sql);
    $rowCount = mysqli_num_rows($result);
    
    if ($rowCount > 0) {
        while($row = mysqli_fetch_array($result)) {
            // Determine the image source for events
            $image_src = '../assets/images/events/placeholder.jpg'; // Default fallback
            
            if (!empty($row['image_url'])) {
                if (file_exists($row['image_url'])) {
                    $image_src = $row['image_url'];
                } elseif (file_exists('../' . preg_replace('/^(\.\.\/)+/', '', $row['image_url']))) {
                    $image_src = '../' . preg_replace('/^(\.\.\/)+/', '', $row['image_url']);
                }
            } elseif (file_exists("../assets/images/events/{$row['event_id']}.jpg")) {
                $image_src = "../assets/images/events/{$row['event_id']}.jpg";
            }
            
            $event_date = date("M d, Y", strtotime($row['event_date']));
            $event_time = date("g:i A", strtotime($row['event_time']));
            
            echo '<div class="event-card">';
            echo '<img src="' . htmlspecialchars($image_src) . '" alt="' . htmlspecialchars($row['event_name']) . '">';
            echo '<div class="event-content">';
            echo '<h3>' . htmlspecialchars($row['event_name']) . '</h3>';
            echo '<p class="event-type"><i class="fas fa-tag"></i> ' . ucfirst($row['event_type']) . '</p>';
            echo '<p class="event-datetime"><i class="fas fa-calendar"></i> ' . $event_date . ' at ' . $event_time . '</p>';
            echo '<p class="event-location"><i class="fas fa-map-marker-alt"></i> ' . htmlspecialchars($row['location']) . '</p>';
            if (!empty($row['description'])) {
                echo '<p class="event-description">' . htmlspecialchars($row['description']) . '</p>';
            }
            echo '<div class="event-details">';
            echo '<p class="event-capacity"><i class="fas fa-users"></i> Max ' . $row['capacity'] . ' people</p>';
            echo '<p class="event-price">$' . number_format($row['price'], 2) . ' per person</p>';
            echo '</div>';
            echo '<a href="#" class="btn event-btn" data-event-id="' . $row['event_id'] . '">Reserve Spot</a>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<div class="no-events">';
        echo '<i class="fas fa-calendar-times"></i>';
        echo '<h3>No Upcoming Events</h3>';
        echo '<p>Check back soon for exciting events and special dining experiences!</p>';
        echo '</div>';
    }
}

function display_featured_events($limit = 3) {
    display_upcoming_events($limit);
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
