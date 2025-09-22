<?php
/**
 * Reusable Admin Table View
 * Replaces all table_functions.php functionality with clean, configurable MVC approach
 */

if (!isset($table_config) || !isset($sql_query)) {
    die("Table configuration and SQL query are required");
}

global $connection;
$result = mysqli_query($connection, $sql_query);
$rowCount = mysqli_num_rows($result);
?>

<?php if ($rowCount > 0): ?>
    <div class="admin-table-container">
        <div class="admin-table-header">
            <h3><?php echo $table_config['title']; ?> (<?php echo $rowCount; ?>)</h3>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <?php foreach ($table_config['columns'] as $header): ?>
                            <th><?php echo $header; ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_array($result)): ?>
                        <tr>
                            <?php foreach ($table_config['columns'] as $column => $header): ?>
                                <td><?php echo renderTableCell($row, $column, $table_config); ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="admin-table-container">
        <div class="empty-state">
            <i class="<?php echo $table_config['icon']; ?>"></i>
            <h4>No <?php echo $table_config['title']; ?> Found</h4>
            <p><?php echo $table_config['empty_message']; ?></p>
        </div>
    </div>
<?php endif; ?>

<?php
/**
 * Render individual table cell based on column type
 */
function renderTableCell($row, $column, $config) {
    switch ($column) {
        case 'id':
            return $row[$config['id_field']];
            
        case 'image':
            return renderImageCell($row, $config);
            
        case 'name':
            return renderNameCell($row, $config);
            
        case 'category':
            return '<span class="category-badge category-' . strtolower($row['category']) . '">' . ucfirst($row['category']) . '</span>';
            
        case 'type':
            return '<span class="event-badge event-' . strtolower($row['event_type']) . '">' . ucfirst($row['event_type']) . '</span>';
            
        case 'price':
            return '<span class="price-cell">$' . number_format($row['price'], 2) . '</span>';
            
        case 'prep_time':
            return $row['preparation_time'] . ' min';
            
        case 'capacity':
            return $row['capacity'] . ' people';
            
        case 'status':
            return renderStatusCell($row);
            
        case 'date':
            return '<span class="date-cell">' . date("M d, Y", strtotime($row['created_at'])) . '</span>';
            
        case 'event_date':
            $event_date = date("M d, Y", strtotime($row['event_date']));
            $event_time = date("g:i A", strtotime($row['event_time']));
            return '<strong>' . $event_date . '</strong><br><small class="text-muted">' . $event_time . '</small>';
            
        case 'email':
            return '<a href="mailto:' . htmlspecialchars($row['email']) . '">' . htmlspecialchars($row['email']) . '</a>';
            
        case 'phone':
            if (!empty($row['phone'])) {
                return '<a href="tel:' . htmlspecialchars($row['phone']) . '">' . htmlspecialchars($row['phone']) . '</a>';
            }
            return '<span class="text-muted">Not provided</span>';
            
        case 'location':
            if (isset($row['city']) && isset($row['state'])) {
                $location = trim($row['city'] . ', ' . $row['state'], ', ');
                return !empty($location) ? htmlspecialchars($location) : '<span class="text-muted">Not provided</span>';
            }
            return htmlspecialchars($row['location'] ?? '');
            
        case 'actions':
            return renderActionsCell($row, $config);
            
        default:
            return htmlspecialchars($row[$column] ?? '');
    }
}

function renderImageCell($row, $config) {
    $placeholder = '../assets/images/' . $config['image_directory'] . '/placeholder.jpg';
    $image_path = $placeholder;
    
    if (isset($row['image_url']) && !empty($row['image_url'])) {
        if (strpos($row['image_url'], 'assets/') === 0) {
            $image_path = '../' . $row['image_url'];
        } else {
            $image_path = $row['image_url'];
        }
        
        if (!file_exists($image_path)) {
            $image_path = $placeholder;
        }
    }
    
    $name = $row[$config['name_field']];
    return '<img src="' . $image_path . '" alt="' . htmlspecialchars($name) . '" class="table-image">';
}

function renderNameCell($row, $config) {
    $name_field = $config['name_field'];
    $html = '';
    
    if (is_array($name_field)) {
        // Handle composite names like first_name + last_name
        $html = '<strong>' . htmlspecialchars($row[$name_field[0]] . ' ' . $row[$name_field[1]]) . '</strong>';
    } else {
        $html = '<strong>' . htmlspecialchars($row[$name_field]) . '</strong>';
        
        // Add description if available
        if (isset($row['description']) && !empty($row['description'])) {
            $html .= '<br><small class="text-muted">' . htmlspecialchars(substr($row['description'], 0, 50)) . '...</small>';
        }
    }
    
    return $html;
}

function renderStatusCell($row) {
    $status_field = isset($row['is_available']) ? 'is_available' : 'is_active';
    $status_class = $row[$status_field] == 1 ? 'status-active' : 'status-inactive';
    $status_text = $row[$status_field] == 1 
        ? (isset($row['is_available']) ? 'Available' : 'Active')
        : (isset($row['is_available']) ? 'Unavailable' : 'Inactive');
    
    return '<span class="status-badge ' . $status_class . '">' . $status_text . '</span>';
}

function renderActionsCell($row, $config) {
    $id = $row[$config['id_field']];
    $name_field = $config['name_field'];
    $name = is_array($name_field) 
        ? $row[$name_field[0]] . ' ' . $row[$name_field[1]]
        : $row[$name_field];
    
    $html = '<div class="action-buttons">';
    $html .= '<a href="edit.php?editid=' . $id . '" class="btn-action btn-edit"><i class="fas fa-edit"></i> Edit</a>';
    $html .= '<a href="#" class="btn-action btn-delete" onclick="confirmDelete(' . $id . ', \'' . addslashes($name) . '\', \'' . $config['page'] . '\')"><i class="fas fa-trash"></i> Delete</a>';
    $html .= '</div>';
    
    return $html;
}
?>