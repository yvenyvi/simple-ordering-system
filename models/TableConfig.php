<?php

/**
 * Centralized Table Configuration Class
 * Consolidates all table-specific configurations to follow DRY principle
 */
class TableConfig {
    
    /**
     * Table configuration mappings
     */
    private static $config = [
        'users' => [
            'id_field' => 'user_id',
            'name_fields' => ['first_name', 'last_name', 'email', 'username'],
            'image_directory' => 'users',
            'icon' => 'fas fa-users',
            'actions' => ['view', 'edit', 'delete'],
            'admin_page' => 'user_list.php',
            'title' => 'Users',
            'order_by' => 'created_at DESC',
            'excluded_fields' => ['address', 'city', 'state', 'password']
        ],
        'menu' => [
            'id_field' => 'menu_id',
            'name_fields' => ['name'],
            'image_directory' => 'products',
            'icon' => 'fas fa-utensils',
            'actions' => ['view', 'edit', 'toggle', 'delete'],
            'admin_page' => 'menu_list.php',
            'title' => 'Menu Items',
            'order_by' => 'created_at DESC'
        ],
        'events' => [
            'id_field' => 'event_id',
            'name_fields' => ['event_name', 'title', 'name'],
            'image_directory' => 'events',
            'icon' => 'fas fa-calendar-alt',
            'actions' => ['view', 'edit', 'delete'],
            'admin_page' => 'event_list.php',
            'title' => 'Events',
            'order_by' => 'event_date DESC',
            'excluded_fields' => ['description', 'requirements', 'contact_email', 'contact_phone', 'created_at', 'updated_at']
        ],
        'orders' => [
            'id_field' => 'order_id',
            'name_fields' => ['customer_name', 'customer_email'],
            'image_directory' => 'orders',
            'icon' => 'fas fa-shopping-cart',
            'actions' => ['view', 'delete'],
            'admin_page' => 'order_list.php',
            'title' => 'Orders',
            'order_by' => 'order_date DESC'
        ]
    ];

    /**
     * Field type detection patterns (centralized from scattered logic)
     */
    private static $field_patterns = [
        'image' => ['image', 'photo', 'picture', 'avatar'],
        'price' => ['price', 'cost', 'amount', 'fee', 'total', 'subtotal'],
        'email' => ['email', 'mail'],
        'phone' => ['phone', 'tel', 'mobile', 'contact'],
        'url' => ['url', 'link', 'website', 'site'],
        'boolean' => ['is_', 'active', 'available', 'enabled', 'visible'],
        'status' => ['status', 'state', 'condition'],
        'date' => ['date', 'created_at', 'updated_at', 'event_date'],
        'text' => ['description', 'content', 'notes', 'comment', 'details']
    ];

    /**
     * Status styling configurations
     */
    private static $status_styles = [
        'pending' => 'warning',
        'confirmed' => 'info',
        'preparing' => 'primary',
        'ready' => 'success',
        'delivered' => 'success',
        'cancelled' => 'danger',
        'paid' => 'success',
        'failed' => 'danger',
        'refunded' => 'info',
        'active' => 'success',
        'inactive' => 'secondary'
    ];

    /**
     * Get table configuration
     */
    public static function get($table, $key = null) {
        if (!isset(self::$config[$table])) {
            // Return default configuration for unknown tables
            return self::getDefaultConfig($table, $key);
        }

        if ($key === null) {
            return self::$config[$table];
        }

        return self::$config[$table][$key] ?? self::getDefaultValue($key);
    }

    /**
     * Get ID field name for table
     */
    public static function getIdField($table) {
        return self::get($table, 'id_field') ?? ($table . '_id');
    }

    /**
     * Get name fields for table (for display purposes)
     */
    public static function getNameFields($table) {
        return self::get($table, 'name_fields') ?? ['name'];
    }

    /**
     * Get image directory for table
     */
    public static function getImageDirectory($table) {
        return self::get($table, 'image_directory') ?? 'general';
    }

    /**
     * Get icon for table
     */
    public static function getIcon($table) {
        return self::get($table, 'icon') ?? 'fas fa-table';
    }

    /**
     * Get allowed actions for table
     */
    public static function getActions($table) {
        return self::get($table, 'actions') ?? ['delete'];
    }

    /**
     * Get admin page for table
     */
    public static function getAdminPage($table) {
        return self::get($table, 'admin_page') ?? ($table . '_list.php');
    }

    /**
     * Get display title for table
     */
    public static function getTitle($table) {
        return self::get($table, 'title') ?? ucfirst($table);
    }

    /**
     * Get default order by clause for table
     */
    public static function getOrderBy($table) {
        return self::get($table, 'order_by') ?? 'id DESC';
    }

    /**
     * Detect field type based on patterns
     */
    public static function detectFieldType($field_name, $field_info = []) {
        $field_lower = strtolower($field_name);
        
        // Check each pattern category
        foreach (self::$field_patterns as $type => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($field_lower, $pattern) !== false) {
                    // Additional validation for specific types
                    if ($type === 'boolean' && isset($field_info['type'])) {
                        if (strpos($field_info['type'], 'tinyint(1)') !== false) {
                            return 'boolean';
                        }
                    }
                    if ($type === 'date' && isset($field_info['type'])) {
                        if (strpos($field_info['type'], 'datetime') !== false || 
                            strpos($field_info['type'], 'timestamp') !== false) {
                            return 'datetime';
                        }
                        if (strpos($field_info['type'], 'date') !== false) {
                            return 'date';
                        }
                    }
                    return $type;
                }
            }
        }

        // Database type-based detection
        if (isset($field_info['type'])) {
            $db_type = strtolower($field_info['type']);
            
            if (strpos($db_type, 'text') !== false || strpos($db_type, 'longtext') !== false) {
                return 'text';
            }
            if (strpos($db_type, 'enum') !== false) {
                return 'status';
            }
            if (strpos($db_type, 'decimal') !== false || strpos($db_type, 'float') !== false) {
                return 'price';
            }
        }

        return 'string'; // Default type
    }

    /**
     * Get status style class
     */
    public static function getStatusStyle($status) {
        return self::$status_styles[strtolower($status)] ?? 'secondary';
    }

    /**
     * Get all status styles
     */
    public static function getStatusStyles() {
        return self::$status_styles;
    }

    /**
     * Check if table supports specific action
     */
    public static function hasAction($table, $action) {
        $actions = self::getActions($table);
        return in_array($action, $actions);
    }

    /**
     * Get default configuration for unknown tables
     */
    private static function getDefaultConfig($table, $key = null) {
        $default = [
            'id_field' => $table . '_id',
            'name_fields' => ['name'],
            'image_directory' => 'general',
            'icon' => 'fas fa-table',
            'actions' => ['delete'],
            'admin_page' => $table . '_list.php',
            'title' => ucfirst($table),
            'order_by' => 'id DESC'
        ];

        if ($key === null) {
            return $default;
        }

        return $default[$key] ?? null;
    }

    /**
     * Get default value for configuration key
     */
    private static function getDefaultValue($key) {
        $defaults = [
            'id_field' => 'id',
            'name_fields' => ['name'],
            'image_directory' => 'general',
            'icon' => 'fas fa-table',
            'actions' => ['delete'],
            'admin_page' => 'list.php',
            'title' => 'Items',
            'order_by' => 'id DESC'
        ];

        return $defaults[$key] ?? null;
    }

    /**
     * Add or update table configuration
     */
    public static function setConfig($table, $config) {
        self::$config[$table] = array_merge(
            self::getDefaultConfig($table),
            $config
        );
    }

    /**
     * Get all configured tables
     */
    public static function getAllTables() {
        return array_keys(self::$config);
    }

    /**
     * Get excluded fields for a table
     */
    public static function getExcludedFields($table) {
        return self::$config[$table]['excluded_fields'] ?? [];
    }
}

?>