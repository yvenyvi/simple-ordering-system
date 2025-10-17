<?php
/**
 * Event Management Controller
 * Handles CRUD operations for events
 */

require_once __DIR__ . "/../../models/db_Model.php";

// Route requests to appropriate handlers
if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])) {
    handleViewEvent();
} elseif (isset($_GET['deleteid'])) {
    handleDeleteEvent();
} elseif (isset($_POST['event_name'])) {
    handleCreateEvent();
}

/**
 * Handle AJAX request to view event details
 */
function handleViewEvent() {
    header('Content-Type: application/json');
    
    $event_id = intval($_GET['id']);
    
    if ($event_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid event ID']);
        exit;
    }
    
    global $connection;
    
    try {
        // Fetch event details
        $sql = "SELECT event_id, event_name, description, event_date, event_time, location, capacity, price, 
                event_type, is_active, image_url, contact_email, contact_phone, requirements, created_at 
                FROM events WHERE event_id = ?";
        
        $stmt = mysqli_prepare($connection, $sql);
        if (!$stmt) {
            throw new Exception('Database prepare error: ' . mysqli_error($connection));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $event_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Database execute error: ' . mysqli_stmt_error($stmt));
        }
        
        $result = mysqli_stmt_get_result($stmt);
        
        if ($event = mysqli_fetch_assoc($result)) {
            echo json_encode(['success' => true, 'event' => $event]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Event not found with ID: ' . $event_id]);
        }
        
        mysqli_stmt_close($stmt);
        
    } catch (Exception $e) {
        error_log("Error in handleViewEvent: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to load event details']);
    }
    
    exit;
}

/**
 * Handle event deletion request
 */
function handleDeleteEvent() {
    $delete_id = intval($_GET['deleteid']);
    
    if ($delete_id <= 0) {
        $GLOBALS['error_message'] = "Invalid event ID provided.";
        redirect_to("event_list.php");
        return;
    }
    
    // Use centralized delete function
    $result = delete_record('events', $delete_id);
    
    if ($result['success']) {
        $GLOBALS['success_message'] = $result['message'];
    } else {
        $GLOBALS['error_message'] = $result['message'];
    }
    
    redirect_to("event_list.php");
}

/**
 * Handle event creation request
 */
function handleCreateEvent() {
    // Validate input data
    $validation_result = validateEventData($_POST);
    
    if (!$validation_result['valid']) {
        $GLOBALS['error_message'] = "Please fix the following errors: " . implode(", ", $validation_result['errors']);
        return;
    }
    
    // Check for duplicate event
    if (isDuplicateEvent($_POST['event_name'], $_POST['event_date'])) {
        $GLOBALS['error_message'] = "An event with this name already exists on the selected date";
        return;
    }
    
    // Prepare event data
    $event_data = prepareEventData($_POST);
    
    // Save event to database
    $new_id = save('events', $event_data);
    
    if ($new_id) {
        // Handle image upload if present
        $image_uploaded = handleEventImageUpload($new_id);
        
        // Set success message
        $message_suffix = $image_uploaded ? ' with image!' : '!';
        $GLOBALS['success_message'] = "Event '{$event_data['event_name']}' has been successfully created{$message_suffix}";
    } else {
        $GLOBALS['error_message'] = "Failed to create event. Please try again.";
    }
}

/**
 * Validate event form data
 * 
 * @param array $data POST data to validate
 * @return array Validation result with 'valid' boolean and 'errors' array
 */
function validateEventData($data) {
    $errors = [];
    
    // Required field validations
    if (empty(trim($data['event_name'] ?? ''))) {
        $errors[] = "Event name is required";
    }
    
    if (empty($data['event_date'] ?? '')) {
        $errors[] = "Event date is required";
    } elseif (strtotime($data['event_date']) < strtotime(date('Y-m-d'))) {
        $errors[] = "Event date cannot be in the past";
    }
    
    if (empty($data['event_time'] ?? '')) {
        $errors[] = "Event time is required";
    }
    
    if (empty(trim($data['location'] ?? ''))) {
        $errors[] = "Location is required";
    }
    
    if (empty($data['capacity']) || !is_numeric($data['capacity']) || intval($data['capacity']) <= 0) {
        $errors[] = "Valid capacity is required";
    }
    
    if (!is_numeric($data['price']) || floatval($data['price']) < 0) {
        $errors[] = "Valid price is required";
    }
    
    if (empty(trim($data['event_type'] ?? ''))) {
        $errors[] = "Event type is required";
    }
    
    // Email format validation (if provided)
    if (!empty($data['contact_email']) && !filter_var($data['contact_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Check if event with same name and date already exists
 * 
 * @param string $event_name
 * @param string $event_date
 * @return bool
 */
function isDuplicateEvent($event_name, $event_date) {
    global $connection;
    
    $sql = "SELECT COUNT(*) as count FROM events WHERE event_name = ? AND event_date = ?";
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $event_name, $event_date);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $row['count'] > 0;
}

/**
 * Prepare event data for database insertion
 * 
 * @param array $post_data POST data
 * @return array Sanitized event data
 */
function prepareEventData($post_data) {
    return [
        'event_name' => trim($post_data['event_name']),
        'description' => trim($post_data['description'] ?? ''),
        'event_date' => $post_data['event_date'],
        'event_time' => $post_data['event_time'],
        'location' => trim($post_data['location']),
        'capacity' => intval($post_data['capacity']),
        'price' => floatval($post_data['price']),
        'event_type' => trim($post_data['event_type']),
        'contact_email' => trim($post_data['contact_email'] ?? ''),
        'contact_phone' => trim($post_data['contact_phone'] ?? ''),
        'requirements' => trim($post_data['requirements'] ?? ''),
        'is_active' => isset($post_data['is_active']) ? 1 : 0
    ];
}

/**
 * Handle event image upload and update database
 * 
 * @param int $event_id
 * @return bool True if image was uploaded and processed
 */
function handleEventImageUpload($event_id) {
    if (!isset($_FILES['fileField']) || !$_FILES['fileField']['tmp_name']) {
        return false;
    }
    
    global $connection;
    
    try {
        $image_url = "../assets/images/events/{$event_id}.jpg";
        
        // Update the event record with the image URL
        $update_sql = "UPDATE events SET image_url = ? WHERE event_id = ?";
        $stmt = mysqli_prepare($connection, $update_sql);
        
        if (!$stmt) {
            throw new Exception('Failed to prepare image update statement');
        }
        
        mysqli_stmt_bind_param($stmt, "si", $image_url, $event_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Failed to update image URL in database');
        }
        
        mysqli_stmt_close($stmt);
        return true;
        
    } catch (Exception $e) {
        error_log("Error updating event image URL: " . $e->getMessage());
        return false;
    }
}

?>