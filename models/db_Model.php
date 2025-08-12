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

function display_all($sql, $column_mappings, $url){
    global $connection;
    $output_list = "";
    $result = mysqli_query($connection, $sql);

    $rowCount = mysqli_num_rows($result);

    if ($rowCount > 0) {
        while($row = mysqli_fetch_array($result)){ 
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
    } else {
        $output_list = "No records found.";
    }
    echo $output_list;
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
