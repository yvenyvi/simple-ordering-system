<?php
$page_title = "Admin Dashboard";
include 'includes/header.php';

global $connection;

include 'controller/index.php';
?>

<body>
    <div class="admin-container">
        <!-- Admin Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Dashboard Section -->
            <section id="dashboard" class="admin-section active">
                <div class="section-header">
                    <h1>Dashboard</h1>
                    <p class="text-muted">Welcome to your restaurant management dashboard</p>
                </div>

                <!-- Revenue Stats -->
                <div class="stats-section">
                    <h3 class="section-title"><i class="fas fa-dollar-sign"></i> Revenue Overview</h3>
                    <div class="stats-grid revenue-stats">
                        <div class="stat-card revenue-card">
                            <i class="fas fa-calendar-day"></i>
                            <div class="stat-info">
                                <h3>$<?php echo number_format($todayStats['total_revenue'], 2); ?></h3>
                                <p>Today's Revenue</p>
                                <small><?php echo $todayStats['orders_count']; ?> orders</small>
                            </div>
                        </div>
                        <div class="stat-card revenue-card">
                            <i class="fas fa-calendar-week"></i>
                            <div class="stat-info">
                                <h3>$<?php echo number_format($weeklyRevenue, 2); ?></h3>
                                <p>This Week</p>
                            </div>
                        </div>
                        <div class="stat-card revenue-card">
                            <i class="fas fa-calendar-alt"></i>
                            <div class="stat-info">
                                <h3>$<?php echo number_format($monthlyRevenue, 2); ?></h3>
                                <p>This Month</p>
                            </div>
                        </div>
                        <div class="stat-card revenue-card">
                            <i class="fas fa-chart-line"></i>
                            <div class="stat-info">
                                <h3>$<?php echo number_format($totalStats['total_revenue'], 2); ?></h3>
                                <p>Total Revenue</p>
                                <small><?php echo $totalStats['total_orders']; ?> total orders</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="dashboard-row">
                    <!-- Recent Orders -->
                    <div class="dashboard-widget">
                        <h3 class="widget-title"><i class="fas fa-clock"></i> Recent Orders</h3>
                        <div class="recent-orders">
                            <?php if (mysqli_num_rows($recentOrdersResult) > 0): ?>
                                <?php while ($order = mysqli_fetch_array($recentOrdersResult)): ?>
                                    <div class="recent-order-item">
                                        <div class="order-info">
                                            <strong>Order #<?php echo $order['order_id']; ?></strong>
                                            <span class="customer-name"><?php echo $order['customer_name']; ?></span>
                                        </div>
                                        <div class="order-details">
                                            <span class="order-amount">$<?php echo number_format($order['total_amount'], 2); ?></span>
                                            <span class="order-status status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                                        </div>
                                        <div class="order-time"><?php echo date('M d, g:i A', strtotime($order['order_date'])); ?></div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="no-data">No recent orders</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Upcoming Events -->
                    <div class="dashboard-widget">
                        <h3 class="widget-title"><i class="fas fa-calendar-alt"></i> Upcoming Events</h3>
                        <div class="upcoming-events">
                            <?php if (mysqli_num_rows($upcomingEventsResult) > 0): ?>
                                <?php while ($event = mysqli_fetch_array($upcomingEventsResult)): ?>
                                    <div class="upcoming-event-item">
                                        <div class="event-date">
                                            <span class="month"><?php echo date('M', strtotime($event['event_date'])); ?></span>
                                            <span class="day"><?php echo date('d', strtotime($event['event_date'])); ?></span>
                                        </div>
                                        <div class="event-info">
                                            <strong><?php echo $event['event_name']; ?></strong>
                                            <div class="event-details">
                                                <span class="event-time"><?php echo date('g:i A', strtotime($event['event_time'])); ?></span>
                                                <span class="event-price">$<?php echo number_format($event['price'], 2); ?></span>
                                            </div>
                                            <small class="event-capacity"><?php echo $event['capacity']; ?> spots</small>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="no-data">No upcoming events</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Business Overview -->
                <div class="stats-section">
                    <h3 class="section-title"><i class="fas fa-chart-bar"></i> Business Overview</h3>
                    <div class="stats-grid business-stats">
                        <div class="stat-card">
                            <i class="fas fa-utensils"></i>
                            <div class="stat-info">
                                <h3><?php echo $menuCount; ?></h3>
                                <p>Menu Items</p>
                                <small><?php echo $availableCount; ?> available</small>
                            </div>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-users"></i>
                            <div class="stat-info">
                                <h3><?php echo $userCount; ?></h3>
                                <p>Registered Users</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-calendar-check"></i>
                            <div class="stat-info">
                                <h3><?php echo $eventCount; ?></h3>
                                <p>Active Events</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-shopping-cart"></i>
                            <div class="stat-info">
                                <h3><?php echo array_sum($orderStatus); ?></h3>
                                <p>Today's Orders</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Performers -->
                <div class="stats-section">
                    <h3 class="section-title"><i class="fas fa-trophy"></i> Top Performers</h3>
                    <div class="stats-grid top-performers">
                        <div class="stat-card featured-card">
                            <i class="fas fa-medal"></i>
                            <div class="stat-info">
                                <h4><?php echo $topItem ? $topItem['name'] : 'No data'; ?></h4>
                                <p>Most Sold Item</p>
                                <?php if ($topItem): ?>
                                    <small><?php echo $topItem['total_sold']; ?> units sold</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="stat-card featured-card">
                            <i class="fas fa-dollar-sign"></i>
                            <div class="stat-info">
                                <h4><?php echo $topRevenueItem ? $topRevenueItem['name'] : 'No data'; ?></h4>
                                <p>Top Revenue Item</p>
                                <?php if ($topRevenueItem): ?>
                                    <small>$<?php echo number_format($topRevenueItem['total_revenue'], 2); ?> earned</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Status Breakdown -->
                <div class="stats-section">
                    <h3 class="section-title"><i class="fas fa-tasks"></i> Today's Order Status</h3>
                    <div class="stats-grid order-status-grid">
                        <div class="stat-card status-pending">
                            <i class="fas fa-clock"></i>
                            <div class="stat-info">
                                <h3><?php echo $orderStatus['pending'] ?? 0; ?></h3>
                                <p>Pending Orders</p>
                            </div>
                        </div>
                        <div class="stat-card status-preparing">
                            <i class="fas fa-fire"></i>
                            <div class="stat-info">
                                <h3><?php 
                                    $confirmed = $orderStatus['confirmed'] ?? 0;
                                    $preparing = $orderStatus['preparing'] ?? 0;
                                    $inKitchen = $confirmed + $preparing;
                                    echo $inKitchen;
                                ?></h3>
                                <p>In Kitchen</p>
                                <small>Confirmed: <?php echo $confirmed; ?> + Preparing: <?php echo $preparing; ?></small>
                                <!-- Debug: <?php echo "orderStatus array: " . json_encode($orderStatus); ?> -->
                            </div>
                        </div>
                        <div class="stat-card status-ready">
                            <i class="fas fa-check-circle"></i>
                            <div class="stat-info">
                                <h3><?php echo $orderStatus['ready'] ?? 0; ?></h3>
                                <p>Ready for Delivery</p>
                            </div>
                        </div>
                        <div class="stat-card status-delivered">
                            <i class="fas fa-shipping-fast"></i>
                            <div class="stat-info">
                                <h3><?php echo $orderStatus['delivered'] ?? 0; ?></h3>
                                <p>Delivered</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Status Overview -->
                <div class="stats-section">
                    <h3 class="section-title"><i class="fas fa-credit-card"></i> Payment Status (Today)</h3>
                    <div class="stats-grid payment-stats">
                        <div class="stat-card payment-pending">
                            <i class="fas fa-hourglass-half"></i>
                            <div class="stat-info">
                                <h3><?php echo $paymentStatus['pending']['count'] ?? 0; ?></h3>
                                <p>Pending Payments</p>
                                <small>$<?php echo number_format($paymentStatus['pending']['amount'] ?? 0, 2); ?></small>
                            </div>
                        </div>
                        <div class="stat-card payment-paid">
                            <i class="fas fa-check-circle"></i>
                            <div class="stat-info">
                                <h3><?php echo $paymentStatus['paid']['count'] ?? 0; ?></h3>
                                <p>Paid Orders</p>
                                <small>$<?php echo number_format($paymentStatus['paid']['amount'] ?? 0, 2); ?></small>
                            </div>
                        </div>
                        <div class="stat-card payment-failed">
                            <i class="fas fa-times-circle"></i>
                            <div class="stat-info">
                                <h3><?php echo $paymentStatus['failed']['count'] ?? 0; ?></h3>
                                <p>Failed Payments</p>
                                <small>$<?php echo number_format($paymentStatus['failed']['amount'] ?? 0, 2); ?></small>
                            </div>
                        </div>
                        <div class="stat-card payment-refunded">
                            <i class="fas fa-undo"></i>
                            <div class="stat-info">
                                <h3><?php echo $paymentStatus['refunded']['count'] ?? 0; ?></h3>
                                <p>Refunded</p>
                                <small>$<?php echo number_format($paymentStatus['refunded']['amount'] ?? 0, 2); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <?php include 'includes/footer.php'; ?>