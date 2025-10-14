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

function save($table, $data){
    global $connection;
    
    // NOTE: UPDATE functionality has been temporarily disabled
    // This function now only supports INSERT operations
    // UPDATE functionality coming soon...
    
    // Escape all data values
    $escaped_data = array();
    foreach ($data as $field => $value) {
        if ($value === null) {
            $escaped_data[$field] = 'NULL';
        } else {
            $escaped_data[$field] = "'" . mysqli_real_escape_string($connection, $value) . "'";
        }
    }
    
    // Check if table has timestamp columns
    $table_columns = getTableColumns($table);
    $has_created_at = isset($table_columns['created_at']);
    $has_updated_at = isset($table_columns['updated_at']);
    
    // INSERT operation
    $fields = implode(", ", array_keys($escaped_data));
    $values = implode(", ", array_values($escaped_data));
    
    // Add timestamp fields only if they exist in the table
    if ($has_created_at && $has_updated_at) {
        $sql_query = "INSERT INTO $table ($fields, created_at, updated_at) VALUES ($values, NOW(), NOW())";
    } elseif ($has_created_at) {
        $sql_query = "INSERT INTO $table ($fields, created_at) VALUES ($values, NOW())";
    } elseif ($has_updated_at) {
        $sql_query = "INSERT INTO $table ($fields, updated_at) VALUES ($values, NOW())";
    } else {
        $sql_query = "INSERT INTO $table ($fields) VALUES ($values)";
    }
    
    $result = mysqli_query($connection, $sql_query) or die(mysqli_error($connection));
    $new_id = mysqli_insert_id($connection);
        
        if(isset($_FILES['fileField']) && $_FILES['fileField']['tmp_name']) {
            $newname = "$new_id.jpg";
            
            $upload_dir = get_upload_directory($table);
            $upload_path = $upload_dir . $newname;
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            move_uploaded_file($_FILES['fileField']['tmp_name'], $upload_path);
        }
        
        confirm_query($result);
        return $new_id;
    }

function get_upload_directory($table) {
    $base_dir = "../assets/images/";
    
    $directory_map = array(
        'menu' => 'products/'
    );

    if (isset($directory_map[$table])) {
        return $base_dir . $directory_map[$table];
    } else {
        return $base_dir . $table . '/';
    }
}

function get_id_field_name($table) {
    // Map table names to correct ID field names
    $id_field_mapping = [
        'users' => 'user_id',
        'menu' => 'menu_id',
        'events' => 'event_id',
        'orders' => 'order_id'
    ];
    
    return isset($id_field_mapping[$table]) ? $id_field_mapping[$table] : $table . '_id';
}

function get_image_path($row, $table) {
    // Use helper functions for consistency
    $base_dir = get_upload_directory($table);
    $id_field = get_id_field_name($table);
    $id = $row[$id_field] ?? $row['id'] ?? null;
    $placeholder = $base_dir . 'placeholder.jpg';
    
    if (!empty($row['image_url'])) {
        if (file_exists($row['image_url'])) {
            return $row['image_url'];
        } elseif (file_exists('../' . preg_replace('/^(\.\.\/)+/', '', $row['image_url']))) {
            return '../' . preg_replace('/^(\.\.\/)+/', '', $row['image_url']);
        }
    }
    
    if ($id && file_exists($base_dir . $id . '.jpg')) {
        return $base_dir . $id . '.jpg';
    }
    
    return $placeholder;
}

function display_table($table_name, $sql = null, $options = array()) {
    require_once dirname(__FILE__) . '/admin_display_model.php';
    
    display_admin_table($table_name, $sql, $options);
}

function getTableColumns($table_name) {
    global $connection;
    
    $columns = array();
    $query = "SHOW COLUMNS FROM `$table_name`";
    $result = mysqli_query($connection, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $columns[$row['Field']] = array(
                'type' => $row['Type'],
                'null' => $row['Null'],
                'key' => $row['Key'],
                'default' => $row['Default'],
                'extra' => $row['Extra']
            );
        }
    }
    
    return $columns;
}


function display_menu_table($sql = null) {
    // Custom configuration for menu table with view action and limited columns
    $options = [
        'columns' => [
            'image_url' => [
                'label' => 'Image',
                'type' => 'image'
            ],
            'name' => [
                'label' => 'Name',
                'type' => 'string'
            ],
            'category' => [
                'label' => 'Category',
                'type' => 'string'
            ],
            'price' => [
                'label' => 'Price',
                'type' => 'price'
            ],
            'preparation_time' => [
                'label' => 'Prep Time',
                'type' => 'prep_time'
            ],
            'is_available' => [
                'label' => 'Available',
                'type' => 'boolean'
            ],
            'created_at' => [
                'label' => 'Created',
                'type' => 'datetime'
            ]
        ],
        'actions' => ['view', 'edit', 'toggle', 'delete']
    ];
    display_table('menu', $sql, $options);
}

function display_users_table($sql = null) {
    display_table('users', $sql);
}

function display_events_table($sql = null) {
    display_table('events', $sql);
}

function display_orders_table($sql = null) {
    // Custom column configuration for orders display
    $options = [
        'columns' => [
            'order_id' => [
                'label' => 'Order #',
                'type' => 'string'
            ],
            'customer_name' => [
                'label' => 'Customer',
                'type' => 'string'
            ],
            'customer_email' => [
                'label' => 'Email',
                'type' => 'email'
            ],
            'phone' => [
                'label' => 'Phone',
                'type' => 'phone'
            ],
            'item_count' => [
                'label' => 'Items',
                'type' => 'string'
            ],
            'total_amount' => [
                'label' => 'Total',
                'type' => 'price'
            ],
            'status' => [
                'label' => 'Status',
                'type' => 'status'
            ],
            'payment_status' => [
                'label' => 'Payment',
                'type' => 'status'
            ],
            'order_date' => [
                'label' => 'Order Date',
                'type' => 'datetime'
            ]
        ],
        'actions' => ['view', 'delete']
    ];
    
    display_table('orders', $sql, $options);
}

function delete_record($table, $id_value) {
    global $connection;
    
    // Sanitize the ID
    $id_value = intval($id_value);
    if ($id_value <= 0) {
        return ['success' => false, 'message' => 'Invalid ID provided'];
    }
    
    // Get the correct ID field name for the table
    $id_field = get_id_field_name($table);
    
    try {
        // Start transaction for data integrity
        mysqli_autocommit($connection, false);
        
        // Handle table-specific cleanup before deletion
        $cleanup_result = handle_pre_delete_cleanup($table, $id_field, $id_value);
        if (!$cleanup_result['success']) {
            mysqli_rollback($connection);
            return $cleanup_result;
        }
        
        // Perform the actual deletion with prepared statement
        $sql = "DELETE FROM `$table` WHERE `$id_field` = ? LIMIT 1";
        $stmt = mysqli_prepare($connection, $sql);
        
        if (!$stmt) {
            mysqli_rollback($connection);
            return ['success' => false, 'message' => 'Failed to prepare delete statement'];
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id_value);
        $result = mysqli_stmt_execute($stmt);
        
        if (!$result) {
            mysqli_rollback($connection);
            mysqli_stmt_close($stmt);
            return ['success' => false, 'message' => 'Failed to delete record: ' . mysqli_error($connection)];
        }
        
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        if ($affected_rows === 0) {
            mysqli_rollback($connection);
            return ['success' => false, 'message' => 'No record found with the specified ID'];
        }
        
        // Commit the transaction
        mysqli_commit($connection);
        mysqli_autocommit($connection, true);
        
        return ['success' => true, 'message' => ucfirst($table) . ' record deleted successfully'];
        
    } catch (Exception $e) {
        mysqli_rollback($connection);
        mysqli_autocommit($connection, true);
        return ['success' => false, 'message' => 'Error deleting record: ' . $e->getMessage()];
    }
}


function handle_pre_delete_cleanup($table, $id_field, $id_value) {
    global $connection;
    
    switch ($table) {
        case 'menu':
        case 'events':
            // Get and delete associated image file
            $image_result = get_and_delete_image($table, $id_field, $id_value);
            if (!$image_result['success']) {
                return $image_result;
            }
            break;
            
        case 'orders':
            // Delete associated order items first (cascade delete)
            $items_sql = "DELETE FROM order_items WHERE order_id = ?";
            $stmt = mysqli_prepare($connection, $items_sql);
            mysqli_stmt_bind_param($stmt, "i", $id_value);
            
            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                return ['success' => false, 'message' => 'Failed to delete order items'];
            }
            mysqli_stmt_close($stmt);
            break;
            
        case 'users':
            // Check if user has any orders before deletion
            $check_sql = "SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?";
            $stmt = mysqli_prepare($connection, $check_sql);
            mysqli_stmt_bind_param($stmt, "i", $id_value);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            
            if ($row['order_count'] > 0) {
                return ['success' => false, 'message' => 'Cannot delete user with existing orders. Please handle orders first.'];
            }
            break;
    }
    
    return ['success' => true, 'message' => 'Pre-delete cleanup completed'];
}

function get_and_delete_image($table, $id_field, $id_value) {
    global $connection;
    
    // Get the image URL before deleting
    $get_image_sql = "SELECT image_url FROM `$table` WHERE `$id_field` = ? LIMIT 1";
    $stmt = mysqli_prepare($connection, $get_image_sql);
    mysqli_stmt_bind_param($stmt, "i", $id_value);
    mysqli_stmt_execute($stmt);
    $image_result = mysqli_stmt_get_result($stmt);
    $image_row = mysqli_fetch_array($image_result);
    mysqli_stmt_close($stmt);
    
    // Remove the image file if it exists and is not a placeholder
    if ($image_row && $image_row['image_url'] && strpos($image_row['image_url'], 'placeholder.jpg') === false) {
        $image_path = $image_row['image_url'];
        
        // Handle both relative and absolute paths
        if (!file_exists($image_path)) {
            // Try relative path from current directory
            $image_path = "../" . $image_row['image_url'];
        }
        
        if (file_exists($image_path)) {
            if (!unlink($image_path)) {
                // Image deletion failed, but don't stop the record deletion
                error_log("Warning: Failed to delete image file: $image_path");
            }
        }
    }
    
    return ['success' => true, 'message' => 'Image cleanup completed'];
}


?>
