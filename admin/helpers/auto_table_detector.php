<?php
/**
 * Automatic Table Detection System
 * Automatically generates table configurations using database introspection
 * Follows DRY principles and works with any table
 */

/**
 * Get automatic table configuration for any table
 */
function getAutoTableConfig($table_name) {
    global $connection;
    
    // Get table structure
    $columns = getTableColumns($table_name);
    $sample_data = getSampleTableData($table_name);
    
    // Generate configuration automatically
    $config = array(
        'title' => formatTableTitle($table_name),
        'icon' => getTableIcon($table_name),
        'empty_message' => "No " . strtolower(formatTableTitle($table_name)) . " have been added yet.",
        'page' => $table_name . '_list.php',
        'id_field' => detectIdField($columns),
        'name_field' => detectNameField($columns),
        'image_directory' => getImageDirectory($table_name),
        'columns' => generateColumnConfig($columns, $table_name, $sample_data)
    );
    
    return $config;
}

/**
 * Get all columns and their metadata for a table
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
 * Get sample data to help determine column types
 */
function getSampleTableData($table_name) {
    global $connection;
    
    $query = "SELECT * FROM `$table_name` LIMIT 1";
    $result = mysqli_query($connection, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return array();
}

/**
 * Detect the primary ID field
 */
function detectIdField($columns) {
    // Look for primary key first
    foreach ($columns as $field => $info) {
        if ($info['key'] === 'PRI') {
            return $field;
        }
    }
    
    // Fallback to common ID patterns
    $id_patterns = array('id', '_id$', '^.*_id$');
    foreach ($id_patterns as $pattern) {
        foreach ($columns as $field => $info) {
            if (preg_match("/$pattern/i", $field)) {
                return $field;
            }
        }
    }
    
    return 'id'; // Default fallback
}

/**
 * Detect the name/title field(s)
 */
function detectNameField($columns) {
    // Single name fields (priority order)
    $name_fields = array('name', 'title', 'event_name', 'product_name', 'item_name');
    foreach ($name_fields as $field) {
        if (isset($columns[$field])) {
            return $field;
        }
    }
    
    // Composite name fields (first_name + last_name)
    if (isset($columns['first_name']) && isset($columns['last_name'])) {
        return array('first_name', 'last_name');
    }
    
    // Fallback to first text field
    foreach ($columns as $field => $info) {
        if (strpos($info['type'], 'varchar') !== false || strpos($info['type'], 'text') !== false) {
            return $field;
        }
    }
    
    return 'name'; // Default fallback
}

/**
 * Generate automatic column configuration
 */
function generateColumnConfig($columns, $table_name, $sample_data = array()) {
    $config = array();
    $column_count = 0;
    $max_columns = 8; // Limit columns for readability
    
    foreach ($columns as $field => $info) {
        if ($column_count >= $max_columns) break;
        
        // Skip certain fields
        if (shouldSkipField($field)) {
            continue;
        }
        
        $column_type = detectColumnType($field, $info, $sample_data);
        $header = formatColumnHeader($field);
        
        $config[$column_type] = $header;
        $column_count++;
    }
    
    // Always add actions column last
    $config['actions'] = 'Actions';
    
    return $config;
}

/**
 * Detect what type of column this is for rendering
 */
function detectColumnType($field, $info, $sample_data = array()) {
    $field_lower = strtolower($field);
    
    // ID field
    if ($info['key'] === 'PRI' || strpos($field_lower, 'id') !== false) {
        return 'id';
    }
    
    // Image field
    if (strpos($field_lower, 'image') !== false || $field_lower === 'photo' || $field_lower === 'picture') {
        return 'image';
    }
    
    // Name/title field
    if (in_array($field_lower, array('name', 'title', 'event_name', 'first_name', 'last_name'))) {
        return 'name';
    }
    
    // Category field
    if (strpos($field_lower, 'category') !== false || strpos($field_lower, 'type') !== false) {
        if (strpos($field_lower, 'event') !== false) {
            return 'type';
        }
        return 'category';
    }
    
    // Price field
    if (strpos($field_lower, 'price') !== false || strpos($field_lower, 'cost') !== false) {
        return 'price';
    }
    
    // Time/duration fields
    if (strpos($field_lower, 'preparation_time') !== false || strpos($field_lower, 'prep_time') !== false) {
        return 'prep_time';
    }
    
    // Capacity field
    if (strpos($field_lower, 'capacity') !== false) {
        return 'capacity';
    }
    
    // Status fields
    if (strpos($field_lower, 'is_') === 0 || strpos($field_lower, 'active') !== false || 
        strpos($field_lower, 'available') !== false || strpos($field_lower, 'enabled') !== false) {
        return 'status';
    }
    
    // Date fields
    if (strpos($field_lower, 'date') !== false && $field_lower !== 'updated_at') {
        if (strpos($field_lower, 'event') !== false) {
            return 'event_date';
        }
        return 'date';
    }
    
    // Email field
    if (strpos($field_lower, 'email') !== false) {
        return 'email';
    }
    
    // Phone field
    if (strpos($field_lower, 'phone') !== false) {
        return 'phone';
    }
    
    // Location fields
    if (in_array($field_lower, array('location', 'address', 'city', 'state'))) {
        return 'location';
    }
    
    // Default to the field name
    return $field;
}

/**
 * Fields that should be skipped in table display
 */
function shouldSkipField($field) {
    $skip_fields = array(
        'password', 'created_at', 'updated_at', 'deleted_at', 
        'hash', 'token', 'secret', 'salt', 'zip_code', 'description',
        'ingredients', 'address', 'state' // Skip some verbose fields
    );
    
    return in_array(strtolower($field), $skip_fields);
}

/**
 * Format field name into readable header
 */
function formatColumnHeader($field) {
    // Handle common patterns
    $replacements = array(
        '_' => ' ',
        'id' => 'ID',
        'url' => 'URL',
        'is_' => '',
        'event_' => '',
        'preparation_' => 'Prep '
    );
    
    $header = $field;
    foreach ($replacements as $search => $replace) {
        $header = str_replace($search, $replace, $header);
    }
    
    return ucwords(trim($header));
}

/**
 * Format table name into readable title
 */
function formatTableTitle($table_name) {
    $title = ucwords(str_replace('_', ' ', $table_name));
    
    // Handle plurals
    if (substr($title, -1) === 's') {
        return $title;
    }
    
    return $title;
}

/**
 * Get appropriate icon for table
 */
function getTableIcon($table_name) {
    $icons = array(
        'menu' => 'fas fa-utensils',
        'users' => 'fas fa-users',
        'events' => 'fas fa-calendar',
        'orders' => 'fas fa-shopping-cart',
        'categories' => 'fas fa-tags',
        'products' => 'fas fa-box'
    );
    
    return $icons[$table_name] ?? 'fas fa-table';
}

/**
 * Get image directory for table
 */
function getImageDirectory($table_name) {
    $directories = array(
        'menu' => 'products',
        'events' => 'events',
        'users' => 'users'
    );
    
    return $directories[$table_name] ?? $table_name;
}
?>