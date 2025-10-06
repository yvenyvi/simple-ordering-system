<?php

function display_admin_table($table_name, $sql = null, $options = array()) {
    global $connection;
    
    // Get table columns for configuration
    $columns = getTableColumns($table_name);
    if (empty($columns)) {
        echo '<div class="admin-empty-state">';
        echo '<i class="fas fa-exclamation-triangle"></i>';
        echo '<h3>Table Error</h3>';
        echo '<p>Could not load table configuration for: ' . htmlspecialchars($table_name) . '</p>';
        echo '</div>';
        return;
    }
    
    // Build SQL query if not provided
    if (!$sql) {
        $sql = "SELECT * FROM " . $table_name . " ORDER BY created_at DESC";
    }
    
    // Execute query
    $result = mysqli_query($connection, $sql);
    if (!$result) {
        echo '<div class="admin-empty-state">';
        echo '<i class="fas fa-exclamation-triangle"></i>';
        echo '<h3>Query Error</h3>';
        echo '<p>Error executing query: ' . mysqli_error($connection) . '</p>';
        echo '</div>';
        return;
    }
    
    $rowCount = mysqli_num_rows($result);
    
    // Configuration
    $config = array_merge(array(
        'title' => ucfirst($table_name),
        'icon' => getTableIcon($table_name),
        'empty_message' => 'No ' . $table_name . ' found. Click the button above to add your first entry.',
        'id_field' => $table_name . '_id',
        'image_directory' => getImageDirectory($table_name),
        'columns' => $options['columns'] ?? generateDisplayColumns($columns, $table_name),
        'actions' => $options['actions'] ?? array('delete')
    ), $options);
    
    // Render the table
    renderAdminTable($table_name, $result, $rowCount, $config, $options);
}

/**
 * Render the complete admin table HTML with modern, clean styling
 */
function renderAdminTable($table_name, $result, $rowCount, $config, $options) {

    // Statistics cards
    renderStatsCards($table_name, $rowCount, $config);
    
    if ($rowCount === 0) {
        echo '<div class="admin-empty-state">';
        echo '<i class="' . $config['icon'] . '"></i>';
        echo '<h3>No Data Found</h3>';
        echo '<p>' . $config['empty_message'] . '</p>';
        echo '</div>';
        return;
    }
    
    // Enhanced table container
    echo '<div class="admin-enhanced-table">';
    echo '<table>';
    echo '<thead>';
    echo '<tr>';
    foreach ($config['columns'] as $key => $column) {
        echo '<th>' . htmlspecialchars($column['label']) . '</th>';
    }
    echo '<th>Actions</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    while ($row = mysqli_fetch_array($result)) {
        echo '<tr>';
        foreach ($config['columns'] as $key => $column) {
            echo '<td>';
            echo formatColumnValue($row, $key, $column, $table_name);
            echo '</td>';
        }
        echo '<td>';
        echo renderActionButtons($row, $table_name, $config);
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}

/**
 * Render statistics cards for the admin overview
 */
function renderStatsCards($table_name, $rowCount, $config) {
    echo '<div class="admin-stats-cards">';
    
    // Total count card
    echo '<div class="admin-stat-card">';
    echo '<h3>Total ' . $config['title'] . '</h3>';
    echo '<div class="stat-number">' . $rowCount . '</div>';
    echo '</div>';
    
    // Additional stats based on table type
    if ($table_name === 'menu') {
        global $connection;
        $available_count = mysqli_fetch_array(mysqli_query($connection, "SELECT COUNT(*) as count FROM menu WHERE is_available = 1"))['count'];
        echo '<div class="admin-stat-card">';
        echo '<h3>Available Items</h3>';
        echo '<div class="stat-number">' . $available_count . '</div>';
        echo '</div>';
    } elseif ($table_name === 'users') {
        global $connection;
        $active_count = mysqli_fetch_array(mysqli_query($connection, "SELECT COUNT(*) as count FROM users WHERE is_active = 1"))['count'];
        echo '<div class="admin-stat-card">';
        echo '<h3>Active Users</h3>';
        echo '<div class="stat-number">' . $active_count . '</div>';
        echo '</div>';
    } elseif ($table_name === 'events') {
        global $connection;
        $upcoming_count = mysqli_fetch_array(mysqli_query($connection, "SELECT COUNT(*) as count FROM events WHERE event_date >= CURDATE()"))['count'];
        echo '<div class="admin-stat-card">';
        echo '<h3>Upcoming Events</h3>';
        echo '<div class="stat-number">' . $upcoming_count . '</div>';
        echo '</div>';
    }
    
    echo '</div>';
}

/**
 * Format individual column values with rich display types
 */
function formatColumnValue($row, $column_key, $column_config, $table_name) {
    $value = $row[$column_key] ?? '';
    $display_type = $column_config['type'] ?? detectColumnDisplayType($column_key, $column_config);
    
    switch ($display_type) {
        case 'image':
            if (empty($value)) {
                return '<img src="../assets/images/' . getImageDirectory($table_name) . '/placeholder.jpg" alt="No Image" class="cell-image">';
            }
            $image_path = $value;
            if (strpos($value, '../') !== 0) {
                $image_path = '../' . $value;
            }
            return '<img src="' . htmlspecialchars($image_path) . '" alt="' . htmlspecialchars($row['name'] ?? 'Image') . '" class="cell-image">';
            
        case 'price':
            return '<span class="cell-price">$' . number_format((float)$value, 2) . '</span>';
            
        case 'date':
            if (empty($value)) return '<span class="cell-date">-</span>';
            return '<span class="cell-date">' . date('M d, Y', strtotime($value)) . '</span>';
            
        case 'datetime':
            if (empty($value)) return '<span class="cell-date">-</span>';
            return '<span class="cell-date">' . date('M d, Y g:i A', strtotime($value)) . '</span>';
            
        case 'boolean':
            $is_true = ($value == 1 || $value === 'true' || $value === true);
            $class = $is_true ? 'true' : 'false';
            $text = $is_true ? 'Yes' : 'No';
            return '<span class="cell-boolean ' . $class . '">' . $text . '</span>';
            
        case 'email':
            if (empty($value)) return '<span class="text-muted">-</span>';
            return '<a href="mailto:' . htmlspecialchars($value) . '" class="cell-email">' . htmlspecialchars($value) . '</a>';
            
        case 'phone':
            if (empty($value)) return '<span class="text-muted">-</span>';
            return '<a href="tel:' . htmlspecialchars($value) . '" class="cell-phone">' . htmlspecialchars($value) . '</a>';
            
        case 'url':
            if (empty($value)) return '<span class="text-muted">-</span>';
            $display_url = strlen($value) > 30 ? substr($value, 0, 30) . '...' : $value;
            return '<a href="' . htmlspecialchars($value) . '" target="_blank" class="cell-url">' . htmlspecialchars($display_url) . '</a>';
            
        case 'text':
            if (empty($value)) return '<span class="text-muted">-</span>';
            $display_text = strlen($value) > 100 ? substr($value, 0, 100) . '...' : $value;
            return '<span class="cell-text" title="' . htmlspecialchars($value) . '">' . htmlspecialchars($display_text) . '</span>';
            
        default:
            return htmlspecialchars($value);
    }
}

/**
 * Render action buttons for each row
 */
function renderActionButtons($row, $table_name, $config) {
    $id_field = $config['id_field'];
    $id = $row[$id_field] ?? $row['id'] ?? '';
    
    // Smart name detection based on table type
    $name = 'Item';
    if ($table_name === 'users') {
        $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
        if (empty(trim($name))) {
            $name = $row['email'] ?? 'User';
        }
    } elseif ($table_name === 'events') {
        $name = $row['event_name'] ?? $row['title'] ?? 'Event';
    } else {
        $name = $row['name'] ?? $row['title'] ?? 'Item';
    }
    
    // Properly escape name for JavaScript to prevent syntax errors
    $safe_name = addslashes(strip_tags($name));
    
    $html = '<div class="action-buttons">';
    
    // Delete button with properly escaped parameters
    $html .= '<a href="#" class="btn-action btn-delete" onclick="confirmDelete(' . intval($id) . ', \'' . $safe_name . '\', \'' . $table_name . '_list.php\');">';
    $html .= '<i class="fas fa-trash"></i>';
    $html .= '</a>';
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Detect column display type based on field name and info
 */
function detectColumnDisplayType($field, $info) {
    $field_lower = strtolower($field);
    
    // Image fields
    if (strpos($field_lower, 'image') !== false || strpos($field_lower, 'photo') !== false) {
        return 'image';
    }
    
    // Price/money fields
    if (strpos($field_lower, 'price') !== false || strpos($field_lower, 'cost') !== false || 
        strpos($field_lower, 'amount') !== false || strpos($field_lower, 'fee') !== false) {
        return 'price';
    }
    
    // Email fields
    if (strpos($field_lower, 'email') !== false || strpos($field_lower, 'mail') !== false) {
        return 'email';
    }
    
    // Phone fields  
    if (strpos($field_lower, 'phone') !== false || strpos($field_lower, 'tel') !== false ||
        strpos($field_lower, 'mobile') !== false) {
        return 'phone';
    }
    
    // URL fields
    if (strpos($field_lower, 'url') !== false || strpos($field_lower, 'link') !== false ||
        strpos($field_lower, 'website') !== false) {
        return 'url';
    }
    
    // Date fields
    if (isset($info['type']) && strpos($info['type'], 'date') !== false && strpos($info['type'], 'time') === false) {
        return 'date';
    }
    
    // DateTime fields
    if (isset($info['type']) && (strpos($info['type'], 'datetime') !== false || strpos($info['type'], 'timestamp') !== false)) {
        return 'datetime';
    }
    
    // Boolean fields
    if (isset($info['type']) && (strpos($info['type'], 'tinyint(1)') !== false || $field_lower === 'is_active' || 
        $field_lower === 'is_available' || $field_lower === 'active' || 
        strpos($field_lower, 'is_') === 0)) {
        return 'boolean';
    }
    
    // Text fields
    if (isset($info['type']) && (strpos($info['type'], 'text') !== false || strpos($info['type'], 'longtext') !== false)) {
        return 'text';
    }
    
    return 'string';
}

/**
 * Generate display columns configuration automatically
 */
function generateDisplayColumns($columns, $table_name) {
    $display_columns = array();
    
    foreach ($columns as $column_name => $column_info) {
        // Skip technical fields
        if (in_array($column_name, ['created_at', 'updated_at']) && count($columns) > 5) {
            continue;
        }
        
        $display_columns[$column_name] = array(
            'label' => ucwords(str_replace('_', ' ', $column_name)),
            'type' => detectColumnDisplayType($column_name, $column_info),
            'sortable' => true
        );
    }
    
    return $display_columns;
}

/**
 * Get appropriate icon for table type
 */
function getTableIcon($table_name) {
    $icons = array(
        'menu' => 'fas fa-utensils',
        'users' => 'fas fa-users',
        'events' => 'fas fa-calendar-alt',
        'orders' => 'fas fa-shopping-cart',
        'categories' => 'fas fa-tags'
    );
    
    return $icons[$table_name] ?? 'fas fa-table';
}

/**
 * Get image directory for table type
 */
function getImageDirectory($table_name) {
    $directories = array(
        'menu' => 'products',
        'events' => 'events',
        'users' => 'users'
    );
    
    return $directories[$table_name] ?? 'general';
}

/**
 * Convenience functions for common tables (following user_display_model pattern)
 */
function display_admin_menu($sql = null) {
    display_admin_table('menu', $sql);
}

function display_admin_users($sql = null) {
    display_admin_table('users', $sql);
}

function display_admin_events($sql = null) {
    display_admin_table('events', $sql);
}
?>