<?php
/**
 * Database Model - Core Database Operations
 * Handles database connection, basic CRUD operations, and utility functions
 */

// Database Configuration
define("DB_SERVER", "localhost");
define("DB_USER", "root");
define("DB_PASS", "password");
define("DB_NAME", "delicious_eats");

// Establish Database Connection
$connection = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
if (mysqli_connect_errno()) {
    die("Database connection failed: " .
        mysqli_connect_error() .
        "(" . mysqli_connect_errno() . ")"
    );
}

// Load Dependencies
require_once dirname(__FILE__) . '/TableConfig.php';
require_once dirname(__FILE__) . '/HtmlGenerator.php';
require_once dirname(__FILE__) . '/admin_display_model.php';

/**
 * =============================================================================
 * UTILITY FUNCTIONS
 * =============================================================================
 */

/**
 * Redirect to new location
 */
function redirect_to($new_location) {
    header("Location: " . $new_location);
    exit();
}

/**
 * Confirm query result
 */
function confirm_query($result_set) {
    if (!$result_set) {
        die("Database query failed!");
    }
}

/**
 * =============================================================================
 * CORE DATABASE OPERATIONS
 * =============================================================================
 */

/**
 * Save data to database (INSERT operation)
 * Note: UPDATE functionality has been temporarily disabled
 */
function save($table, $data) {
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
    
    // Check if table has timestamp columns
    $table_columns = getTableColumns($table);
    $has_created_at = isset($table_columns['created_at']);
    $has_updated_at = isset($table_columns['updated_at']);
    
    // INSERT operation
    $fields = implode(", ", array_keys($escaped_data));
    $values = implode(", ", array_values($escaped_data));
    
    // Add timestamp fields only if they exist in the table
    if ($has_created_at && $has_updated_at) {
        $sql_query = "INSERT INTO $table ($fields, created_at, updated_at) VALUES ($values, NOW(), NOW())";
    } elseif ($has_created_at) {
        $sql_query = "INSERT INTO $table ($fields, created_at) VALUES ($values, NOW())";
    } elseif ($has_updated_at) {
        $sql_query = "INSERT INTO $table ($fields, updated_at) VALUES ($values, NOW())";
    } else {
        $sql_query = "INSERT INTO $table ($fields) VALUES ($values)";
    }
    
    $result = mysqli_query($connection, $sql_query) or die(mysqli_error($connection));
    $new_id = mysqli_insert_id($connection);
        
    // Handle file upload if present
    if (isset($_FILES['fileField']) && $_FILES['fileField']['tmp_name']) {
        $newname = "$new_id.jpg";
        
        $upload_dir = get_upload_directory($table);
        $upload_path = $upload_dir . $newname;
        
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        if (move_uploaded_file($_FILES['fileField']['tmp_name'], $upload_path)) {
            $id_field_name = get_id_field_name($table);
            
            $image_url = str_replace("../", "", $upload_path);
            $update_sql = "UPDATE $table SET image_url = '" . mysqli_real_escape_string($connection, $image_url) . "' WHERE $id_field_name = '$new_id'";
            mysqli_query($connection, $update_sql);
        }
    }
    
    confirm_query($result);
    return $new_id;
}

/**
 * Update existing record in database
 */
function update($table, $data, $id, $options = array()) {
    global $connection;
    
    // Validate inputs
    if (empty($table) || empty($data) || empty($id)) {
        return ['success' => false, 'message' => 'Missing required parameters'];
    }
    
    // Sanitize ID
    $id = intval($id);
    if ($id <= 0) {
        return ['success' => false, 'message' => 'Invalid ID provided'];
    }
    
    // Get table configuration
    $id_field = get_id_field_name($table);
    $table_columns = getTableColumns($table);
    
    if (empty($table_columns)) {
        return ['success' => false, 'message' => 'Invalid table specified'];
    }
    
    // Check if record exists
    $check_sql = "SELECT COUNT(*) as count FROM `$table` WHERE `$id_field` = ?";
    $stmt = mysqli_prepare($connection, $check_sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if ($row['count'] == 0) {
        return ['success' => false, 'message' => 'Record not found'];
    }
    
    // Custom validation if provided
    if (isset($options['validator']) && is_callable($options['validator'])) {
        $validation = call_user_func($options['validator'], $data, $id, $table);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }
    }
    
    try {
        // Start transaction
        mysqli_autocommit($connection, false);
        
        // Filter data to only include valid table columns
        $filtered_data = array();
        foreach ($data as $field => $value) {
            if (isset($table_columns[$field])) {
                $filtered_data[$field] = $value;
            }
        }
        
        if (empty($filtered_data)) {
            mysqli_rollback($connection);
            mysqli_autocommit($connection, true);
            return ['success' => false, 'message' => 'No valid fields to update'];
        }
        
        // Build SET clause
        $set_clauses = array();
        $values = array();
        $types = '';
        
        foreach ($filtered_data as $field => $value) {
            $set_clauses[] = "`$field` = ?";
            
            if ($value === null) {
                $values[] = null;
                $types .= 's';
            } else {
                $values[] = $value;
                // Determine type based on table column info
                if (strpos($table_columns[$field]['type'], 'int') !== false || 
                    strpos($table_columns[$field]['type'], 'decimal') !== false ||
                    strpos($table_columns[$field]['type'], 'float') !== false ||
                    strpos($table_columns[$field]['type'], 'double') !== false) {
                    $types .= is_float($value) ? 'd' : 'i';
                } else {
                    $types .= 's';
                }
            }
        }
        
        // Add updated_at if column exists
        $has_updated_at = isset($table_columns['updated_at']);
        if ($has_updated_at) {
            $set_clauses[] = "`updated_at` = NOW()";
        }
        
        // Add ID parameter
        $values[] = $id;
        $types .= 'i';
        
        // Build and execute UPDATE query
        $sql = "UPDATE `$table` SET " . implode(', ', $set_clauses) . " WHERE `$id_field` = ?";
        $stmt = mysqli_prepare($connection, $sql);
        
        if (!$stmt) {
            throw new Exception('Failed to prepare update statement: ' . mysqli_error($connection));
        }
        
        // Bind parameters dynamically
        if (!empty($values)) {
            mysqli_stmt_bind_param($stmt, $types, ...$values);
        }
        
        $result = mysqli_stmt_execute($stmt);
        
        if (!$result) {
            throw new Exception('Failed to execute update: ' . mysqli_stmt_error($stmt));
        }
        
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        // Handle file upload if present
        if (isset($_FILES['fileField']) && $_FILES['fileField']['tmp_name']) {
            $upload_success = handle_file_upload_for_update($table, $id);
            if (!$upload_success['success']) {
                // Log warning but don't fail the update
                error_log("Warning: File upload failed during update: " . $upload_success['message']);
            }
        }
        
        // Commit transaction
        mysqli_commit($connection);
        mysqli_autocommit($connection, true);
        
        if ($affected_rows > 0) {
            return ['success' => true, 'message' => 'Record updated successfully', 'affected_rows' => $affected_rows];
        } else {
            return ['success' => true, 'message' => 'No changes made (data identical)', 'affected_rows' => 0];
        }
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($connection);
        mysqli_autocommit($connection, true);
        error_log("Error updating record in table '$table': " . $e->getMessage());
        return ['success' => false, 'message' => 'Update failed: ' . $e->getMessage()];
    }
}

/**
 * Handle file upload for record updates
 */
function handle_file_upload_for_update($table, $id) {
    global $connection;
    
    $newname = "$id.jpg";
    $upload_dir = get_upload_directory($table);
    $upload_path = $upload_dir . $newname;
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    if (move_uploaded_file($_FILES['fileField']['tmp_name'], $upload_path)) {
        $id_field_name = get_id_field_name($table);
        $image_url = str_replace("../", "", $upload_path);
        
        $update_sql = "UPDATE `$table` SET image_url = ? WHERE `$id_field_name` = ?";
        $stmt = mysqli_prepare($connection, $update_sql);
        mysqli_stmt_bind_param($stmt, "si", $image_url, $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        if ($result) {
            return ['success' => true, 'message' => 'File uploaded successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update image URL in database'];
        }
    } else {
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }
}

/**
 * =============================================================================
 * HELPER FUNCTIONS
 * =============================================================================
 */

/**
 * Get upload directory for table
 */
function get_upload_directory($table) {
    return "../assets/images/" . TableConfig::getImageDirectory($table) . "/";
}

/**
 * Get ID field name for table
 */
function get_id_field_name($table) {
    return TableConfig::getIdField($table);
}

/**
 * Get image path for table record
 */
function get_image_path($row, $table) {
    $base_dir = get_upload_directory($table);
    $id_field = get_id_field_name($table);
    $id = $row[$id_field] ?? $row['id'] ?? null;
    $placeholder = $base_dir . 'placeholder.jpg';
    
    // Check if image_url exists in row
    if (!empty($row['image_url'])) {
        if (file_exists($row['image_url'])) {
            return $row['image_url'];
        } elseif (file_exists('../' . preg_replace('/^(\.\.\/)+/', '', $row['image_url']))) {
            return '../' . preg_replace('/^(\.\.\/)+/', '', $row['image_url']);
        }
    }
    
    // Check for ID-based image
    if ($id && file_exists($base_dir . $id . '.jpg')) {
        return $base_dir . $id . '.jpg';
    }
    
    return $placeholder;
}

/**
 * Get table column information
 */
function getTableColumns($table_name) {
    global $connection;
    
    $columns = array();
    $query = "SHOW COLUMNS FROM `$table_name`";
    $result = mysqli_query($connection, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $columns[$row['Field']] = array(
                'type' => $row['Type'],
                'null' => $row['Null'],
                'key' => $row['Key'],
                'default' => $row['Default'],
                'extra' => $row['Extra']
            );
        }
    }
    
    return $columns;
}

/**
 * =============================================================================
 * TABLE DISPLAY FUNCTIONS
 * =============================================================================
 */

/**
 * Universal table display function
 */
/**
 * Universal table display function
 */
function display_table($table_name, $sql = null, $options = array()) {
    display_admin_table($table_name, $sql, $options);
}

/**
 * =============================================================================
 * CONVENIENCE FUNCTIONS FOR SPECIFIC TABLES
 * =============================================================================
 */

/**
 * Display menu table with predefined configuration
 */
function display_menu_table($sql = null) {
    $options = [
        'columns' => [
            'image_url' => ['label' => 'Image', 'type' => 'image'],
            'name' => ['label' => 'Name', 'type' => 'string'],
            'category' => ['label' => 'Category', 'type' => 'string'],
            'price' => ['label' => 'Price', 'type' => 'price'],
            'preparation_time' => ['label' => 'Prep Time', 'type' => 'prep_time'],
            'is_available' => ['label' => 'Available', 'type' => 'boolean'],
            'created_at' => ['label' => 'Created', 'type' => 'datetime']
        ],
        'actions' => TableConfig::getActions('menu')
    ];
    display_table('menu', $sql, $options);
}

/**
 * Display users table with predefined configuration
 */
function display_users_table($sql = null) {
    $options = [
        'actions' => TableConfig::getActions('users')
    ];
    display_table('users', $sql, $options);
}

/**
 * Display events table with predefined configuration
 */
function display_events_table($sql = null) {
    $options = [
        'actions' => TableConfig::getActions('events')
    ];
    display_table('events', $sql, $options);
}

/**
 * Display orders table with predefined configuration
 */
function display_orders_table($sql = null) {
    $options = [
        'columns' => [
            'order_id' => ['label' => 'Order #', 'type' => 'string'],
            'customer_name' => ['label' => 'Customer', 'type' => 'string'],
            'customer_email' => ['label' => 'Email', 'type' => 'email'],
            'phone' => ['label' => 'Phone', 'type' => 'phone'],
            'item_count' => ['label' => 'Items', 'type' => 'string'],
            'total_amount' => ['label' => 'Total', 'type' => 'price'],
            'status' => ['label' => 'Status', 'type' => 'status'],
            'payment_status' => ['label' => 'Payment', 'type' => 'status'],
            'order_date' => ['label' => 'Order Date', 'type' => 'datetime']
        ],
        'actions' => TableConfig::getActions('orders')
    ];
    
    display_table('orders', $sql, $options);
}

/**
 * =============================================================================
 * RECORD MANAGEMENT FUNCTIONS
 * =============================================================================
 */

/**
 * Delete record from table
 */
function delete_record($table, $id_value) {
    global $connection;
    
    // Sanitize the ID
    $id_value = intval($id_value);
    if ($id_value <= 0) {
        return ['success' => false, 'message' => 'Invalid ID provided'];
    }
    
    // Get the correct ID field name for the table
    $id_field = get_id_field_name($table);
    
    try {
        // Start transaction for data integrity
        mysqli_autocommit($connection, false);
        
        // Handle table-specific cleanup before deletion
        $cleanup_result = handle_pre_delete_cleanup($table, $id_field, $id_value);
        if (!$cleanup_result['success']) {
            mysqli_rollback($connection);
            return $cleanup_result;
        }
        
        // Perform the actual deletion with prepared statement
        $sql = "DELETE FROM `$table` WHERE `$id_field` = ? LIMIT 1";
        $stmt = mysqli_prepare($connection, $sql);
        
        if (!$stmt) {
            mysqli_rollback($connection);
            return ['success' => false, 'message' => 'Failed to prepare delete statement'];
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id_value);
        $result = mysqli_stmt_execute($stmt);
        
        if (!$result) {
            mysqli_rollback($connection);
            mysqli_stmt_close($stmt);
            return ['success' => false, 'message' => 'Failed to delete record: ' . mysqli_error($connection)];
        }
        
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        if ($affected_rows === 0) {
            mysqli_rollback($connection);
            return ['success' => false, 'message' => 'No record found with the specified ID'];
        }
        
        // Commit the transaction
        mysqli_commit($connection);
        mysqli_autocommit($connection, true);
        
        return ['success' => true, 'message' => ucfirst($table) . ' record deleted successfully'];
        
    } catch (Exception $e) {
        mysqli_rollback($connection);
        mysqli_autocommit($connection, true);
        return ['success' => false, 'message' => 'Error deleting record: ' . $e->getMessage()];
    }
}


function handle_pre_delete_cleanup($table, $id_field, $id_value) {
    global $connection;
    
    switch ($table) {
        case 'menu':
        case 'events':
            // Get and delete associated image file
            $image_result = get_and_delete_image($table, $id_field, $id_value);
            if (!$image_result['success']) {
                return $image_result;
            }
            break;
            
        case 'orders':
            // Delete associated order items first (cascade delete)
            $items_sql = "DELETE FROM order_items WHERE order_id = ?";
            $stmt = mysqli_prepare($connection, $items_sql);
            mysqli_stmt_bind_param($stmt, "i", $id_value);
            
            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                return ['success' => false, 'message' => 'Failed to delete order items'];
            }
            mysqli_stmt_close($stmt);
            break;
            
        case 'users':
            // Check if user has any orders before deletion
            $check_sql = "SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?";
            $stmt = mysqli_prepare($connection, $check_sql);
            mysqli_stmt_bind_param($stmt, "i", $id_value);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            
            if ($row['order_count'] > 0) {
                return ['success' => false, 'message' => 'Cannot delete user with existing orders. Please handle orders first.'];
            }
            break;
    }
    
    return ['success' => true, 'message' => 'Pre-delete cleanup completed'];
}

function get_and_delete_image($table, $id_field, $id_value) {
    global $connection;
    
    // Get the image URL before deleting
    $get_image_sql = "SELECT image_url FROM `$table` WHERE `$id_field` = ? LIMIT 1";
    $stmt = mysqli_prepare($connection, $get_image_sql);
    mysqli_stmt_bind_param($stmt, "i", $id_value);
    mysqli_stmt_execute($stmt);
    $image_result = mysqli_stmt_get_result($stmt);
    $image_row = mysqli_fetch_array($image_result);
    mysqli_stmt_close($stmt);
    
    // Remove the image file if it exists and is not a placeholder
    if ($image_row && $image_row['image_url'] && strpos($image_row['image_url'], 'placeholder.jpg') === false) {
        $image_path = $image_row['image_url'];
        
        // Handle both relative and absolute paths
        if (!file_exists($image_path)) {
            // Try relative path from current directory
            $image_path = "../" . $image_row['image_url'];
        }
        
        if (file_exists($image_path)) {
            if (!unlink($image_path)) {
                // Image deletion failed, but don't stop the record deletion
                error_log("Warning: Failed to delete image file: $image_path");
            }
        }
    }
    
    return ['success' => true, 'message' => 'Image cleanup completed'];
}

// ======================================================================
// USER AUTHENTICATION FUNCTIONS
// ======================================================================

/**
 * Authenticate user login
 * @param string $email User's email
 * @param string $password Plain text password
 * @return array Array with 'success' boolean and 'user' data or 'message' error
 */
function authenticate_user($email, $password) {
    global $connection;
    
    if (!$connection) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    try {
        // Prepare statement to get user by email
        $sql = "SELECT user_id, first_name, last_name, email, password, is_active 
                FROM users WHERE email = ? AND is_active = 1 LIMIT 1";
        
        $stmt = mysqli_prepare($connection, $sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error occurred'];
        }
        
        mysqli_stmt_bind_param($stmt, "s", $email);
        
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return ['success' => false, 'message' => 'Database error occurred'];
        }
        
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Remove password from user data for security
            unset($user['password']);
            return ['success' => true, 'user' => $user];
        } else {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
    } catch (Exception $e) {
        error_log("Authentication error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Authentication failed'];
    }
}

/**
 * Register a new user
 * @param array $user_data Array containing user information
 * @return array Array with 'success' boolean and 'user_id' or 'message'
 */
function register_user($user_data) {
    global $connection;
    
    if (!$connection) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    // Validate required fields
    $required_fields = ['first_name', 'last_name', 'email', 'password'];
    foreach ($required_fields as $field) {
        if (empty($user_data[$field])) {
            return ['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
        }
    }
    
    // Validate email format
    if (!filter_var($user_data['email'], FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email format'];
    }
    
    // Validate password length
    if (strlen($user_data['password']) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters'];
    }
    
    try {
        // Check if email already exists
        $check_email_sql = "SELECT COUNT(*) as count FROM users WHERE email = ?";
        $stmt = mysqli_prepare($connection, $check_email_sql);
        mysqli_stmt_bind_param($stmt, "s", $user_data['email']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if ($row['count'] > 0) {
            return ['success' => false, 'message' => 'Email address already exists'];
        }
        
        // Hash password
        $hashed_password = password_hash($user_data['password'], PASSWORD_DEFAULT);
        
        // Prepare user data for insertion
        $insert_data = [
            'first_name' => trim($user_data['first_name']),
            'last_name' => trim($user_data['last_name']),
            'email' => trim($user_data['email']),
            'password' => $hashed_password,
            'phone' => isset($user_data['phone']) ? trim($user_data['phone']) : '',
            'address' => isset($user_data['address']) ? trim($user_data['address']) : '',
            'city' => isset($user_data['city']) ? trim($user_data['city']) : '',
            'state' => isset($user_data['state']) ? trim($user_data['state']) : '',
            'zip_code' => isset($user_data['zip_code']) ? trim($user_data['zip_code']) : '',
            'is_active' => 1
        ];
        
        // Use existing save function
        $user_id = save('users', $insert_data);
        
        if ($user_id) {
            return ['success' => true, 'user_id' => $user_id, 'message' => 'Account created successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to create account. Please try again.'];
        }
        
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Registration failed'];
    }
}

/**
 * Start user session
 * @param array $user User data from database
 */
function start_user_session($user) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['user_logged_in'] = true;
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_first_name'] = $user['first_name'];
    $_SESSION['user_last_name'] = $user['last_name'];
    $_SESSION['login_time'] = time();
}

/**
 * Check if user is logged in
 * @return bool True if user is logged in, false otherwise
 */
function is_user_logged_in() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
}

/**
 * Get logged in user data with complete profile information
 * @return array|null User data or null if not logged in
 */
function get_logged_in_user() {
    if (!is_user_logged_in()) {
        return null;
    }
    
    global $connection;
    $user_id = $_SESSION['user_id'];
    
    // Fetch complete user data from database
    $query = "SELECT user_id, first_name, last_name, email, phone, address, city, state, zip_code, created_at, updated_at 
              FROM users WHERE user_id = ? AND is_active = 1";
    
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // Add login time from session
        $row['login_time'] = $_SESSION['login_time'];
        mysqli_stmt_close($stmt);
        return $row;
    }
    
    mysqli_stmt_close($stmt);
    
    // If user not found in database, logout
    logout_user();
    return null;
}

/**
 * Logout user and destroy session
 */
function logout_user() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Require user login - redirect to login page if not logged in
 * @param string $redirect_url URL to redirect to after login
 */
function require_user_login($redirect_url = null) {
    if (!is_user_logged_in()) {
        $login_url = 'login.php';
        if ($redirect_url) {
            $login_url .= '?redirect=' . urlencode($redirect_url);
        }
        redirect_to($login_url);
        exit;
    }
}


?>
