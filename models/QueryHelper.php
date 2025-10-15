<?php

/**
 * Database Query Helper Class
 * Eliminates repeated query patterns and provides reusable database operations
 */
class QueryHelper {
    
    private static $connection;

    /**
     * Initialize with database connection
     */
    public static function init($connection) {
        self::$connection = $connection;
    }

    /**
     * Get total count for table
     */
    public static function getCount($table, $where_conditions = []) {
        $sql = "SELECT COUNT(*) as count FROM `$table`";
        
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . self::buildWhereClause($where_conditions);
        }
        
        $result = mysqli_query(self::$connection, $sql);
        if (!$result) {
            return 0;
        }
        
        $row = mysqli_fetch_array($result);
        return (int)$row['count'];
    }

    /**
     * Get count with custom condition
     */
    public static function getCountWhere($table, $field, $value, $operator = '=') {
        $escaped_value = mysqli_real_escape_string(self::$connection, $value);
        $sql = "SELECT COUNT(*) as count FROM `$table` WHERE `$field` $operator '$escaped_value'";
        
        $result = mysqli_query(self::$connection, $sql);
        if (!$result) {
            return 0;
        }
        
        $row = mysqli_fetch_array($result);
        return (int)$row['count'];
    }

    /**
     * Get recent items count (last N days)
     */
    public static function getRecentCount($table, $date_field = 'created_at', $days = 30) {
        $sql = "SELECT COUNT(*) as count FROM `$table` 
                WHERE `$date_field` >= DATE_SUB(NOW(), INTERVAL $days DAY)";
        
        $result = mysqli_query(self::$connection, $sql);
        if (!$result) {
            return 0;
        }
        
        $row = mysqli_fetch_array($result);
        return (int)$row['count'];
    }

    /**
     * Get today's count
     */
    public static function getTodayCount($table, $date_field = 'created_at') {
        $sql = "SELECT COUNT(*) as count FROM `$table` 
                WHERE DATE(`$date_field`) = CURDATE()";
        
        $result = mysqli_query(self::$connection, $sql);
        if (!$result) {
            return 0;
        }
        
        $row = mysqli_fetch_array($result);
        return (int)$row['count'];
    }

    /**
     * Get sum for numeric field
     */
    public static function getSum($table, $field, $where_conditions = []) {
        $sql = "SELECT SUM(`$field`) as total FROM `$table`";
        
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . self::buildWhereClause($where_conditions);
        }
        
        $result = mysqli_query(self::$connection, $sql);
        if (!$result) {
            return 0;
        }
        
        $row = mysqli_fetch_array($result);
        return (float)($row['total'] ?? 0);
    }

    /**
     * Get average for numeric field
     */
    public static function getAverage($table, $field, $where_conditions = []) {
        $sql = "SELECT AVG(`$field`) as average FROM `$table`";
        
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . self::buildWhereClause($where_conditions);
        }
        
        $result = mysqli_query(self::$connection, $sql);
        if (!$result) {
            return 0;
        }
        
        $row = mysqli_fetch_array($result);
        return (float)($row['average'] ?? 0);
    }

    /**
     * Get distinct count (e.g., unique categories)
     */
    public static function getDistinctCount($table, $field, $where_conditions = []) {
        $sql = "SELECT COUNT(DISTINCT `$field`) as count FROM `$table`";
        
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . self::buildWhereClause($where_conditions);
        }
        
        $result = mysqli_query(self::$connection, $sql);
        if (!$result) {
            return 0;
        }
        
        $row = mysqli_fetch_array($result);
        return (int)$row['count'];
    }

    /**
     * Get count and sum together (for orders: count + revenue)
     */
    public static function getCountAndSum($table, $sum_field, $where_conditions = []) {
        $sql = "SELECT COUNT(*) as count, SUM(`$sum_field`) as total FROM `$table`";
        
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . self::buildWhereClause($where_conditions);
        }
        
        $result = mysqli_query(self::$connection, $sql);
        if (!$result) {
            return ['count' => 0, 'total' => 0];
        }
        
        $row = mysqli_fetch_array($result);
        return [
            'count' => (int)$row['count'],
            'total' => (float)($row['total'] ?? 0)
        ];
    }

    /**
     * Get today's count and sum
     */
    public static function getTodayCountAndSum($table, $sum_field, $date_field = 'created_at') {
        return self::getCountAndSum($table, $sum_field, [
            $date_field => ['DATE(?) = CURDATE()', 'NOW()']
        ]);
    }

    /**
     * Get upcoming events count (future date)
     */
    public static function getUpcomingCount($table, $date_field = 'event_date') {
        $sql = "SELECT COUNT(*) as count FROM `$table` 
                WHERE `$date_field` >= CURDATE()";
        
        $result = mysqli_query(self::$connection, $sql);
        if (!$result) {
            return 0;
        }
        
        $row = mysqli_fetch_array($result);
        return (int)$row['count'];
    }

    /**
     * Get count by status
     */
    public static function getCountByStatus($table, $status_field, $status_values) {
        if (is_string($status_values)) {
            $status_values = [$status_values];
        }
        
        $escaped_values = array_map(function($value) {
            return "'" . mysqli_real_escape_string(self::$connection, $value) . "'";
        }, $status_values);
        
        $values_str = implode(', ', $escaped_values);
        $sql = "SELECT COUNT(*) as count FROM `$table` 
                WHERE `$status_field` IN ($values_str)";
        
        $result = mysqli_query(self::$connection, $sql);
        if (!$result) {
            return 0;
        }
        
        $row = mysqli_fetch_array($result);
        return (int)$row['count'];
    }

    /**
     * Execute custom query and return single value
     */
    public static function getSingleValue($sql, $default = null) {
        $result = mysqli_query(self::$connection, $sql);
        if (!$result) {
            return $default;
        }
        
        $row = mysqli_fetch_array($result);
        return $row[0] ?? $default;
    }

    /**
     * Execute custom query and return associative array
     */
    public static function getRow($sql) {
        $result = mysqli_query(self::$connection, $sql);
        if (!$result) {
            return [];
        }
        
        return mysqli_fetch_array($result) ?: [];
    }

    /**
     * Execute custom query and return all rows
     */
    public static function getRows($sql) {
        $result = mysqli_query(self::$connection, $sql);
        if (!$result) {
            return [];
        }
        
        $rows = [];
        while ($row = mysqli_fetch_array($result)) {
            $rows[] = $row;
        }
        
        return $rows;
    }

    /**
     * Check if record exists
     */
    public static function exists($table, $field, $value) {
        $escaped_value = mysqli_real_escape_string(self::$connection, $value);
        $sql = "SELECT 1 FROM `$table` WHERE `$field` = '$escaped_value' LIMIT 1";
        
        $result = mysqli_query(self::$connection, $sql);
        return $result && mysqli_num_rows($result) > 0;
    }

    /**
     * Get table statistics in one query
     */
    public static function getTableStats($table) {
        $columns = getTableColumns($table);
        $stats = [];
        
        // Basic count
        $stats['total'] = self::getCount($table);
        
        // Check for common fields and get stats
        if (isset($columns['is_available'])) {
            $stats['available'] = self::getCountWhere($table, 'is_available', '1');
            $stats['unavailable'] = $stats['total'] - $stats['available'];
        }
        
        if (isset($columns['status'])) {
            $stats['active'] = self::getCountWhere($table, 'status', 'active');
        }
        
        if (isset($columns['created_at'])) {
            $stats['recent'] = self::getRecentCount($table, 'created_at', 30);
            $stats['today'] = self::getTodayCount($table, 'created_at');
        }
        
        if (isset($columns['event_date'])) {
            $stats['upcoming'] = self::getUpcomingCount($table, 'event_date');
        }
        
        // Table-specific stats
        if ($table === 'menu' && isset($columns['category'])) {
            $stats['categories'] = self::getDistinctCount($table, 'category');
        }
        
        if ($table === 'menu' && isset($columns['price'])) {
            $stats['avg_price'] = self::getAverage($table, 'price', ['is_available' => '1']);
        }
        
        if ($table === 'orders' && isset($columns['total_amount'])) {
            $revenue_data = self::getCountAndSum($table, 'total_amount', ['status' => "!= 'cancelled'"]);
            $stats['revenue'] = $revenue_data['total'];
            
            $today_data = self::getTodayCountAndSum($table, 'total_amount', 'order_date');
            $stats['today_revenue'] = $today_data['total'];
            
            $stats['pending'] = self::getCountByStatus($table, 'status', ['pending', 'confirmed', 'preparing', 'ready']);
            $stats['completed'] = self::getCountWhere($table, 'status', 'delivered');
        }
        
        return $stats;
    }

    /**
     * Build WHERE clause from conditions array
     */
    private static function buildWhereClause($conditions) {
        $clauses = [];
        
        foreach ($conditions as $field => $condition) {
            if (is_array($condition)) {
                // Custom condition with parameters
                $clauses[] = str_replace('?', $condition[1], $condition[0]);
            } else {
                // Simple field = value
                $escaped_value = mysqli_real_escape_string(self::$connection, $condition);
                $clauses[] = "`$field` = '$escaped_value'";
            }
        }
        
        return implode(' AND ', $clauses);
    }

    /**
     * Get connection for advanced operations
     */
    public static function getConnection() {
        return self::$connection;
    }
}

?>