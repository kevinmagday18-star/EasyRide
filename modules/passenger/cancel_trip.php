<?php
session_start();
require_once '../../config/db.php';

$reservation_id = $_POST['reservation_id'] ?? null;
$passenger_id = $_SESSION['user_id'] ?? null;

if (!$reservation_id || !$passenger_id) {
    die("Missing data.");
}

// Update reservation status
$stmt = $conn->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ? AND passenger_id = ?");
$stmt->bind_param("ii", $reservation_id, $passenger_id);
$stmt->execute();

header("Location: reserve.php");
exit;
?>
