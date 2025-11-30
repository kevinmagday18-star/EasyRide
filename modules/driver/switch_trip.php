<?php
session_start();
require_once '../../config/db.php';

$trip_id = $_POST['trip_id'] ?? null;
$driver_id = $_SESSION['user_id'] ?? null;

if ($trip_id && $driver_id) {
    // Get current route
    $trip = $conn->query("SELECT route_id, started_at FROM active_trips WHERE id='$trip_id'")->fetch_assoc();
    $route_id = $trip['route_id'];
    $current_time = $trip['started_at'];

    // Get the next driver (after current one)
    $next = $conn->query("
        SELECT id, started_at FROM active_trips 
        WHERE route_id='$route_id' AND status='active' AND started_at > '$current_time'
        ORDER BY started_at ASC LIMIT 1
    ");

    if ($next->num_rows > 0) {
        $nextTrip = $next->fetch_assoc();

        // Swap start times
        $conn->query("UPDATE active_trips SET started_at='{$nextTrip['started_at']}' WHERE id='$trip_id'");
        $conn->query("UPDATE active_trips SET started_at='$current_time' WHERE id='{$nextTrip['id']}'");

        $_SESSION['message'] = "Switched queue position successfully!";
    } else {
        $_SESSION['message'] = "No driver available to switch with.";
    }
}

header("Location: trip_status.php");
exit();
?>
