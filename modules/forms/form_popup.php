<?php
session_start();
require_once '../../config/db.php';

// Get stored registration data
$temp = $_SESSION['temp_register'] ?? null;

if (!$temp) {
  header("Location: ../../modules/login/login&registrion.php");
  exit();
}

$role = $temp['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Complete Registration | EasyRide</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
  <div class="card p-4 shadow-lg" style="width: 400px;">
    <h3 class="text-center mb-3">Complete Your Registration</h3>

    <form action="submit_form.php" method="POST">
      <input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>">

      <?php if ($role === 'driver'): ?>
        <div class="mb-3">
          <label class="form-label">Driver License No.</label>
          <input type="text" class="form-control" name="license_no" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Vehicle Plate No.</label>
          <input type="text" class="form-control" name="vehicle_plate" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Seat Capacity</label>
          <input type="number" class="form-control" name="seat_capacity" value="16" min="1" required>
        </div>

      <?php elseif ($role === 'dispatcher'): ?>
        <div class="mb-3">
          <label class="form-label">Assigned Terminal</label>
          <input type="text" class="form-control" name="station" required>
        </div>

      <?php elseif ($role === 'admin'): ?>
        <div class="mb-3">
          <label class="form-label">Admin Code</label>
          <input type="text" class="form-control" name="admin_code" required>
        </div>
      <?php endif; ?>

      <button type="submit" class="btn btn-success w-100">Submit for Approval</button>
    </form>
  </div>
</body>
</html>
