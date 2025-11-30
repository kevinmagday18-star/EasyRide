<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../../config/db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../modules/login/login_registration.php");
    exit();
}

$driver_id = $_SESSION['user_id'];
$route_id = $_POST['route_id']; // or however you pass it from form/button


if (!$route_id) {
    $_SESSION['message'] = "Error: No route selected.";
    header("Location: dashboard.php");
    exit();
}

// ✅ End any previous active trips
$conn->query("UPDATE active_trips SET status='completed' WHERE driver_id='$driver_id' AND status='active'");

// ✅ Start new trip
$stmt = $conn->prepare("INSERT INTO active_trips (driver_id, route_id, dispatcher_id) VALUES (?, ?, NULL)");
$stmt->bind_param("ii", $driver_id, $route_id);
$stmt->execute();

$trip_id = $conn->insert_id;

// ✅ Link existing reservations to this trip
$sql = "
    UPDATE reservations 
    SET trip_id = $trip_id 
    WHERE driver_id = $driver_id 
      AND route_id = $route_id 
      AND (status IN ('not_boarded', 'boarded', 'not boarded'))
";
if ($conn->query($sql)) {
    echo "✅ Updated " . $conn->affected_rows . " reservation(s).";
} else {
    echo "❌ SQL Error: " . $conn->error;
}



// ✅ Debug info
if ($conn->affected_rows > 0) {
    $_SESSION['message'] = 'Trip started successfully and linked to reservations.';
} else {
    $_SESSION['message'] = 'Trip started, but no matching reservations found.';
}

header("Location: dashboard.php");
exit();
?>
