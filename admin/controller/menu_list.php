<?php
require_once __DIR__ . "/../../models/db_Model.php";

// Handle AJAX requests for menu details
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_GET['action']) {
            case 'get_menu_details':
                handleGetMenuDetails();
                break;
            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        error_log("Menu controller exception: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server error occurred']);
    }
    exit;
}

// Handle get menu details AJAX request
function handleGetMenuDetails() {
    global $connection;
    
    try {
        if (!isset($_GET['menu_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Menu ID required']);
            return;
        }
        
        $menu_id = intval($_GET['menu_id']);
        
        // Get menu details
        $menu_query = "SELECT * FROM menu WHERE menu_id = ?";
        
        $stmt = mysqli_prepare($connection, $menu_query);
        if (!$stmt) {
            throw new Exception("Failed to prepare menu query: " . mysqli_error($connection));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $menu_id);
        mysqli_stmt_execute($stmt);
        $menu_result = mysqli_stmt_get_result($stmt);
        
        if (!$menu_result || mysqli_num_rows($menu_result) === 0) {
            echo json_encode(['success' => false, 'message' => 'Menu item not found']);
            mysqli_stmt_close($stmt);
            return;
        }
        
        $menu = mysqli_fetch_assoc($menu_result);
        mysqli_stmt_close($stmt);
        
        echo json_encode([
            'success' => true,
            'menu' => $menu
        ]);
        
    } catch (Exception $e) {
        error_log("Error in handleGetMenuDetails: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to get menu details'
        ]);
    }
}

// Handle delete request
if (isset($_GET['deleteid'])) {
    $delete_id = intval($_GET['deleteid']); // Sanitize input
    
    if ($delete_id > 0) {
        // Use centralized delete function
        $result = delete_record('menu', $delete_id);
        
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
    } else {
        $error_message = "Invalid menu item ID provided.";
    }
    
    redirect_to("menu_list.php");
}

if (isset($_POST['name'])) {
    // Validate input data
    $errors = [];
    
    if (empty(trim($_POST['name']))) {
        $errors[] = "Menu item name is required";
    }
    if (empty(trim($_POST['category']))) {
        $errors[] = "Category is required";
    }
    if (empty($_POST['price']) || !is_numeric($_POST['price']) || floatval($_POST['price']) <= 0) {
        $errors[] = "Valid price is required";
    }
    
    // Check if menu item name already exists
    global $connection;
    $name_check_sql = "SELECT COUNT(*) as count FROM menu WHERE name = ?";
    $stmt = mysqli_prepare($connection, $name_check_sql);
    mysqli_stmt_bind_param($stmt, "s", $_POST['name']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['count'] > 0) {
        $errors[] = "Menu item with this name already exists";
    }
    mysqli_stmt_close($stmt);
    
    if (empty($errors)) {
        // Prepare secure data
        $data = array(
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description']),
            'category' => trim($_POST['category']),
            'price' => floatval($_POST['price']),
            'ingredients' => trim($_POST['ingredients']),
            'preparation_time' => intval($_POST['preparation_time']),
            'is_available' => isset($_POST['is_available']) ? 1 : 0
        );

        $new_id = save('menu', $data);
        
        if ($new_id) {
            // If a file was uploaded, update the database with the image URL
            if (isset($_FILES['fileField']) && $_FILES['fileField']['tmp_name']) {
                $image_url = "../assets/images/products/{$new_id}.jpg";
                
                // Update the menu record with the image URL using prepared statement
                $update_sql = "UPDATE menu SET image_url = ? WHERE menu_id = ?";
                $stmt = mysqli_prepare($connection, $update_sql);
                mysqli_stmt_bind_param($stmt, "si", $image_url, $new_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                
                $success_message = "Menu item '{$data['name']}' has been successfully added with image!";
            } else {
                $success_message = "Menu item '{$data['name']}' has been successfully added!";
            }
        } else {
            $error_message = "Failed to add menu item. Please try again.";
        }
    } else {
        $error_message = "Please fix the following errors: " . implode(", ", $errors);
    }
}

?>