<?php

/**
 * Admin Display Model
 * Handles admin-specific display functions, table rendering, and audit logging
 * Provides comprehensive backend functionality for administrative interface
 */

/**
 * =============================================================================
 * MAIN ADMIN TABLE DISPLAY FUNCTIONS
 * =============================================================================
 */

/**
 * Display admin table with full configuration support
 */
function display_admin_table($table_name, $sql = null, $options = array()) {
    global $connection;
    
    // Get table columns for configuration
    $columns = getTableColumns($table_name);
    if (empty($columns)) {
        echo HtmlGenerator::renderEmptyState(
            'fas fa-exclamation-triangle',
            'Table Error',
            'Could not load table configuration for: ' . $table_name
        );
        return;
    }
    
    // Build SQL query if not provided with smart ordering
    if (!$sql) {
        $order_by = TableConfig::getOrderBy($table_name);
        $sql = "SELECT * FROM " . $table_name . " ORDER BY " . $order_by;
    }
    
    // Execute query
    $result = mysqli_query($connection, $sql);
    if (!$result) {
        echo HtmlGenerator::renderEmptyState(
            'fas fa-exclamation-triangle',
            'Query Error',
            'Error executing query: ' . mysqli_error($connection)
        );
        return;
    }

    $rowCount = mysqli_num_rows($result);
    
    // Configuration using centralized TableConfig
    $config = array_merge(array(
        'title' => TableConfig::getTitle($table_name),
        'icon' => TableConfig::getIcon($table_name),
        'empty_message' => 'No ' . $table_name . ' found. Click the button above to add your first entry.',
        'id_field' => TableConfig::getIdField($table_name),
        'image_directory' => TableConfig::getImageDirectory($table_name),
        'columns' => $options['columns'] ?? generateDisplayColumns($columns, $table_name),
        'actions' => $options['actions'] ?? TableConfig::getActions($table_name)
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
    global $connection;
    
    echo '<div class="admin-stats-grid">';
    
    // Enhanced stats based on table type
    $columns = getTableColumns($table_name);
    
    if ($table_name === 'menu') {
        // Menu-specific enhanced stats
        $total_result = mysqli_query($connection, "SELECT COUNT(*) as count FROM $table_name");
        $total_count = mysqli_fetch_array($total_result)['count'];
        
        $available_result = mysqli_query($connection, "SELECT COUNT(*) as count FROM $table_name WHERE is_available = 1");
        $available_count = mysqli_fetch_array($available_result)['count'];
        $unavailable_count = $total_count - $available_count;
        
        $categories_result = mysqli_query($connection, "SELECT COUNT(DISTINCT category) as count FROM $table_name");
        $categories_count = mysqli_fetch_array($categories_result)['count'];
        
        $avg_price_result = mysqli_query($connection, "SELECT AVG(price) as avg_price FROM $table_name WHERE is_available = 1");
        $avg_price = mysqli_fetch_array($avg_price_result)['avg_price'];
        
        echo '<div class="admin-stat-card total-items">';
        echo '<div class="stat-icon"><i class="fas fa-utensils"></i></div>';
        echo '<div class="stat-content">';
        echo '<h3>Total Menu Items</h3>';
        echo '<div class="stat-number">' . $total_count . '</div>';
        echo '<small>' . $categories_count . ' categories</small>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="admin-stat-card available-items">';
        echo '<div class="stat-icon"><i class="fas fa-check-circle"></i></div>';
        echo '<div class="stat-content">';
        echo '<h3>Available Items</h3>';
        echo '<div class="stat-number">' . $available_count . '</div>';
        echo '<small>' . $unavailable_count . ' unavailable</small>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="admin-stat-card price-info">';
        echo '<div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>';
        echo '<div class="stat-content">';
        echo '<h3>Average Price</h3>';
        echo '<div class="stat-number">$' . number_format($avg_price, 2) . '</div>';
        echo '<small>for available items</small>';
        echo '</div>';
        echo '</div>';
        
    } elseif ($table_name === 'users') {
        // User-specific enhanced stats
        $total_result = mysqli_query($connection, "SELECT COUNT(*) as count FROM $table_name");
        $total_count = $total_result ? mysqli_fetch_array($total_result)['count'] : 0;
        
        // Check if created_at column exists before querying recent stats
        $columns = getTableColumns($table_name);
        $recent_count = 0;
        if (isset($columns['created_at'])) {
            $recent_result = mysqli_query($connection, "SELECT COUNT(*) as count FROM $table_name WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $recent_count = $recent_result ? mysqli_fetch_array($recent_result)['count'] : 0;
        }
        
        // Check if status column exists before querying
        $active_count = 0;
        if (isset($columns['status'])) {
            $active_result = mysqli_query($connection, "SELECT COUNT(*) as count FROM $table_name WHERE status = 'active'");
            if ($active_result) {
                $active_count = mysqli_fetch_array($active_result)['count'];
            } else {
                error_log("Failed to query status for table $table_name: " . mysqli_error($connection));
                $active_count = 0;
            }
        } else {
            // If no status column, assume all users are active
            $active_count = $total_count;
        }
        
        echo '<div class="admin-stat-card total-users">';
        echo '<div class="stat-icon"><i class="fas fa-users"></i></div>';
        echo '<div class="stat-content">';
        echo '<h3>Total Users</h3>';
        echo '<div class="stat-number">' . $total_count . '</div>';
        echo '<small>' . $active_count . ' active users</small>';
        echo '</div>';
        echo '</div>';
        
        // Only show new users card if we have created_at column
        if (isset($columns['created_at'])) {
            echo '<div class="admin-stat-card new-users">';
            echo '<div class="stat-icon"><i class="fas fa-user-plus"></i></div>';
            echo '<div class="stat-content">';
            echo '<h3>New This Month</h3>';
            echo '<div class="stat-number">' . $recent_count . '</div>';
            echo '<small>last 30 days</small>';
            echo '</div>';
            echo '</div>';
        }
        
    } elseif ($table_name === 'orders') {
        // Order-specific enhanced stats
        $total_result = mysqli_query($connection, "SELECT COUNT(*) as count, SUM(total_amount) as revenue FROM $table_name WHERE status != 'cancelled'");
        $total_data = mysqli_fetch_array($total_result);
        
        $today_result = mysqli_query($connection, "SELECT COUNT(*) as count, SUM(total_amount) as revenue FROM $table_name WHERE DATE(order_date) = CURDATE() AND status != 'cancelled'");
        $today_data = mysqli_fetch_array($today_result);
        
        $pending_result = mysqli_query($connection, "SELECT COUNT(*) as count FROM $table_name WHERE status IN ('pending', 'confirmed', 'preparing', 'ready')");
        $pending_count = mysqli_fetch_array($pending_result)['count'];
        
        $completed_result = mysqli_query($connection, "SELECT COUNT(*) as count FROM $table_name WHERE status = 'delivered'");
        $completed_count = mysqli_fetch_array($completed_result)['count'];
        
        echo '<div class="admin-stat-card total-orders">';
        echo '<div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>';
        echo '<div class="stat-content">';
        echo '<h3>Total Orders</h3>';
        echo '<div class="stat-number">' . $total_data['count'] . '</div>';
        echo '<small>$' . number_format($total_data['revenue'], 2) . ' revenue</small>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="admin-stat-card today-orders">';
        echo '<div class="stat-icon"><i class="fas fa-calendar-day"></i></div>';
        echo '<div class="stat-content">';
        echo '<h3>Today\'s Orders</h3>';
        echo '<div class="stat-number">' . $today_data['count'] . '</div>';
        echo '<small>$' . number_format($today_data['revenue'], 2) . ' today</small>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="admin-stat-card pending-orders">';
        echo '<div class="stat-icon"><i class="fas fa-clock"></i></div>';
        echo '<div class="stat-content">';
        echo '<h3>Pending Orders</h3>';
        echo '<div class="stat-number">' . $pending_count . '</div>';
        echo '<small>need attention</small>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="admin-stat-card completed-orders">';
        echo '<div class="stat-icon"><i class="fas fa-check-circle"></i></div>';
        echo '<div class="stat-content">';
        echo '<h3>Completed Orders</h3>';
        echo '<div class="stat-number">' . $completed_count . '</div>';
        echo '<small>delivered</small>';
        echo '</div>';
        echo '</div>';
        
    } else {
        // Generic enhanced stats for other tables
        $total_result = mysqli_query($connection, "SELECT COUNT(*) as count FROM $table_name");
        if ($total_result) {
            $total_count = mysqli_fetch_array($total_result)['count'];
            echo '<div class="admin-stat-card">';
            echo '<div class="stat-icon"><i class="fas fa-list"></i></div>';
            echo '<div class="stat-content">';
            echo '<h3>Total ' . $config['title'] . '</h3>';
            echo '<div class="stat-number">' . $total_count . '</div>';
            echo '</div>';
            echo '</div>';
        }
        
        // Look for common status/boolean fields
        foreach ($columns as $column_name => $column_info) {
            if (strpos($column_name, 'is_') === 0 || $column_name === 'active' || $column_name === 'available') {
                $result = mysqli_query($connection, "SELECT COUNT(*) as count FROM $table_name WHERE $column_name = 1");
                if ($result) {
                    $active_count = mysqli_fetch_array($result)['count'];
                    $label = ucwords(str_replace(['is_', '_'], ['', ' '], $column_name));
                    echo '<div class="admin-stat-card">';
                    echo '<div class="stat-icon"><i class="fas fa-check-circle"></i></div>';
                    echo '<div class="stat-content">';
                    echo '<h3>' . $label . '</h3>';
                    echo '<div class="stat-number">' . $active_count . '</div>';
                    echo '</div>';
                    echo '</div>';
                    break; // Only show one status card to avoid clutter
                }
            }
        }
        
        // Look for date-based stats (future events, recent items, etc.)
        if (isset($columns['event_date'])) {
            $result = mysqli_query($connection, "SELECT COUNT(*) as count FROM $table_name WHERE event_date >= CURDATE()");
            if ($result) {
                $upcoming_count = mysqli_fetch_array($result)['count'];
                echo '<div class="admin-stat-card upcoming-events">';
                echo '<div class="stat-icon"><i class="fas fa-calendar-plus"></i></div>';
                echo '<div class="stat-content">';
                echo '<h3>Upcoming Events</h3>';
                echo '<div class="stat-number">' . $upcoming_count . '</div>';
                echo '<small>scheduled</small>';
                echo '</div>';
                echo '</div>';
            }
        } elseif (isset($columns['created_at']) && $table_name !== 'menu' && $table_name !== 'users') {
            $result = mysqli_query($connection, "SELECT COUNT(*) as count FROM $table_name WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            if ($result) {
                $recent_count = mysqli_fetch_array($result)['count'];
                echo '<div class="admin-stat-card recent-items">';
                echo '<div class="stat-icon"><i class="fas fa-clock"></i></div>';
                echo '<div class="stat-content">';
                echo '<h3>Recent (30 days)</h3>';
                echo '<div class="stat-number">' . $recent_count . '</div>';
                echo '<small>new additions</small>';
                echo '</div>';
                echo '</div>';
            }
        }
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
            
        case 'prep_time':
            if (empty($value)) return '<span class="text-muted">-</span>';
            return '<span class="cell-prep-time">' . intval($value) . ' min</span>';
            
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
            
        case 'status':
            // For orders table, show dropdown for status updates
            if ($table_name === 'orders' && $column_key === 'status') {
                $order_id = $row['order_id'] ?? '';
                $status_options = [
                    'pending' => 'Pending',
                    'confirmed' => 'Confirmed',
                    'preparing' => 'Preparing',
                    'ready' => 'Ready',
                    'delivered' => 'Delivered',
                    'cancelled' => 'Cancelled'
                ];
                
                $status_classes = [
                    'pending' => 'warning',
                    'confirmed' => 'info', 
                    'preparing' => 'primary',
                    'ready' => 'success',
                    'delivered' => 'success',
                    'cancelled' => 'danger'
                ];
                
                $class = $status_classes[$value] ?? 'secondary';
                
                $dropdown = '<select class="form-select form-select-sm order-status-dropdown bg-' . $class . ' text-white border-0" 
                                     data-order-id="' . $order_id . '" 
                                     data-current-status="' . htmlspecialchars($value) . '"
                                     style="max-width: 180px; font-weight: 500;">';
                
                foreach ($status_options as $status_value => $status_label) {
                    $selected = ($value === $status_value) ? 'selected' : '';
                    $dropdown .= '<option value="' . $status_value . '" ' . $selected . '>' . $status_label . '</option>';
                }
                
                $dropdown .= '</select>';
                return $dropdown;
            }
            
            // For other tables or payment_status, show badge
            $status_classes = [
                'pending' => 'warning',
                'confirmed' => 'info', 
                'preparing' => 'primary',
                'ready' => 'success',
                'delivered' => 'success',
                'cancelled' => 'danger',
                'paid' => 'success',
                'failed' => 'danger',
                'refunded' => 'info'
            ];
            $class = $status_classes[$value] ?? 'secondary';
            return '<span class="badge bg-' . $class . '">' . ucfirst($value) . '</span>';
            
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
    
    // Dynamic name detection - try multiple common name patterns
    $name = 'Item';
    
    // Try composite name fields first (first_name + last_name)
    if (isset($row['first_name']) || isset($row['last_name'])) {
        $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
    }
    
    // If no composite name, try single name fields
    if (empty(trim($name))) {
        $name_fields = ['name', 'title', 'event_name', 'category_name', 'product_name', 'item_name'];
        foreach ($name_fields as $field) {
            if (!empty($row[$field])) {
                $name = $row[$field];
                break;
            }
        }
    }
    
    // Fallback to email or other identifier
    if (empty(trim($name)) || $name === 'Item') {
        $fallback_fields = ['email', 'username', 'code', 'reference'];
        foreach ($fallback_fields as $field) {
            if (!empty($row[$field])) {
                $name = $row[$field];
                break;
            }
        }
    }
    
    // Final fallback
    if (empty(trim($name)) || $name === 'Item') {
        $name = ucfirst($table_name) . ' #' . $id;
    }
    
    // Properly escape name for JavaScript to prevent syntax errors
    $safe_name = addslashes(strip_tags($name));
    
    // Map table names to correct admin page filenames
    $page_mapping = [
        'users' => 'user_list.php',
        'menu' => 'menu_list.php', 
        'events' => 'event_list.php',
        'orders' => 'order_list.php'
    ];
    
    $admin_page = isset($page_mapping[$table_name]) ? $page_mapping[$table_name] : $table_name . '_list.php';
    
    $html = '<div class="action-buttons">';
    
    // Get the actions from config (default to just delete)
    $actions = $config['actions'] ?? ['delete'];
    
    // View button
    if (in_array('view', $actions)) {
        if ($table_name === 'orders') {
            $html .= '<a href="#" class="btn-action btn-view" onclick="viewOrderDetails(' . intval($id) . '); return false;" title="View Details">';
        } elseif ($table_name === 'menu') {
            $html .= '<a href="#" class="btn-action btn-view" onclick="viewMenuDetails(' . intval($id) . '); return false;" title="View Details">';
        } elseif ($table_name === 'users') {
            $html .= '<a href="#" class="btn-action btn-view" onclick="viewUserDetails(' . intval($id) . '); return false;" title="View Details">';
        } elseif ($table_name === 'events') {
            $html .= '<a href="#" class="btn-action btn-view" onclick="viewEventDetails(' . intval($id) . '); return false;" title="View Details">';
        } else {
            $html .= '<a href="#" class="btn-action btn-view" onclick="viewDetails(' . intval($id) . '); return false;" title="View Details">';
        }
        $html .= '<i class="fas fa-eye"></i>';
        $html .= '</a>';
    }
    
    // Edit button
    if (in_array('edit', $actions)) {
        if ($table_name === 'menu') {
            $html .= '<a href="#" class="btn-action btn-edit" onclick="editMenuItem(' . intval($id) . '); return false;" title="Edit Item">';
        } elseif ($table_name === 'users') {
            $html .= '<a href="#" class="btn-action btn-edit" onclick="editUser(' . intval($id) . '); return false;" title="Edit User">';
        } elseif ($table_name === 'events') {
            $html .= '<a href="#" class="btn-action btn-edit" onclick="editEvent(' . intval($id) . '); return false;" title="Edit Event">';
        } else {
            $html .= '<a href="#" class="btn-action btn-edit" onclick="editItem(' . intval($id) . '); return false;" title="Edit">';
        }
        $html .= '<i class="fas fa-edit"></i>';
        $html .= '</a>';
    }
    
    // Toggle availability button (for menu items)
    if (in_array('toggle', $actions) && $table_name === 'menu') {
        $is_available = $row['is_available'] ?? 0;
        $toggle_class = $is_available ? 'btn-toggle-on' : 'btn-toggle-off';
        $toggle_title = $is_available ? 'Make Unavailable' : 'Make Available';
        $toggle_icon = $is_available ? 'fas fa-toggle-on' : 'fas fa-toggle-off';
        
        $html .= '<a href="#" class="btn-action ' . $toggle_class . '" onclick="toggleAvailability(' . intval($id) . ', ' . intval($is_available) . '); return false;" title="' . $toggle_title . '">';
        $html .= '<i class="' . $toggle_icon . '"></i>';
        $html .= '</a>';
    }
    
    // Toggle active status button (for events)
    if (in_array('toggle', $actions) && $table_name === 'events') {
        $is_active = $row['is_active'] ?? 0;
        $toggle_class = $is_active ? 'btn-toggle-on' : 'btn-toggle-off';
        $toggle_title = $is_active ? 'Deactivate Event' : 'Activate Event';
        $toggle_icon = $is_active ? 'fas fa-toggle-on' : 'fas fa-toggle-off';
        
        $html .= '<a href="#" class="btn-action ' . $toggle_class . '" onclick="toggleEventStatus(' . intval($id) . ', ' . intval($is_active) . '); return false;" title="' . $toggle_title . '">';
        $html .= '<i class="' . $toggle_icon . '"></i>';
        $html .= '</a>';
    }
    
    // Delete button
    if (in_array('delete', $actions)) {
        $html .= '<a href="#" class="btn-action btn-delete" onclick="confirmDelete(' . intval($id) . ', \'' . $safe_name . '\', \'' . $admin_page . '\'); return false;" title="Delete">';
        $html .= '<i class="fas fa-trash"></i>';
        $html .= '</a>';
    }
    
    $html .= '</div>';
    
    return $html;
}

function detectColumnDisplayType($field, $info) {
    $field_lower = strtolower($field);
    
    if (strpos($field_lower, 'image') !== false || strpos($field_lower, 'photo') !== false) {
        return 'image';
    }
    
    if (strpos($field_lower, 'price') !== false || strpos($field_lower, 'cost') !== false || 
        strpos($field_lower, 'amount') !== false || strpos($field_lower, 'fee') !== false) {
        return 'price';
    }
    
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
    
    // Status fields (enum or varchar with status-like names)
    if (strpos($field_lower, 'status') !== false || 
        (isset($info['type']) && strpos($info['type'], 'enum') !== false)) {
        return 'status';
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
    $excluded_fields = TableConfig::getExcludedFields($table_name);
    
    foreach ($columns as $column_name => $column_info) {
        // Skip excluded fields
        if (in_array($column_name, $excluded_fields)) {
            continue;
        }
        
        // Skip technical fields (but keep created_at for users table since we need it for filtering)
        if ($column_name === 'updated_at' || 
            ($column_name === 'created_at' && $table_name !== 'users' && count($columns) > 5)) {
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
 * Safely execute a count query only if the specified column exists
 */
function safeCountQuery($table_name, $column_name, $condition = null) {
    global $connection;
    
    $columns = getTableColumns($table_name);
    
    // Check if column exists
    if (!isset($columns[$column_name])) {
        return 0;
    }
    
    // Build the query
    $sql = "SELECT COUNT(*) as count FROM `$table_name`";
    if ($condition) {
        $sql .= " WHERE $condition";
    }
    
    $result = mysqli_query($connection, $sql);
    if ($result) {
        return mysqli_fetch_array($result)['count'];
    }
    
    return 0;
}

?>