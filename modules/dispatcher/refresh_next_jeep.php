<?php
require_once '../../config/db.php';
$route_id = $_GET['route_id'] ?? null;

if (!$route_id) {
  echo "⚠️ No route selected.";
  exit;
}

// Fetch the next active trip for that route
$trip = $conn->query("
  SELECT u.name AS driver_name, df.vehicle_plate, df.seat_capacity, t.driver_id
  FROM active_trips t
  JOIN user u ON t.driver_id = u.id
  JOIN driver_form df ON u.id = df.user_id
  WHERE t.route_id = $route_id AND t.status = 'active'
  LIMIT 1
")->fetch_assoc();

if (!$trip) {
  echo "<p class='text-muted text-center'>No active trips available.</p>";
  exit;
}

echo "
  <div class='row text-center mb-3'>
    <div class='col-md-3'><strong>Driver:</strong><p>{$trip['driver_name']}</p></div>
    <div class='col-md-3'><strong>Plate:</strong><p>{$trip['vehicle_plate']}</p></div>
    <div class='col-md-3'><strong>Seats:</strong><p>{$trip['seat_capacity']}</p></div>
    <div class='col-md-3'><strong>Timer:</strong><p id='timer-{$trip['driver_id']}'>⏳ 30:00</p></div>
  </div>
  <button class='btn btn-success' data-driver-id='{$trip['driver_id']}' data-route-id='{$route_id}' onclick='setDeparture({$trip['driver_id']}, {$route_id}, false)'>
    <i class=\"fa-solid fa-flag-checkered me-1\"></i> Depart
  </button>
";
?>
