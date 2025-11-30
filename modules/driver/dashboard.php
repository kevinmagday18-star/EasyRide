<?php
include 'includes/header.php';
include 'includes/navbar.php';
require_once '../../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login&registrion.php");
    exit();
}

$driver_id = $_SESSION['user_id'] ?? null;

// 🔍 Check if driver already has an active trip
$checkActive = $conn->query("SELECT * FROM active_trips WHERE driver_id = '$driver_id' AND status = 'active'");
$hasActiveTrip = ($checkActive && $checkActive->num_rows > 0);

// Get selected area (if any)
$selected_area = $_GET['area'] ?? null;

// Fetch routes based on selected area
if ($selected_area) {
  $query = "SELECT * FROM routes WHERE origin = '$selected_area'";
  $result = $conn->query($query);
}
?>


<div class="container mt-4">
  <h3><i class="fa-solid fa-gauge-high me-2 text-success"></i>Driver Dashboard</h3>
  <p>Manage your trips and monitor your activity here.</p>

  <!-- ✅ Step 1: Area Selection -->
  <?php if (!$hasActiveTrip): ?>
    <form method="GET" class="mt-3" style="max-width: 400px;">
      <label class="form-label">Select Your Current Area</label>
      <select name="area" class="form-select" onchange="this.form.submit()" required>
        <option value="">-- Choose Area --</option>
        <?php
        $areas = ['Aritao', 'Bambang', 'Bayombong', 'Solano', 'Sta Fe'];
        foreach ($areas as $area) {
          $selected = ($selected_area === $area) ? 'selected' : '';
          echo "<option value='$area' $selected>$area</option>";
        }
        ?>
      </select>
    </form>
  <?php endif; ?>

  <!-- ✅ Step 2: Active Trip Notice -->
  <?php if ($hasActiveTrip): ?>
    <div class="alert alert-info mt-3">
      <i class="fa-solid fa-circle-info me-2"></i>
      You already have an <strong>active trip</strong>. Cancel it before setting another one.
    </div>

    <a href="trip_status.php" class="btn btn-primary mt-2">
      <i class="fa-solid fa-list me-1"></i> View Trip Status
    </a>

    <button class="btn btn-success mt-2" disabled>
      <i class="fa-solid fa-location-dot me-1"></i> Set Your Trip
    </button>
  <?php elseif ($selected_area): ?>
    <!-- ✅ Step 3: Set Trip Modal (once area is chosen) -->
    <button class="btn btn-success mt-3" data-bs-toggle="modal" data-bs-target="#setTripModal">
      <i class="fa-solid fa-location-dot me-2"></i> Set Your Trip
    </button>
  <?php endif; ?>

  <!-- Modal for Setting Trip -->
  <div class="modal fade" id="setTripModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title"><i class="fa-solid fa-route me-2"></i>Set Your Trip</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <form action="start_trip.php" method="POST">
            <input type="hidden" name="area" value="<?= htmlspecialchars($selected_area) ?>">

            <div class="mb-3">
              <label class="form-label">Select Route</label>
              <select name="route_id" id="route_id" class="form-select" required>
                <option value="">-- Select a Route --</option>
                <?php
                if (!empty($result) && $result->num_rows > 0) {
                  while ($row = $result->fetch_assoc()) {
                    $id = $row['id'];
                    $origin = htmlspecialchars($row['origin']);
                    $destination = htmlspecialchars($row['destination']);
                    echo "<option value='$id'>$origin ➜ $destination</option>";
                  }
                } else {
                  echo "<option value=''>No routes available for this area</option>";
                }
                ?>
              </select>
            </div>

            <div class="text-end">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                <i class="fa-solid fa-xmark me-1"></i> Cancel
              </button>
              <button type="submit" class="btn btn-success">
                <i class="fa-solid fa-check me-1"></i> Confirm Trip
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
