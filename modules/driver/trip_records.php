<?php
session_start();
require_once '../../config/db.php';
include 'includes/header.php';
include 'includes/navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login&registrion.php");
    exit();
}

// ✅ Ensure driver is logged in
if (!isset($_SESSION['user_id'])) {
  die("⚠️ Access denied. Please log in first.");
}

$driver_id = $_SESSION['user_id'];

// ✅ Fetch all completed trips of this driver
$trips = $conn->query("
  SELECT 
    t.id AS trip_id,
    t.departed_at,
    r.origin,
    r.destination,
    df.vehicle_plate
  FROM active_trips t
  JOIN routes r ON t.route_id = r.id
  JOIN driver_form df ON t.driver_id = df.user_id
  WHERE t.driver_id = '$driver_id'
    AND t.status = 'completed'
  ORDER BY t.departed_at DESC
");
?>

<div class="container mt-4">
  <h3><i class="fa-solid fa-clock-rotate-left me-2 text-success"></i>My Trip Records</h3>
  <p>View all your completed trips and passengers who boarded.</p>

  <?php if ($trips && $trips->num_rows > 0): ?>
    <div class="row g-4 mt-3">
      <?php while ($trip = $trips->fetch_assoc()): ?>
        <?php
        $tripId = $trip['trip_id'];

        // ✅ Get passengers of this trip
        $passengersQuery = "
          SELECT u.name 
          FROM reservations r
          JOIN user u ON r.passenger_id = u.id
          WHERE r.trip_id = '$tripId'
            AND r.status IN ('boarded', 'completed')
        ";
        $passengersResult = $conn->query($passengersQuery);
        $passengerCount = $passengersResult->num_rows;
        ?>
        
        <div class="col-md-6">
          <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title text-success mb-0">
                  <i class="fa-solid fa-route me-2"></i>
                  <?= htmlspecialchars($trip['origin']) ?> ➜ <?= htmlspecialchars($trip['destination']) ?>
                </h5>
                <small class="text-muted">
                  <?= date('M d, Y - h:i A', strtotime($trip['departed_at'])) ?>
                </small>
              </div>

              <hr class="my-3">

              <div class="mb-2">
                <p class="mb-1 text-secondary fw-bold">
                  <i class="fa-solid fa-id-card me-2"></i>Vehicle Plate:
                </p>
                <p class="ms-4"><?= htmlspecialchars($trip['vehicle_plate']) ?></p>
              </div>

              <div>
                <p class="mb-1 text-secondary fw-bold">
                  <i class="fa-solid fa-users me-2"></i>Boarded Passengers:
                </p>
                <?php if ($passengerCount > 0): ?>
                  <button class="btn btn-sm btn-outline-success" 
                          data-bs-toggle="collapse" 
                          data-bs-target="#passengers<?= $tripId ?>">
                    <?= $passengerCount ?> Passenger(s)
                  </button>
                  <div id="passengers<?= $tripId ?>" class="collapse mt-2">
                    <ul class="list-group list-group-flush">
                      <?php while ($p = $passengersResult->fetch_assoc()): ?>
                        <li class="list-group-item"><?= htmlspecialchars($p['name']) ?></li>
                      <?php endwhile; ?>
                    </ul>
                  </div>
                <?php else: ?>
                  <span class="text-muted">No passengers boarded</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="alert alert-warning text-center mt-4">
      <i class="fa-solid fa-circle-exclamation me-2"></i>No completed trips found yet.
    </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
