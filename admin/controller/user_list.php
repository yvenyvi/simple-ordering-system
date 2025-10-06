<?php

// Handle delete request
if (isset($_GET['deleteid'])) {
    $delete_id = $_GET['deleteid'];
    
    global $connection;
    
    // Delete the record
    $delete_sql = "DELETE FROM users WHERE user_id = '$delete_id'";
    if (mysqli_query($connection, $delete_sql)) {
        $success_message = "User has been successfully deleted!";
    } else {
        $error_message = "Failed to delete user. Please try again.";
    }
    
    // Redirect to prevent resubmission
    redirect_to("user_list.php");
}

if (isset($_POST['first_name'])){
    $data = array(
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'],
        'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
        'phone' => $_POST['phone'],
        'address' => $_POST['address'],
        'city' => $_POST['city'],
        'state' => $_POST['state'],
        'zip_code' => $_POST['zip_code'],
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    );

    if (save('users', $data)) {
        $success_message = "User '{$_POST['first_name']} {$_POST['last_name']}' has been successfully added!";
    } else {
        $error_message = "Failed to add user. Please try again.";
    }

}
?>