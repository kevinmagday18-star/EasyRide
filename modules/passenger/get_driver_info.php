<?php
require_once '../../config/db.php';

$route_id = $_GET['route_id'] ?? null;

if (!$route_id) {
  echo json_encode(['error' => 'Missing route_id']);
  exit;
}

$sql = "
  SELECT 
    u.id AS driver_id,
    u.name AS driver_name,
    df.vehicle_plate,
    df.seat_capacity,
    (
      df.seat_capacity - (
        SELECT COUNT(*) FROM reservations r 
        WHERE r.driver_id = t.driver_id 
          AND r.route_id = t.route_id
          AND r.status IN ('not_boarded', 'boarded')
      )
    ) AS remaining_seats
  FROM active_trips t
  JOIN user u ON t.driver_id = u.id
  JOIN driver_form df ON u.id = df.user_id
  WHERE t.route_id = ? AND t.status = 'active'
  LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $route_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
  if ($row['remaining_seats'] <= 0) {
    echo json_encode(['error' => 'No available seats']);
  } else {
    echo json_encode($row);
  }
} else {
  echo json_encode(['error' => 'No active driver found']);
}
?>
