<?php
require_once '../../config/db.php';

$route_id = $_GET['route_id'] ?? 0;

$query = "
SELECT u.name AS passenger_name, r.status
FROM reservations r
JOIN user u ON r.passenger_id = u.id
WHERE r.route_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $route_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
  while ($res = $result->fetch_assoc()) {
    echo '<li class="list-group-item d-flex justify-content-between align-items-center">'
      . htmlspecialchars($res['passenger_name'])
      . '<span class="' . ($res['status'] === 'boarded' ? 'text-success' : 'text-danger') . '">'
      . '<i class="fa-solid ' . ($res['status'] === 'boarded' ? 'fa-check' : 'fa-xmark') . '"></i> '
      . ucfirst($res['status'])
      . '</span></li>';
  }
} else {
  echo '<li class="list-group-item text-muted text-center">No reservations yet.</li>';
}
?>
