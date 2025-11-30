<?php
include 'includes/header.php';
include 'includes/navbar.php';
include '../../config/db.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login&registrion.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$dispatcher_id = $_SESSION['user_id'] ?? null;

// 🚀 Handle "Set Trip" form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_trip'])) {
    $driver_id = $_POST['driver_id'] ?? null;
    $route_id = $_POST['route_id'] ?? null;
    $seat_capacity = $_POST['seat_capacity'] ?? null;

    if (!$driver_id || !$route_id || !$seat_capacity) {
        header("Location: start_trip.php?error=missing_details");
        exit;
    }

    // 🔍 Check if driver already has an active or waiting trip
    $check = $conn->prepare("SELECT id FROM active_trips WHERE driver_id = ? AND status IN ('waiting','active')");
    $check->bind_param("i", $driver_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        header("Location: start_trip.php?error=already_active");
        exit;
    }

    // 🟢 Insert new waiting trip
    $insert = $conn->prepare("
        INSERT INTO active_trips (driver_id, route_id, dispatcher_id, seat_capacity, status, start_time)
        VALUES (?, ?, ?, ?, 'waiting', NOW())
    ");
    $insert->bind_param("iiii", $driver_id, $route_id, $dispatcher_id, $seat_capacity);

    if ($insert->execute()) {
        header("Location: start_trip.php?message=trip_created");
        exit;
    } else {
        header("Location: start_trip.php?error=create_failed");
        exit;
    }

    $insert->close();
}


// 🟩 Handle trip activation (set from waiting → active)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_trip'])) {
    $trip_id = $_POST['trip_id'];

    $update = $conn->prepare("UPDATE active_trips SET status='active', start_time=NOW() WHERE id=?");
    $update->bind_param("i", $trip_id);

    if ($update->execute()) {
        // Redirect back with a success message
        header("Location: start_trip.php?message=trip_started");
        exit;
    } else {
        // Redirect back with an error message
        header("Location: start_trip.php?error=start_failed");
        exit;
    }

    $update->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Trip ID from hidden input
    $tripId = $_POST['trip_id'];

    // Save the current time as the official start time
    $startTime = date("Y-m-d H:i:s");
    $conn->query("UPDATE active_trips SET start_time = '$startTime' WHERE id = $tripId");

    // After updating start time, redirect
    header("Location: dashboard.php");
    exit();
}
// Fetch Routes
$routes = $conn->query("SELECT id, area, origin, destination FROM routes ORDER BY id ASC");

// Fetch Approved Drivers
$drivers = $conn->query("
  SELECT u.id AS driver_id, u.name, df.vehicle_plate, df.seat_capacity
  FROM user u 
  INNER JOIN driver_form df ON u.id = df.user_id 
  WHERE u.role='driver' AND u.status='approved'
  ORDER BY u.name ASC
");

// Fetch Waiting Trips
$waitingTrips = $conn->query("
  SELECT t.id, u.name AS driver_name, df.vehicle_plate, r.origin, r.destination, r.area
  FROM active_trips t
  INNER JOIN user u ON t.driver_id = u.id
  INNER JOIN driver_form df ON df.user_id = u.id
  INNER JOIN routes r ON t.route_id = r.id
  WHERE t.status = 'waiting'
  ORDER BY t.start_time DESC
");
?>


<div class="main p-3">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
    <h3 class="text-success mb-2"><i class="fa-solid fa-route me-2"></i>Set Driver Trip</h3>
  </div>
  
  <div id="alertContainer" class="mt-3">
  <?php if (isset($_GET['message']) && $_GET['message'] === 'trip_created'): ?>
    <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
      <i class="fa-solid fa-circle-check me-2"></i>
      ✅ Trip successfully created! Waiting to start...
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

  <?php elseif (isset($_GET['error'])): ?>
    <?php if ($_GET['error'] === 'missing_details'): ?>
      <div class="alert alert-warning alert-dismissible fade show text-center" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>
        ⚠️ Missing trip details. Please fill out all required fields.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>

    <?php elseif ($_GET['error'] === 'already_active'): ?>
      <div class="alert alert-warning alert-dismissible fade show text-center" role="alert">
        <i class="fa-solid fa-circle-info me-2"></i>
        ⚠️ This driver already has an active or waiting trip.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>

    <?php elseif ($_GET['error'] === 'create_failed'): ?>
      <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
        <i class="fa-solid fa-xmark-circle me-2"></i>
        ❌ Error creating trip. Please try again.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const alertBox = document.querySelector('.alert');
      if (alertBox) {
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => {
          const bsAlert = new bootstrap.Alert(alertBox);
          bsAlert.close();
        }, 5000);
      }
    });
  </script>
  </div>
  <div id="alertContainer" class="mt-3">
  <?php if (isset($_GET['message']) && $_GET['message'] === 'trip_started'): ?>
    <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
      <i class="fa-solid fa-circle-check me-2"></i>
      🚗 Trip successfully started and is now active!
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const alertBox = document.querySelector('.alert');
        if (alertBox) {
          alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
          setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alertBox);
            bsAlert.close();
          }, 5000);
        }
      });
    </script>
    <?php elseif (isset($_GET['error']) && $_GET['error'] === 'start_failed'): ?>
      <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>
        ❌ Failed to start trip. Please try again.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>

      <script>
        document.addEventListener('DOMContentLoaded', () => {
          const alertBox = document.querySelector('.alert');
          if (alertBox) {
            alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => {
              const bsAlert = new bootstrap.Alert(alertBox);
              bsAlert.close();
            }, 5000);
          }
        });
      </script>
    <?php endif; ?>
  </div>

  <!-- 🟢 CREATE TRIP FORM -->
  <div class="card shadow-sm border-0 p-4 rounded-4 mb-4">
    <form method="POST" action="">
      <!-- Route Selection -->
      <div class="mb-3">
        <label class="form-label fw-bold text-success">Select Route</label>
        <select name="route_id" class="form-select" required>
          <option value="" disabled selected>-- Choose Route --</option>
          <?php while ($route = $routes->fetch_assoc()): ?>
            <option value="<?= $route['id'] ?>">
              <?= htmlspecialchars($route['origin']) ?> → <?= htmlspecialchars($route['destination']) ?> (<?= htmlspecialchars($route['area']) ?>)
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Driver Filter + Dropdown -->
      <div class="mb-3">
        <label class="form-label fw-bold text-success">Select Driver</label>
        <input type="text" id="searchDriver" class="form-control mb-2" placeholder="Search driver by name or plate...">
        <select name="driver_id" id="driverDropdown" class="form-select" size="5" required onchange="updateSeatCapacity()">
          <?php while ($driver = $drivers->fetch_assoc()): ?>
            <option value="<?= $driver['driver_id'] ?>" data-seat="<?= htmlspecialchars($driver['seat_capacity']) ?>">
              <?= htmlspecialchars($driver['name']) ?> - <?= htmlspecialchars($driver['vehicle_plate']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Auto Seat Capacity -->
      <div class="mb-3">
        <label class="form-label fw-bold text-success">Seat Capacity</label>
        <input type="number" id="seat_capacity" name="seat_capacity" class="form-control" readonly placeholder="Auto-filled">
      </div>

      <button type="submit" name="set_trip" class="btn btn-success w-100 rounded-3">Set Trip</button>
    </form>
  </div>

  <!-- 🟠 LIST OF WAITING TRIPS -->
  <div class="card shadow-sm border-0 p-4 rounded-4">
    <h5 class="text-success fw-bold mb-3"><i class="fa-solid fa-clock me-2"></i>Waiting Trips</h5>

    <?php if ($waitingTrips->num_rows > 0): ?>
      <table class="table table-bordered align-middle">
        <thead class="table-light">
          <tr>
            <th>Driver</th>
            <th>Plate</th>
            <th>Route</th>
            <th>Area</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($trip = $waitingTrips->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($trip['driver_name']) ?></td>
              <td><?= htmlspecialchars($trip['vehicle_plate']) ?></td>
              <td><?= htmlspecialchars($trip['origin']) ?> → <?= htmlspecialchars($trip['destination']) ?></td>
              <td><?= htmlspecialchars($trip['area']) ?></td>
              <td>
                <form method="POST" action="" class="d-inline">
                  <input type="hidden" name="trip_id" value="<?= $trip['id'] ?>">
                  <button type="submit" name="start_trip" class="btn btn-sm btn-success">
                    Start Trip
                  </button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p class="text-muted">No waiting trips available.</p>
    <?php endif; ?>
  </div>
</div>

<script>
// 🔍 Filter drivers
document.getElementById('searchDriver').addEventListener('keyup', function() {
  const filter = this.value.toLowerCase();
  const options = document.querySelectorAll('#driverDropdown option');
  options.forEach(opt => {
    const text = opt.textContent.toLowerCase();
    opt.style.display = text.includes(filter) ? '' : 'none';
  });
});

// 🟢 Auto-display seat capacity
function updateSeatCapacity() {
  const dropdown = document.getElementById('driverDropdown');
  const selected = dropdown.options[dropdown.selectedIndex];
  const seat = selected.getAttribute('data-seat');
  document.getElementById('seat_capacity').value = seat || '';
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
