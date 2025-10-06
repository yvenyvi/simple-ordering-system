<?php

// Handle delete request
if (isset($_GET['deleteid'])) {
    $delete_id = $_GET['deleteid'];
    
    // Get the image URL before deleting to remove the file
    global $connection;
    $get_image_sql = "SELECT image_url FROM menu WHERE menu_id = '$delete_id'";
    $image_result = mysqli_query($connection, $get_image_sql);
    $image_row = mysqli_fetch_array($image_result);
    
    // Delete the record
    $delete_sql = "DELETE FROM menu WHERE menu_id = '$delete_id'";
    mysqli_query($connection, $delete_sql);
    
    // Remove the image file if it exists and is not a placeholder
    if ($image_row && $image_row['image_url'] && strpos($image_row['image_url'], 'placeholder.jpg') === false) {
        $image_path = $image_row['image_url'];
        
        // Handle both relative and absolute paths
        if (!file_exists($image_path)) {
            // Try relative path from current directory
            $image_path = "../" . $image_row['image_url'];
        }
        
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    redirect_to("menu_list.php");
}

if (isset($_POST['name'])) {
    $data = array(
        'name' => $_POST['name'],
        'description' => $_POST['description'],
        'category' => $_POST['category'],
        'price' => floatval($_POST['price']),
        'ingredients' => $_POST['ingredients'],
        'preparation_time' => intval($_POST['preparation_time']),
        'is_available' => isset($_POST['is_available']) ? 1 : 0
    );

    $new_id = save('menu', $data);
    
    if ($new_id) {
        // If a file was uploaded, update the database with the image URL
        if (isset($_FILES['fileField']) && $_FILES['fileField']['tmp_name']) {
            $image_url = "../assets/images/products/{$new_id}.jpg";
            
            // Update the menu record with the image URL
            global $connection;
            $update_sql = "UPDATE menu SET image_url = '" . mysqli_real_escape_string($connection, $image_url) . "' WHERE menu_id = '$new_id'";
            mysqli_query($connection, $update_sql);
            
            $success_message = "Menu item '{$_POST['name']}' has been successfully added with image!";
        } else {
            $success_message = "Menu item '{$_POST['name']}' has been successfully added!";
        }
    } else {
        $error_message = "Failed to add menu item. Please try again.";
    }
}

?>