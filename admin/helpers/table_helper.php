<?php
/**
 * Admin Table Helper Functions
 * Replaces table_functions.php with clean, configurable functions
 */

/**
 * Display any admin table using the reusable view
 */
function display_admin_table_view($table_type, $sql) {
    $table_config = getTableConfiguration($table_type);
    $sql_query = $sql;
    
    include __DIR__ . '/../views/shared/admin_table.php';
}

/**
 * Get table configuration for different entity types
 */
function getTableConfiguration($table_type) {
    $configs = array(
        'menu' => array(
            'title' => 'Menu Items',
            'icon' => 'fas fa-utensils',
            'empty_message' => 'Start by adding your first menu item using the "Add New Item" button above.',
            'image_directory' => 'products',
            'id_field' => 'menu_id',
            'name_field' => 'name',
            'page' => 'menu_list.php',
            'columns' => array(
                'id' => 'ID',
                'image' => 'Image', 
                'name' => 'Name',
                'category' => 'Category',
                'price' => 'Price',
                'prep_time' => 'Prep Time',
                'status' => 'Status',
                'date' => 'Created',
                'actions' => 'Actions'
            )
        ),
        'users' => array(
            'title' => 'Registered Users',
            'icon' => 'fas fa-users',
            'empty_message' => 'No registered users found in the system.',
            'image_directory' => 'users',
            'id_field' => 'user_id',
            'name_field' => array('first_name', 'last_name'),
            'page' => 'user_list.php',
            'columns' => array(
                'id' => 'ID',
                'name' => 'Name',
                'email' => 'Email',
                'phone' => 'Phone',
                'location' => 'Location',
                'status' => 'Status',
                'date' => 'Joined',
                'actions' => 'Actions'
            )
        ),
        'events' => array(
            'title' => 'Upcoming Events',
            'icon' => 'fas fa-calendar-alt',
            'empty_message' => 'Start by creating your first event using the "Add New Event" button above.',
            'image_directory' => 'events',
            'id_field' => 'event_id',
            'name_field' => 'event_name',
            'page' => 'event_list.php',
            'columns' => array(
                'id' => 'ID',
                'image' => 'Image',
                'name' => 'Event',
                'type' => 'Type',
                'event_date' => 'Date & Time',
                'location' => 'Location',
                'capacity' => 'Capacity',
                'price' => 'Price',
                'status' => 'Status',
                'actions' => 'Actions'
            )
        )
    );
    
    return $configs[$table_type] ?? $configs['menu']; // Default fallback
}

/**
 * Backward compatibility functions - same names as table_functions.php
 */

?>