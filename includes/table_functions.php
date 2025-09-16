<?php
/**
 * Admin Table Display Functions
 * Reusable functions for displaying data in properly formatted HTML tables
 */

/**
 * Display data in a formatted table for menu items
 */
function display_menu_table($sql) {
    global $connection;
    $result = mysqli_query($connection, $sql);
    $rowCount = mysqli_num_rows($result);
    
    if ($rowCount > 0) {
        echo '<div class="admin-table-container">';
        echo '<div class="admin-table-header">';
        echo '<h3>Menu Items (' . $rowCount . ')</h3>';
        echo '</div>';
        echo '<div class="table-responsive">';
        echo '<table class="admin-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Image</th>';
        echo '<th>Name</th>';
        echo '<th>Category</th>';
        echo '<th>Price</th>';
        echo '<th>Prep Time</th>';
        echo '<th>Status</th>';
        echo '<th>Created</th>';
        echo '<th>Actions</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        while($row = mysqli_fetch_array($result)) {
            // Handle image path properly - add ../ if it's a relative path from assets/
            $image_path = '../assets/images/products/placeholder.jpg'; // Default fallback
            
            if (isset($row['image_url']) && !empty($row['image_url'])) {
                // If image_url starts with 'assets/', add '../' to make it work from admin directory
                if (strpos($row['image_url'], 'assets/') === 0) {
                    $image_path = '../' . $row['image_url'];
                } else {
                    $image_path = $row['image_url'];
                }
                
                // Check if file actually exists, if not use placeholder
                if (!file_exists($image_path)) {
                    $image_path = '../assets/images/products/placeholder.jpg';
                }
            }
            
            $status_class = $row['is_available'] == 1 ? 'status-active' : 'status-inactive';
            $status_text = $row['is_available'] == 1 ? 'Available' : 'Unavailable';
            
            echo '<tr>';
            echo '<td>' . $row['menu_id'] . '</td>';
            echo '<td><img src="' . $image_path . '" alt="' . htmlspecialchars($row['name']) . '" class="table-image"></td>';
            echo '<td><strong>' . htmlspecialchars($row['name']) . '</strong>';
            if (!empty($row['description'])) {
                echo '<br><small class="text-muted">' . htmlspecialchars(substr($row['description'], 0, 50)) . '...</small>';
            }
            echo '</td>';
            echo '<td><span class="category-badge category-' . strtolower($row['category']) . '">' . ucfirst($row['category']) . '</span></td>';
            echo '<td class="price-cell">$' . number_format($row['price'], 2) . '</td>';
            echo '<td>' . $row['preparation_time'] . ' min</td>';
            echo '<td><span class="status-badge ' . $status_class . '">' . $status_text . '</span></td>';
            echo '<td class="date-cell">' . date("M d, Y", strtotime($row['created_at'])) . '</td>';
            echo '<td>';
            echo '<div class="action-buttons">';
            echo '<a href="edit.php?editid=' . $row['menu_id'] . '" class="btn-action btn-edit"><i class="fas fa-edit"></i> Edit</a>';
            echo '<a href="#" class="btn-action btn-delete" onclick="confirmDelete(' . $row['menu_id'] . ', \'' . addslashes($row['name']) . '\', \'menu_list.php\')"><i class="fas fa-trash"></i> Delete</a>';
            echo '</div>';
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<div class="admin-table-container">';
        echo '<div class="empty-state">';
        echo '<i class="fas fa-utensils"></i>';
        echo '<h4>No Menu Items Found</h4>';
        echo '<p>Start by adding your first menu item using the "Add New Item" button above.</p>';
        echo '</div>';
        echo '</div>';
    }
}

/**
 * Display data in a formatted table for users
 */
function display_users_table($sql) {
    global $connection;
    $result = mysqli_query($connection, $sql);
    $rowCount = mysqli_num_rows($result);
    
    if ($rowCount > 0) {
        echo '<div class="admin-table-container">';
        echo '<div class="admin-table-header">';
        echo '<h3>Registered Users (' . $rowCount . ')</h3>';
        echo '</div>';
        echo '<div class="table-responsive">';
        echo '<table class="admin-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Name</th>';
        echo '<th>Email</th>';
        echo '<th>Phone</th>';
        echo '<th>Location</th>';
        echo '<th>Status</th>';
        echo '<th>Joined</th>';
        echo '<th>Actions</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        while($row = mysqli_fetch_array($result)) {
            $status_class = $row['is_active'] == 1 ? 'status-active' : 'status-inactive';
            $status_text = $row['is_active'] == 1 ? 'Active' : 'Inactive';
            $location = trim($row['city'] . ', ' . $row['state'], ', ');
            
            echo '<tr>';
            echo '<td>' . $row['user_id'] . '</td>';
            echo '<td><strong>' . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . '</strong></td>';
            echo '<td><a href="mailto:' . htmlspecialchars($row['email']) . '">' . htmlspecialchars($row['email']) . '</a></td>';
            echo '<td>';
            if (!empty($row['phone'])) {
                echo '<a href="tel:' . htmlspecialchars($row['phone']) . '">' . htmlspecialchars($row['phone']) . '</a>';
            } else {
                echo '<span class="text-muted">Not provided</span>';
            }
            echo '</td>';
            echo '<td>' . (!empty($location) ? htmlspecialchars($location) : '<span class="text-muted">Not provided</span>') . '</td>';
            echo '<td><span class="status-badge ' . $status_class . '">' . $status_text . '</span></td>';
            echo '<td class="date-cell">' . date("M d, Y", strtotime($row['created_at'])) . '</td>';
            echo '<td>';
            echo '<div class="action-buttons">';
            echo '<a href="edit.php?editid=' . $row['user_id'] . '" class="btn-action btn-edit"><i class="fas fa-edit"></i> Edit</a>';
            echo '<a href="#" class="btn-action btn-delete" onclick="confirmDelete(' . $row['user_id'] . ', \'' . addslashes($row['first_name'] . ' ' . $row['last_name']) . '\', \'user_list.php\')"><i class="fas fa-trash"></i> Delete</a>';
            echo '</div>';
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<div class="admin-table-container">';
        echo '<div class="empty-state">';
        echo '<i class="fas fa-users"></i>';
        echo '<h4>No Users Found</h4>';
        echo '<p>No registered users found in the system.</p>';
        echo '</div>';
        echo '</div>';
    }
}

/**
 * Display data in a formatted table for events
 */
function display_events_table($sql) {
    global $connection;
    $result = mysqli_query($connection, $sql);
    $rowCount = mysqli_num_rows($result);
    
    if ($rowCount > 0) {
        echo '<div class="admin-table-container">';
        echo '<div class="admin-table-header">';
        echo '<h3>Upcoming Events (' . $rowCount . ')</h3>';
        echo '</div>';
        echo '<div class="table-responsive">';
        echo '<table class="admin-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Image</th>';
        echo '<th>Event</th>';
        echo '<th>Type</th>';
        echo '<th>Date & Time</th>';
        echo '<th>Location</th>';
        echo '<th>Capacity</th>';
        echo '<th>Price</th>';
        echo '<th>Status</th>';
        echo '<th>Actions</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        while($row = mysqli_fetch_array($result)) {
            // Handle image path properly - add ../ if it's a relative path from assets/
            $image_path = '../assets/images/events/placeholder.jpg'; // Default fallback
            
            if (isset($row['image_url']) && !empty($row['image_url'])) {
                // If image_url starts with 'assets/', add '../' to make it work from admin directory
                if (strpos($row['image_url'], 'assets/') === 0) {
                    $image_path = '../' . $row['image_url'];
                } else {
                    $image_path = $row['image_url'];
                }
                
                // Check if file actually exists, if not use placeholder
                if (!file_exists($image_path)) {
                    $image_path = '../assets/images/events/placeholder.jpg';
                }
            }
            
            $status_class = $row['is_active'] == 1 ? 'status-active' : 'status-inactive';
            $status_text = $row['is_active'] == 1 ? 'Active' : 'Inactive';
            $event_date = date("M d, Y", strtotime($row['event_date']));
            $event_time = date("g:i A", strtotime($row['event_time']));
            
            echo '<tr>';
            echo '<td>' . $row['event_id'] . '</td>';
            echo '<td><img src="' . $image_path . '" alt="' . htmlspecialchars($row['event_name']) . '" class="table-image"></td>';
            echo '<td><strong>' . htmlspecialchars($row['event_name']) . '</strong>';
            if (!empty($row['description'])) {
                echo '<br><small class="text-muted">' . htmlspecialchars(substr($row['description'], 0, 50)) . '...</small>';
            }
            echo '</td>';
            echo '<td><span class="event-badge event-' . strtolower($row['event_type']) . '">' . ucfirst($row['event_type']) . '</span></td>';
            echo '<td>';
            echo '<strong>' . $event_date . '</strong><br>';
            echo '<small class="text-muted">' . $event_time . '</small>';
            echo '</td>';
            echo '<td>' . htmlspecialchars($row['location']) . '</td>';
            echo '<td>' . $row['capacity'] . ' people</td>';
            echo '<td class="price-cell">$' . number_format($row['price'], 2) . '</td>';
            echo '<td><span class="status-badge ' . $status_class . '">' . $status_text . '</span></td>';
            echo '<td>';
            echo '<div class="action-buttons">';
            echo '<a href="edit.php?editid=' . $row['event_id'] . '" class="btn-action btn-edit"><i class="fas fa-edit"></i> Edit</a>';
            echo '<a href="#" class="btn-action btn-delete" onclick="confirmDelete(' . $row['event_id'] . ', \'' . addslashes($row['event_name']) . '\', \'event_list.php\')"><i class="fas fa-trash"></i> Delete</a>';
            echo '</div>';
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<div class="admin-table-container">';
        echo '<div class="empty-state">';
        echo '<i class="fas fa-calendar-alt"></i>';
        echo '<h4>No Events Found</h4>';
        echo '<p>Start by creating your first event using the "Add New Event" button above.</p>';
        echo '</div>';
        echo '</div>';
    }
}
?>
