<?php

// Handle delete request
if (isset($_GET['deleteid'])) {
    $delete_id = $_GET['deleteid'];
    
    global $connection;
    $get_image_sql = "SELECT image_url FROM events WHERE event_id = '$delete_id'";
    $image_result = mysqli_query($connection, $get_image_sql);
    $image_row = mysqli_fetch_array($image_result);
    
    // Delete the record
    $delete_sql = "DELETE FROM events WHERE event_id = '$delete_id'";
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
    
    redirect_to("event_list.php");
}

if (isset($_POST['event_name'])) {
    $data = array(
        'event_name' => $_POST['event_name'],
        'description' => $_POST['description'],
        'event_date' => $_POST['event_date'],
        'event_time' => $_POST['event_time'],
        'location' => $_POST['location'],
        'capacity' => intval($_POST['capacity']),
        'price' => floatval($_POST['price']),
        'event_type' => $_POST['event_type'],
        'contact_email' => $_POST['contact_email'],
        'contact_phone' => $_POST['contact_phone'],
        'requirements' => $_POST['requirements'],
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    );

    if (save('events', $data)) {
        $success_message = "Event '{$_POST['event_name']}' has been successfully created!";
    } else {
        $error_message = "Failed to create event. Please try again.";
    }
}


?>