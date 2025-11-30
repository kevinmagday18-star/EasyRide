<?php
require_once '../../config/db.php';

$routeId = intval($_GET['route_id']);
$driverId = intval($_GET['driver_id']);

$query = "
  SELECT r.id, u.name AS passenger_name, r.status
  FROM reservations r
  JOIN user u ON r.passenger_id = u.id
  WHERE r.route_id = $routeId AND r.driver_id = $driverId
  ORDER BY r.id ASC
";

$result = $conn->query($query);

if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $statusIcon = $row['status'] === 'boarded' ? 'fa-check text-success' : 'fa-xmark text-danger';
    echo "
      <li class='list-group-item d-flex justify-content-between align-items-center'>
        " . htmlspecialchars($row['passenger_name']) . "
        <span><i class='fa-solid $statusIcon'></i> " . ucfirst($row['status']) . "</span>
      </li>
    ";
  }
} else {
  echo "<li class='list-group-item text-muted text-center'>No reservations yet.</li>";
}
?>
