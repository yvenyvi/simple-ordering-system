<?php
require_once '../models/db_Model.php';

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get upcoming active events
$current_date = date('Y-m-d');
$current_time = date('H:i:s');

// Query to get active events that are today or in the future
$events_query = "SELECT * FROM events 
                WHERE is_active = 1 
                AND (event_date > ? OR (event_date = ? AND event_time >= ?))
                ORDER BY event_date ASC, event_time ASC";

$stmt = mysqli_prepare($connection, $events_query);
mysqli_stmt_bind_param($stmt, "sss", $current_date, $current_date, $current_time);
mysqli_stmt_execute($stmt);
$events_result = mysqli_stmt_get_result($stmt);
$events = [];
while ($row = mysqli_fetch_assoc($events_result)) {
    $events[] = $row;
}
mysqli_stmt_close($stmt);

$page_title = "Upcoming Events";
?>