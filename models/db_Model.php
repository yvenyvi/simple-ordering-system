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

function save($insertQuery){
    global $connection;
    $sql = mysqli_query($connection, $insertQuery) or die (mysqli_error($connection));
    $pid = mysqli_insert_id($connection);
    
    // Handle file upload if exists
    if(isset($_FILES['fileField']) && $_FILES['fileField']['tmp_name']) {
        $newname = "$pid.jpg";
        move_uploaded_file($_FILES['fileField']['tmp_name'], "../assets/images/products/$newname");
    }
    
    confirm_query($sql);
    return $pid;
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
