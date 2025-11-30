<?php
include '../../config/db.php';
include 'includes/header.php';
include 'includes/navbar.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login&registrion.php");
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
  header("Location: ../../modules/login/login&registrion.php");
  exit();
}

// Get user info
$user_sql = "SELECT name, address, mobile FROM user WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get dispatcher details
$form_sql = "SELECT license_no, vehicle_plate, seat_capacity FROM driver_form WHERE user_id = ?";
$stmt2 = $conn->prepare($form_sql);
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$form = $stmt2->get_result()->fetch_assoc();
?>

<div class="container mt-4">
  <h3><i class="fa-solid fa-user-gear me-1 text-success"></i> My Account</h3>
  <p>View your account information.</p>

  <form class="mt-3" style="max-width: 500px;">
    <div class="mb-3">
      <label class="form-label">Full Name</label>
      <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
    </div>
    <div class="mb-3">
      <label class="form-label">Address</label>
      <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['address']); ?>" readonly>
    </div>
    <div class="mb-3">
      <label class="form-label">Mobile Number</label>
      <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['mobile']); ?>" readonly>
    </div>
    <div class="mb-3">
      <label class="form-label">License Number</label>
      <input type="text" class="form-control" value="<?php echo htmlspecialchars($form['license_no'] ?? 'N/A'); ?>" readonly>
    </div>
    <div class="mb-3">
      <label class="form-label">Plate Number</label>
      <input type="text" class="form-control" value="<?php echo htmlspecialchars($form['vehicle_plate'] ?? 'N/A'); ?>" readonly>
    </div>
    <div class="mb-3">
      <label class="form-label">Seat Capacity</label>
      <input type="text" class="form-control" value="<?php echo htmlspecialchars($form['seat_capacity'] ?? 'N/A'); ?>" readonly>
    </div>
    <a href="../../modules/login/logout.php" class="btn btn-danger mt-2">Logout</a>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
