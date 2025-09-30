<?php
/**
 * Universal Automatic Table Helper
 * Automatically generates table configurations for any database table
 * Follows DRY principles - no more hardcoded configurations!
 */

// Include the automatic table detection system
require_once __DIR__ . '/auto_table_detector.php';

/**
 * Display any admin table using automatic configuration generation
 */
function display_admin_table_view($table_name, $sql) {
    // Generate configuration automatically based on table structure
    $table_config = getAutoTableConfig($table_name);
    $sql_query = $sql;
    
    // Use the reusable admin table view
    include __DIR__ . '/../views/shared/admin_table.php';
}

/**
 * Get table configuration - now fully automatic!
 * No more hardcoded configurations needed
 */
function getTableConfiguration($table_name) {
    // Simply delegate to the automatic system
    return getAutoTableConfig($table_name);
}

/**
 * Backward compatibility functions
 * These maintain the same interface while using the new automatic system
 */

?>