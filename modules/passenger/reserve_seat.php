<?php
session_start();
require_once '../../config/db.php';

$passenger_id = $_SESSION['user_id'] ?? null;
$route_id     = $_POST['route_id'] ?? null;
$driver_id    = $_POST['driver_id'] ?? null;

if (!$passenger_id || !$route_id || !$driver_id) {
    die("Missing required information.");
}

// ✅ STEP 1: Check for an active trip first
$tripStmt = $conn->prepare("
  SELECT id 
  FROM active_trips 
  WHERE driver_id = ? AND route_id = ? AND status = 'active'
  LIMIT 1
");
$tripStmt->bind_param("ii", $driver_id, $route_id);
$tripStmt->execute();
$tripResult = $tripStmt->get_result();
$trip_id = $tripResult->fetch_assoc()['id'] ?? null;
$tripStmt->close();

if (!$trip_id) {
    echo '
    <div class="alert alert-warning text-center mt-3" role="alert">
        ⚠️ No active trip found for this route and driver.<br>
        Please wait until the driver starts a trip.
    </div>';
    // Optional: stop here if no trip should continue
    return;
}



// ✅ STEP 2: Prevent duplicate active reservations for the same active trip
$check = $conn->prepare("
  SELECT id 
  FROM reservations
  WHERE passenger_id = ? 
    AND trip_id = ? 
    AND status IN ('not_boarded', 'boarded')
  LIMIT 1
");
$check->bind_param("ii", $passenger_id, $trip_id);
$check->execute();
$active = $check->get_result();

if ($active && $active->num_rows > 0) {
    header("Location: reserve.php?error=active_reservation");
    exit;
}


// ✅ STEP 3: Generate unique QR code for this trip
$uniqueCode = "RES-" . uniqid() . "-" . $passenger_id;
$status = 'not_boarded';
$created_at = time();

// ✅ STEP 4: Insert the reservation properly linked to the trip
$stmt = $conn->prepare("
  INSERT INTO reservations (passenger_id, driver_id, route_id, trip_id, qr_code, status, created_at)
  VALUES (?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("iiiissi", $passenger_id, $driver_id, $route_id, $trip_id, $uniqueCode, $status, $created_at);

if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}

$reservation_id = $stmt->insert_id;
$stmt->close();

// ✅ STEP 5: Redirect to success page with QR code
$qrImageURL = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($uniqueCode) . "&size=200x200";
header("Location: reservation_success.php?qr=" . urlencode($qrImageURL) . "&code=" . urlencode($uniqueCode));
exit;
?>
