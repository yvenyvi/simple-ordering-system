<?php
require_once "../models/db_Model.php";

// === BASIC COUNTS ===
// Count menu items
$menuResult = mysqli_query($connection, "SELECT COUNT(*) as count FROM menu");
$menuCount = mysqli_fetch_array($menuResult)['count'];

// Count users
$userResult = mysqli_query($connection, "SELECT COUNT(*) as count FROM users");
$userCount = mysqli_fetch_array($userResult)['count'];

// Count available menu items
$availableResult = mysqli_query($connection, "SELECT COUNT(*) as count FROM menu WHERE is_available = 1");
$availableCount = mysqli_fetch_array($availableResult)['count'];

// Count events
$eventResult = mysqli_query($connection, "SELECT COUNT(*) as count FROM events WHERE is_active = 1");
$eventCount = mysqli_fetch_array($eventResult)['count'];

// === SALES & REVENUE STATS ===
// Today's sales
$todaySalesQuery = "SELECT 
                        COUNT(*) as orders_count,
                        COALESCE(SUM(total_amount), 0) as total_revenue
                    FROM orders 
                    WHERE DATE(order_date) = CURDATE() 
                    AND status != 'cancelled'";
$todaySalesResult = mysqli_query($connection, $todaySalesQuery);
$todayStats = mysqli_fetch_array($todaySalesResult);

// This month's revenue
$monthlyRevenueQuery = "SELECT COALESCE(SUM(total_amount), 0) as monthly_revenue
                        FROM orders 
                        WHERE MONTH(order_date) = MONTH(CURDATE()) 
                        AND YEAR(order_date) = YEAR(CURDATE())
                        AND status != 'cancelled'";
$monthlyRevenueResult = mysqli_query($connection, $monthlyRevenueQuery);
$monthlyRevenue = mysqli_fetch_array($monthlyRevenueResult)['monthly_revenue'];

// This week's revenue
$weeklyRevenueQuery = "SELECT COALESCE(SUM(total_amount), 0) as weekly_revenue
                       FROM orders 
                       WHERE YEARWEEK(order_date) = YEARWEEK(CURDATE())
                       AND status != 'cancelled'";
$weeklyRevenueResult = mysqli_query($connection, $weeklyRevenueQuery);
$weeklyRevenue = mysqli_fetch_array($weeklyRevenueResult)['weekly_revenue'];

// Total orders and revenue (all time)
$totalStatsQuery = "SELECT 
                        COUNT(*) as total_orders,
                        COALESCE(SUM(total_amount), 0) as total_revenue
                    FROM orders 
                    WHERE status != 'cancelled'";
$totalStatsResult = mysqli_query($connection, $totalStatsQuery);
$totalStats = mysqli_fetch_array($totalStatsResult);

// Order status breakdown
$orderStatusQuery = "SELECT 
                        status,
                        COUNT(*) as count
                     FROM orders 
                     WHERE DATE(order_date) = CURDATE()
                     GROUP BY status";
$orderStatusResult = mysqli_query($connection, $orderStatusQuery);
$orderStatus = [];
while ($row = mysqli_fetch_array($orderStatusResult)) {
    $orderStatus[$row['status']] = $row['count'];
}

// === TOP PERFORMING ITEMS ===
// Most sold menu item (by quantity)
$topItemByQuantityQuery = "SELECT 
                              m.name,
                              SUM(oi.quantity) as total_sold
                          FROM order_items oi
                          JOIN menu m ON oi.menu_id = m.menu_id
                          JOIN orders o ON oi.order_id = o.order_id
                          WHERE o.status != 'cancelled'
                          GROUP BY oi.menu_id, m.name
                          ORDER BY total_sold DESC
                          LIMIT 1";
$topItemResult = mysqli_query($connection, $topItemByQuantityQuery);
$topItem = mysqli_fetch_array($topItemResult);

// Most revenue-generating item
$topRevenueItemQuery = "SELECT 
                           m.name,
                           SUM(oi.total_price) as total_revenue
                       FROM order_items oi
                       JOIN menu m ON oi.menu_id = m.menu_id
                       JOIN orders o ON oi.order_id = o.order_id
                       WHERE o.status != 'cancelled'
                       GROUP BY oi.menu_id, m.name
                       ORDER BY total_revenue DESC
                       LIMIT 1";
$topRevenueItemResult = mysqli_query($connection, $topRevenueItemQuery);
$topRevenueItem = mysqli_fetch_array($topRevenueItemResult);

// === RECENT ACTIVITY ===
// Recent orders (last 5)
$recentOrdersQuery = "SELECT 
                         o.order_id,
                         o.total_amount,
                         o.status,
                         o.order_date,
                         CONCAT(u.first_name, ' ', u.last_name) as customer_name
                     FROM orders o
                     LEFT JOIN users u ON o.user_id = u.user_id
                     ORDER BY o.order_date DESC
                     LIMIT 5";
$recentOrdersResult = mysqli_query($connection, $recentOrdersQuery);

// === UPCOMING EVENTS ===
// Next 3 upcoming events
$upcomingEventsQuery = "SELECT 
                           event_name,
                           event_date,
                           event_time,
                           capacity,
                           price
                       FROM events 
                       WHERE is_active = 1 
                       AND event_date >= CURDATE()
                       ORDER BY event_date ASC, event_time ASC
                       LIMIT 3";
$upcomingEventsResult = mysqli_query($connection, $upcomingEventsQuery);

// === PAYMENT STATUS BREAKDOWN ===
$paymentStatusQuery = "SELECT 
                          payment_status,
                          COUNT(*) as count,
                          SUM(total_amount) as amount
                      FROM orders 
                      WHERE DATE(order_date) = CURDATE()
                      GROUP BY payment_status";
$paymentStatusResult = mysqli_query($connection, $paymentStatusQuery);
$paymentStatus = [];
while ($row = mysqli_fetch_array($paymentStatusResult)) {
    $paymentStatus[$row['payment_status']] = [
        'count' => $row['count'],
        'amount' => $row['amount']
    ];
}
?>