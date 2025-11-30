<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/db.php';

$qr_code = $_POST['qr_code'] ?? '';

if (!$qr_code) {
  echo "⚠️ Invalid QR Code.";
  exit;
}

// 🔍 Step 1: Find reservation and check its linked trip
$query = $conn->prepare("
  SELECT 
    r.id AS reservation_id,
    r.status AS reservation_status,
    r.trip_id,
    t.status AS trip_status
  FROM reservations r
  LEFT JOIN active_trips t ON r.trip_id = t.id
  WHERE r.qr_code = ?
  LIMIT 1
");
$query->bind_param("s", $qr_code);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
  echo "❌ QR code not found.";
  exit;
}

$res = $result->fetch_assoc();
$reservation_id = $res['reservation_id'];
$current_status = $res['reservation_status'];
$trip_status = $res['trip_status'] ?? null;

// 🔍 Step 2: Validate that reservation belongs to an ACTIVE trip
if ($trip_status !== 'active') {
  echo "⚠️ This reservation is not part of an active trip.";
  exit;
}

// 🔍 Step 3: Prevent duplicate scans
if ($current_status === 'boarded') {
  echo "⚠️ Passenger already boarded.";
  exit;
}

// ✅ Step 4: Update status to boarded
$update = $conn->prepare("UPDATE reservations SET status = 'boarded' WHERE id = ?");
$update->bind_param("i", $reservation_id);

if ($update->execute()) {
  echo "✅ Passenger marked as boarded!";
} else {
  echo "❌ Failed to update passenger status.";
}

$update->close();
$query->close();
$conn->close();
?>
