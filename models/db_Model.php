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
    $id_field_map = array(
        'users' => 'user_id',
        'menu' => 'menu_id',
        'events' => 'event_id'
    );
    
    if (isset($id_field_map[$table])) {
        return $id_field_map[$table];
    } else {
        return $table . '_id';
    }
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

function display_admin_list($sql, $column_mappings, $edit_url) {
    global $connection;
    $result = mysqli_query($connection, $sql);
    $rowCount = mysqli_num_rows($result);
    
    if ($rowCount === 0) {
        echo "No records found.";
        return;
    }
    
    $output = "";
    while ($row = mysqli_fetch_array($result)) {
        foreach ($column_mappings as $column_name => $label) {
            $value = $row[$column_name];
            if (strpos($column_name, 'date') !== false) {
                $value = date("M d, Y", strtotime($value));
            }
            $output .= "<strong>$label</strong> $value &nbsp; ";
        }
        
        $id = $row['user_id'] ?? $row['menu_id'] ?? $row['event_id'] ?? $row['id'];
        $output .= "<a href='edit.php?editid=$id'>edit</a> &bull; <a href='$edit_url?deleteid=$id'>delete</a><br />";
    }
    
    echo $output;
}

function delete_record($table, $id_field, $id_value) {
    global $connection;
    $sql = "DELETE FROM $table WHERE $id_field = '$id_value'";
    $result = mysqli_query($connection, $sql) or die(mysqli_error($connection));
    confirm_query($result);
    return $result;
}

function display_admin_table($table, $sql = null) {
    // Default column mappings for common tables
    $default_mappings = array(
        'menu' => array(
            'columns' => array('name' => 'Menu Item', 'category' => 'Category', 'price' => 'Price', 'is_available' => 'Status', 'created_at' => 'Added'),
            'order' => 'created_at DESC'
        ),
        'users' => array(
            'columns' => array('first_name' => 'First Name', 'last_name' => 'Last Name', 'email' => 'Email', 'phone' => 'Phone', 'created_at' => 'Joined'),
            'order' => 'created_at DESC'
        ),
        'events' => array(
            'columns' => array('event_name' => 'Event', 'event_type' => 'Type', 'event_date' => 'Date', 'location' => 'Location', 'capacity' => 'Capacity', 'price' => 'Price'),
            'order' => 'event_date ASC'
        )
    );
    
    // Use provided SQL or build default
    if (!$sql) {
        $order = isset($default_mappings[$table]) ? $default_mappings[$table]['order'] : 'created_at DESC';
        $sql = "SELECT * FROM $table ORDER BY $order";
    }
    
    $column_mappings = isset($default_mappings[$table]) ? $default_mappings[$table]['columns'] : array('name' => 'Name', 'created_at' => 'Created');
    
    $edit_url = $table . '_list.php';
    display_admin_list($sql, $column_mappings, $edit_url);
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
