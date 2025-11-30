<?php
require_once '../../config/db.php';
session_start();

$passenger_id = $_SESSION['user_id'] ?? null;
$reservation_id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if (!$passenger_id || !$reservation_id || !$action) {
  die("Invalid request.");
}

if ($action === 'cancel') {
  $stmt = $conn->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ? AND passenger_id = ?");
} elseif ($action === 'complete') {
  $stmt = $conn->prepare("UPDATE reservations SET status = 'completed' WHERE id = ? AND passenger_id = ?");
} else {
  die("Invalid action.");
}

$stmt->bind_param("ii", $reservation_id, $passenger_id);
$stmt->execute();

header("Location: my_trips.php");
exit;
?>
