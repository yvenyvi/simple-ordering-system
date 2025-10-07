<?php

// Handle delete request
if (isset($_GET['deleteid'])) {
    $delete_id = intval($_GET['deleteid']); // Sanitize input
    
    if ($delete_id > 0) {
        // Use centralized delete function
        $result = delete_record('events', $delete_id);
        
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
    } else {
        $error_message = "Invalid event ID provided.";
    }
    
    redirect_to("event_list.php");
}

if (isset($_POST['event_name'])) {
    // Validate input data
    $errors = [];
    
    if (empty(trim($_POST['event_name']))) {
        $errors[] = "Event name is required";
    }
    if (empty($_POST['event_date'])) {
        $errors[] = "Event date is required";
    } elseif (strtotime($_POST['event_date']) < strtotime(date('Y-m-d'))) {
        $errors[] = "Event date cannot be in the past";
    }
    if (empty($_POST['event_time'])) {
        $errors[] = "Event time is required";
    }
    if (empty(trim($_POST['location']))) {
        $errors[] = "Location is required";
    }
    if (empty($_POST['capacity']) || !is_numeric($_POST['capacity']) || intval($_POST['capacity']) <= 0) {
        $errors[] = "Valid capacity is required";
    }
    if (!is_numeric($_POST['price']) || floatval($_POST['price']) < 0) {
        $errors[] = "Valid price is required";
    }
    if (empty(trim($_POST['event_type']))) {
        $errors[] = "Event type is required";
    }
    
    // Check if email is provided and validate format
    if (!empty($_POST['contact_email']) && !filter_var($_POST['contact_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Check if event name and date combination already exists
    global $connection;
    $event_check_sql = "SELECT COUNT(*) as count FROM events WHERE event_name = ? AND event_date = ?";
    $stmt = mysqli_prepare($connection, $event_check_sql);
    mysqli_stmt_bind_param($stmt, "ss", $_POST['event_name'], $_POST['event_date']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['count'] > 0) {
        $errors[] = "An event with this name already exists on the selected date";
    }
    mysqli_stmt_close($stmt);
    
    if (empty($errors)) {
        // Prepare secure data
        $data = array(
            'event_name' => trim($_POST['event_name']),
            'description' => trim($_POST['description']),
            'event_date' => $_POST['event_date'],
            'event_time' => $_POST['event_time'],
            'location' => trim($_POST['location']),
            'capacity' => intval($_POST['capacity']),
            'price' => floatval($_POST['price']),
            'event_type' => trim($_POST['event_type']),
            'contact_email' => trim($_POST['contact_email']),
            'contact_phone' => trim($_POST['contact_phone']),
            'requirements' => trim($_POST['requirements']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        );

        if (save('events', $data)) {
            $success_message = "Event '{$data['event_name']}' has been successfully created!";
        } else {
            $error_message = "Failed to create event. Please try again.";
        }
    } else {
        $error_message = "Please fix the following errors: " . implode(", ", $errors);
    }
}


?>