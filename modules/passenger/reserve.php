<?php
include 'includes/header.php';
include 'includes/navbar.php';
require_once '../../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login&registrion.php");
    exit();
}

$passenger_id = $_SESSION['user_id'] ?? null;
if (!$passenger_id) {
  header("Location: ../../modules/login/login_registration.php");
  exit;
}

// Fetch all unique areas (origins)
$areas = $conn->query("SELECT DISTINCT origin FROM routes ORDER BY origin ASC");
?>

<div class="container mt-4">
  <h3><i class="fa-solid fa-bus me-2 text-success"></i> Passenger Dashboard</h3>
  <p>Reserve your seat and get your QR code for easy boarding.</p>

  <form id="reservationForm" method="POST" action="reserve_seat.php">

    <div id="softMessage"></div>
    <div id="alertContainer">
      <?php if (isset($_GET['error']) && $_GET['error'] === 'active_reservation'): ?>
        <div class="alert alert-warning alert-dismissible fade show mt-3 text-center" role="alert">
          <i class="fa-solid fa-triangle-exclamation me-2"></i>
          You already have an active reservation for this trip.
          <strong>Cancel or complete it</strong> before making a new one.
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <script>
          // Smooth scroll to alert
          document.addEventListener('DOMContentLoaded', () => {
            const alertBox = document.querySelector('.alert');
            if (alertBox) {
              alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
              // Optional: fade out automatically after 5 seconds
              setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alertBox);
                bsAlert.close();
              }, 5000);
            }
          });
        </script>
      <?php endif; ?>
    </div>

    <!-- Area selection -->
    <div class="mb-3 mt-3">
      <label class="form-label">Select Your Area</label>
      <select id="areaSelect" name="area" class="form-select" required>
        <option value="">-- Select Area --</option>
        <?php while ($a = $areas->fetch_assoc()): ?>
          <option value="<?= htmlspecialchars($a['origin']) ?>"><?= htmlspecialchars($a['origin']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <!-- Route selection -->
    <div class="mb-3">
      <label class="form-label">Select Route</label>
      <select id="routeSelect" name="route_id" class="form-select" required>
        <option value="">-- Select Route --</option>
      </select>
    </div>

    <!-- 🔒 Hidden driver ID field -->
    <input type="hidden" name="driver_id" id="driver_id">

    <!-- Driver & Seats Info -->
    <div id="driverInfo" class="card p-3 mb-3 shadow-sm d-none">
      <h5 class="text-success"><i class="fa-solid fa-user-tie me-2"></i>Driver Information</h5>
      <p><strong>Name:</strong> <span id="driverName"></span></p>
      <p><strong>Vehicle Plate:</strong> <span id="vehiclePlate"></span></p>
      <p><strong>Remaining Seats:</strong> <span id="remainingSeats"></span></p>
    </div>

    <button type="submit" class="btn btn-success mt-2" id="reserveBtn" disabled>
      <i class="fa-solid fa-ticket me-1"></i> Reserve Seat
    </button>
  </form>
</div>

<!-- Script to fetch routes and driver info dynamically -->
<script>
document.getElementById('areaSelect').addEventListener('change', function() {
  const area = this.value;
  fetch('get_routes.php?area=' + area)
    .then(res => res.json())
    .then(data => {
      const routeSelect = document.getElementById('routeSelect');
      routeSelect.innerHTML = '<option value="">-- Select Route --</option>';
      data.forEach(r => {
        routeSelect.innerHTML += `<option value="${r.id}">${r.origin} ➜ ${r.destination}</option>`;
      });
      document.getElementById('driverInfo').classList.add('d-none');
      document.getElementById('reserveBtn').disabled = true;
    });
});

document.getElementById('routeSelect').addEventListener('change', function() {
  const routeId = this.value;
  const messageContainer = document.getElementById('softMessage');
  if (!routeId) return;

  fetch('get_driver_info.php?route_id=' + routeId)
    .then(res => res.json())
    .then(data => {
      if (data && data.driver_id) {
        document.getElementById('driverInfo').classList.remove('d-none');
        document.getElementById('driverName').textContent = data.driver_name;
        document.getElementById('vehiclePlate').textContent = data.vehicle_plate;
        document.getElementById('remainingSeats').textContent = data.remaining_seats + ' seats';
        document.getElementById('driver_id').value = data.driver_id;
        document.getElementById('reserveBtn').disabled = false;
      } else {
        messageContainer.innerHTML = `
          <div class="alert alert-warning text-center mb-3" role="alert">
            ⚠️ No active trip found for this route and driver.<br>
            Please wait until the driver starts a trip.
          </div>`;
        document.getElementById('driverInfo').classList.add('d-none');
        document.getElementById('reserveBtn').disabled = true;
      }
    })
    .catch(err => {
      console.error('Error fetching driver info:', err);
      messageContainer.innerHTML = `
        <div class="alert alert-danger text-center mb-3" role="alert">
          ❌ Something went wrong while fetching driver info. Please try again later.
        </div>`;
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
