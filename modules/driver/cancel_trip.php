<?php
session_start();
require_once '../../config/db.php';

$trip_id = $_POST['trip_id'] ?? null;
$driver_id = $_SESSION['user_id'] ?? null;

if ($trip_id && $driver_id) {
    $conn->query("UPDATE active_trips SET status = 'cancelled' WHERE id = '$trip_id' AND driver_id = '$driver_id'");
    $_SESSION['message'] = "Trip cancelled successfully.";
}

header("Location: trip_status.php");
exit();
?>
