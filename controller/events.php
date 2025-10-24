<?php
require_once '../models/db_Model.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize events array
$events = [];

// Get upcoming active events
$current_date = date('Y-m-d');
$current_time = date('H:i:s');
$current_datetime = date('Y-m-d H:i:s');

// Debug logging
error_log("Events Controller - Current date: $current_date, time: $current_time");

// Check database connection
if (!isset($connection) || !$connection) {
    error_log("Events Controller - ERROR: No database connection!");
    $events = [];
} else {
    error_log("Events Controller - Database connection OK");
    
    // Enhanced query to get active events that are today or in the future
    $events_query = "SELECT * FROM events 
                    WHERE is_active = 1 
                    AND (
                        event_date > ? 
                        OR (event_date = ? AND (event_time IS NULL OR event_time >= ?))
                    )
                    ORDER BY event_date ASC, event_time ASC";

    $stmt = mysqli_prepare($connection, $events_query);
    if (!$stmt) {
        error_log("Events Controller - Error preparing query: " . mysqli_error($connection));
        $events = [];
    } else {
        mysqli_stmt_bind_param($stmt, "sss", $current_date, $current_date, $current_time);
        
        if (!mysqli_stmt_execute($stmt)) {
            error_log("Events Controller - Error executing query: " . mysqli_stmt_error($stmt));
            $events = [];
        } else {
            $events_result = mysqli_stmt_get_result($stmt);
            $events = [];
            
            while ($row = mysqli_fetch_assoc($events_result)) {
                // Additional validation for events happening today
                if ($row['event_date'] == $current_date && !empty($row['event_time'])) {
                    // For events today, check if the time hasn't passed
                    if ($row['event_time'] >= $current_time) {
                        $events[] = $row;
                    }
                } else if ($row['event_date'] > $current_date) {
                    // For future events, include them
                    $events[] = $row;
                } else if ($row['event_date'] == $current_date && empty($row['event_time'])) {
                    // For events today with no specific time, include them
                    $events[] = $row;
                }
            }
            
            error_log("Events Controller - Raw query returned " . mysqli_num_rows($events_result) . " rows");
            error_log("Events Controller - After filtering: " . count($events) . " events");
            
            if (!empty($events)) {
                $event_names = array_column($events, 'event_name');
                error_log("Events Controller - Events found: " . implode(', ', $event_names));
            } else {
                error_log("Events Controller - No upcoming events found after filtering");
            }
        }
        mysqli_stmt_close($stmt);
    }
}

$page_title = "Upcoming Events";

// Final debug log
error_log("Events Controller - Final events count: " . count($events));
?>