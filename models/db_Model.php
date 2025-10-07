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
    display_table('menu', $sql);
}

function display_users_table($sql = null) {
    display_table('users', $sql);
}

function display_events_table($sql = null) {
    display_table('events', $sql);
}

function display_orders_table($sql = null) {
    display_table('orders', $sql);
}

function delete_record($table, $id_field, $id_value) {
    global $connection;
    $sql = "DELETE FROM $table WHERE $id_field = '$id_value'";
    $result = mysqli_query($connection, $sql) or die(mysqli_error($connection));
    confirm_query($result);
    return $result;
}

if (isset($_GET['deleteid'])) {
    $delete_id = $_GET['deleteid'];
    $current_page = basename($_SERVER['PHP_SELF']);
    
    $table = str_replace('_list.php', '', $current_page);
    
    $id_field = get_id_field_name($table);
    
    delete_record($table, $id_field, $delete_id);
    redirect_to($current_page);
}
?>
