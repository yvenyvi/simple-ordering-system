<?php
require_once __DIR__ . "/../../models/db_Model.php";

// Handle AJAX requests for menu details
if (isset($_GET['action']) || isset($_POST['action'])) {
    header('Content-Type: application/json');

    $action = $_GET['action'] ?? $_POST['action'];

    try {
        switch ($action) {
            case 'get_menu_details':
                handleGetMenuDetails();
                break;
            case 'toggle_availability':
                handleToggleAvailability();
                break;
            case 'get_menu_for_edit':
                handleGetMenuForEdit();
                break;
            case 'update_menu':
                handleUpdateMenu();
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
function handleGetMenuDetails()
{
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

// Handle toggle availability AJAX request
function handleToggleAvailability()
{
    global $connection;

    try {
        if (!isset($_POST['menu_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Menu ID required']);
            return;
        }

        $menu_id = intval($_POST['menu_id']);

        // First get the current status
        $status_query = "SELECT is_available FROM menu WHERE menu_id = ?";
        $stmt = mysqli_prepare($connection, $status_query);
        if (!$stmt) {
            throw new Exception("Failed to prepare status query: " . mysqli_error($connection));
        }

        mysqli_stmt_bind_param($stmt, "i", $menu_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (!$result || mysqli_num_rows($result) === 0) {
            echo json_encode(['success' => false, 'message' => 'Menu item not found']);
            mysqli_stmt_close($stmt);
            return;
        }

        $row = mysqli_fetch_assoc($result);
        $current_status = intval($row['is_available']);
        mysqli_stmt_close($stmt);

        // Toggle the status
        $new_status = $current_status ? 0 : 1;

        // Update availability
        $update_query = "UPDATE menu SET is_available = ?, updated_at = NOW() WHERE menu_id = ?";

        $stmt = mysqli_prepare($connection, $update_query);
        if (!$stmt) {
            throw new Exception("Failed to prepare update query: " . mysqli_error($connection));
        }

        mysqli_stmt_bind_param($stmt, "ii", $new_status, $menu_id);
        $result = mysqli_stmt_execute($stmt);

        if (!$result) {
            throw new Exception('Database update failed');
        }

        if (mysqli_stmt_affected_rows($stmt) === 0) {
            echo json_encode(['success' => false, 'message' => 'No changes made']);
            return;
        }
        mysqli_stmt_close($stmt);

        $status_text = $new_status ? 'available' : 'unavailable';

        // Clear any lingering error messages
        unset($GLOBALS['error_message']);
        unset($GLOBALS['success_message']);

        echo json_encode([
            'success' => true,
            'message' => "Menu item marked as {$status_text}",
            'new_status' => $new_status
        ]);
    } catch (Exception $e) {
        error_log("Error in handleToggleAvailability: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to toggle availability'
        ]);
    }
}

// Handle get menu for edit AJAX request
function handleGetMenuForEdit()
{
    global $connection;

    try {
        if (!isset($_GET['menu_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Menu ID required']);
            return;
        }

        $menu_id = intval($_GET['menu_id']);

        // Get menu details for editing
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
        error_log("Error in handleGetMenuForEdit: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to get menu for editing'
        ]);
    }
}

// Handle update menu AJAX request
function handleUpdateMenu()
{
    global $connection;

    try {
        if (!isset($_POST['menu_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Menu ID required']);
            return;
        }

        $menu_id = intval($_POST['menu_id']);

        // Validate category against ENUM values
        $valid_categories = ['pizza', 'burgers', 'pasta', 'salads', 'desserts', 'beverages'];
        $category = trim($_POST['category'] ?? '');

        if (!in_array($category, $valid_categories)) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid category. Must be one of: ' . implode(', ', $valid_categories)
            ]);
            return;
        }

        // Prepare menu data
        $menu_data = [
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description']),
            'category' => $category,
            'price' => floatval($_POST['price']),
            'ingredients' => trim($_POST['ingredients']),
            'nutritional_info' => trim($_POST['nutritional_info']),
            'preparation_time' => intval($_POST['preparation_time']),
            'is_available' => isset($_POST['is_available']) ? 1 : 0
        ];

        // Create custom validator for menu items
        $validator = function ($data, $id, $table) use ($connection) {
            $errors = [];

            if (empty($data['name'])) {
                $errors[] = "Menu item name is required";
            }
            if (empty($data['category'])) {
                $errors[] = "Category is required";
            }
            if (empty($data['price']) || $data['price'] <= 0) {
                $errors[] = "Valid price is required";
            }

            // Check for duplicate name (excluding current item)
            $name_check_sql = "SELECT COUNT(*) as count FROM menu WHERE name = ? AND menu_id != ?";
            $stmt = mysqli_prepare($connection, $name_check_sql);
            mysqli_stmt_bind_param($stmt, "si", $data['name'], $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if ($row['count'] > 0) {
                $errors[] = "Menu item with this name already exists";
            }

            if (!empty($errors)) {
                return ['valid' => false, 'message' => implode(', ', $errors)];
            }

            return ['valid' => true];
        };

        // Debug logging
        error_log("Updating menu item ID: $menu_id with data: " . json_encode($menu_data));

        // Update menu item using unified update function
        $update_result = update('menu', $menu_data, $menu_id, ['validator' => $validator]);

        error_log("Menu update result: " . json_encode($update_result));

        if ($update_result['success']) {
            // Clear any lingering error messages
            unset($GLOBALS['error_message']);
            unset($GLOBALS['success_message']);

            echo json_encode([
                'success' => true,
                'message' => "Menu item '{$menu_data['name']}' updated successfully"
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => $update_result['message']
            ]);
        }
    } catch (Exception $e) {
        error_log("Error in handleUpdateMenu: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update menu item: ' . $e->getMessage()
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

// Handle NEW menu item creation (not AJAX updates)
// Only run if this is a regular form submission, not an AJAX request
if (
    isset($_POST['name']) &&
    !isset($_POST['action']) &&
    !isset($_POST['menu_id']) &&
    !isset($_GET['action']) &&
    (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') &&
    $_SERVER['REQUEST_METHOD'] === 'POST'
) {


    // Validate category against ENUM values first
    $valid_categories = ['pizza', 'burgers', 'pasta', 'salads', 'desserts', 'beverages'];
    $category = trim($_POST['category'] ?? '');

    if (!in_array($category, $valid_categories)) {
        $error_message = 'Invalid category. Must be one of: ' . implode(', ', $valid_categories);
    } else {
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

        // Check if menu item name already exists (for NEW items only)
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
                'category' => $category,
                'price' => floatval($_POST['price']),
                'ingredients' => trim($_POST['ingredients']),
                'nutritional_info' => trim($_POST['nutritional_info']),
                'preparation_time' => intval($_POST['preparation_time']),
                'is_available' => isset($_POST['is_available']) ? 1 : 0
            );

            // Debug logging for new item creation
            error_log("Creating new menu item: " . json_encode($data));

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

                error_log("Successfully created menu item with ID: $new_id");
            } else {
                $error_message = "Failed to add menu item. Please try again.";
                error_log("Failed to create new menu item");
            }
        } else {
            $error_message = "Please fix the following errors: " . implode(", ", $errors);
        }
    }
}
