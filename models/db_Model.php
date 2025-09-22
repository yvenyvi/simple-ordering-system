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
        // INSERT operation
        $fields = implode(", ", array_keys($escaped_data));
        $values = implode(", ", array_values($escaped_data));
        
        $sql_query = "INSERT INTO $table ($fields, created_at, updated_at) VALUES ($values, NOW(), NOW())";
        $result = mysqli_query($connection, $sql_query) or die(mysqli_error($connection));
        $new_id = mysqli_insert_id($connection);
        
        if(isset($_FILES['fileField']) && $_FILES['fileField']['tmp_name']) {
            $newname = "$new_id.jpg";
            
            // Use helper function for flexible directory handling
            $upload_dir = get_upload_directory($table);
            $upload_path = $upload_dir . $newname;
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            if(move_uploaded_file($_FILES['fileField']['tmp_name'], $upload_path)) {
                // Use helper function for flexible ID field handling
                $id_field_name = get_id_field_name($table);
                
                $image_url = str_replace("../", "", $upload_path);
                $update_sql = "UPDATE $table SET image_url = '" . mysqli_real_escape_string($connection, $image_url) . "' WHERE $id_field_name = '$new_id'";
                mysqli_query($connection, $update_sql);
            }
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
    // Simple convention: table_name + '_id'
    return $table . '_id';
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

/**
 * Universal admin table display function
 * Works with ANY database table using the table helper for rich UI
 */
function display_table($table_name, $sql = null) {
    // If no SQL provided, build a default one
    if (!$sql) {
        $sql = "SELECT * FROM `$table_name` ORDER BY created_at DESC";
    }
    
    // Load the table helper functions if not already loaded
    if (!function_exists('display_admin_table_view')) {
        require_once dirname(__FILE__) . '/../admin/helpers/table_helper.php';
    }
    
    // Use the table helper to display the rich UI
    display_admin_table_view($table_name, $sql);
}

/**
 * Convenience functions for backward compatibility and common tables
 */
function display_menu_table($sql = null) {
    $sql = $sql ?: "SELECT * FROM menu ORDER BY created_at DESC";
    display_table('menu', $sql);
}

function display_users_table($sql = null) {
    $sql = $sql ?: "SELECT * FROM users ORDER BY created_at DESC";
    display_table('users', $sql);
}

function display_events_table($sql = null) {
    $sql = $sql ?: "SELECT * FROM events ORDER BY event_date ASC";
    display_table('events', $sql);
}

function delete_record($table, $id_field, $id_value) {
    global $connection;
    $sql = "DELETE FROM $table WHERE $id_field = '$id_value'";
    $result = mysqli_query($connection, $sql) or die(mysqli_error($connection));
    confirm_query($result);
    return $result;
}

// Flexible delete handler that works with any module
if (isset($_GET['deleteid'])) {
    $delete_id = $_GET['deleteid'];
    $current_page = basename($_SERVER['PHP_SELF']);
    
    $table = str_replace('_list.php', '', $current_page);
    
    $id_field = get_id_field_name($table);
    
    delete_record($table, $id_field, $delete_id);
    redirect_to($current_page);
}
?>
