<?php
require_once '../../config/db.php';
session_start();

$driver_id = $_POST['driver_id'] ?? null;
$route_id = $_POST['route_id'] ?? null;
$auto = $_POST['auto'] ?? false;

if (!$driver_id || !$route_id) {
    echo "⚠️ Missing data.";
    exit;
}

// 🔍 Get trip details from active_trips
$tripQuery = $conn->query("
    SELECT * FROM active_trips
    WHERE driver_id = '$driver_id' AND route_id = '$route_id' AND status = 'active'
");
$trip = $tripQuery->fetch_assoc();

if (!$trip) {
    echo "⚠️ No active trip found.";
    exit;
}

$trip_id = $trip['id'];
$start_time = $trip['start_time'];
$end_time = date('Y-m-d H:i:s');

// ✅ Count boarded passengers
$countBoarded = $conn->query("
    SELECT COUNT(*) AS total FROM reservations
    WHERE trip_id = '$trip_id' AND status = 'boarded'
")->fetch_assoc()['total'] ?? 0;

// ✅ Get seat capacity
$capacity = $conn->query("
    SELECT df.seat_capacity 
    FROM driver_form df 
    JOIN user u ON df.user_id = u.id 
    WHERE u.id = '$driver_id'
")->fetch_assoc()['seat_capacity'] ?? 0;

// ✅ If manually departed or jeep full, move to completed_trips
// Convert to boolean properly
$auto = $_POST['auto'] ?? 'false';
$auto = filter_var($auto, FILTER_VALIDATE_BOOLEAN);

// ✅ Allow manual departure (auto == false) or full jeep
if ($auto === false || $countBoarded >= $capacity) {
    // Insert into completed_trips
    $conn->query("
        INSERT INTO completed_trips (trip_id, driver_id, route_id, start_time, end_time, total_passengers)
        VALUES ('$trip_id', '$driver_id', '$route_id', '$start_time', '$end_time', '$countBoarded')
    ");

    // Update trip + reservations
    $conn->query("UPDATE active_trips SET status = 'completed' WHERE id = '$trip_id'");
    $conn->query("UPDATE reservations SET status = 'completed' WHERE trip_id = '$trip_id'");

    echo "✅ Trip successfully moved to Trip Records.";
} else {
    echo "🟡 Jeep not yet full. Waiting for remaining passengers...";
}

?>
