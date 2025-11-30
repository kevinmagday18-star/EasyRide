<?php
session_start();
require_once '../../config/db.php';
include 'includes/header.php';
include 'includes/navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login&registrion.php");
    exit();
}

?>

<div class="container mt-4">
  <h3><i class="fa-solid fa-clipboard-list me-2 text-success"></i>Trip Records</h3>
  <p>View all completed trips with detailed passenger lists.</p>

  <?php
  // ✅ Fetch completed trips from completed_trips table
  $tripRecords = $conn->query("
      SELECT 
          t.id AS trip_id,
          t.start_time,
          r.origin,
          r.destination,
          u.name AS driver_name,
          df.vehicle_plate
      FROM completed_trips t
      INNER JOIN routes r ON t.route_id = r.id
      INNER JOIN user u ON t.driver_id = u.id
      INNER JOIN driver_form df ON u.id = df.user_id
      ORDER BY t.start_time DESC
  ");

  if ($tripRecords && $tripRecords->num_rows > 0):
  ?>
    <table class="table table-striped mt-3 align-middle">
      <thead class="table-success">
        <tr>
          <th>Start Time</th>
          <th>Route</th>
          <th>Driver</th>
          <th>Vehicle Plate</th>
          <th>Passengers</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($trip = $tripRecords->fetch_assoc()): ?>
          <?php
          $tripId = $trip['trip_id'];

          // 🕒 Format start time
          $startTime = !empty($trip['start_time']) && $trip['start_time'] != '0000-00-00 00:00:00'
            ? date('Y-m-d h:i A', strtotime($trip['start_time']))
            : "<span class='text-muted'>N/A</span>";

          // 🔍 Fetch boarded/completed passengers
          $passengersQuery = "
              SELECT u.name 
              FROM reservations r
              JOIN user u ON r.passenger_id = u.id
              JOIN active_trips a ON r.trip_id = a.id
              JOIN completed_trips c ON c.trip_id = a.id
              WHERE c.id = '$tripId'
                AND r.status IN ('boarded', 'completed')
          ";
          
          $passengersResult = $conn->query($passengersQuery);
          $passengerCount = $passengersResult ? $passengersResult->num_rows : 0;
          ?>

          <tr>
            <td><?= $startTime ?></td>
            <td><?= htmlspecialchars($trip['origin'] . " ➜ " . $trip['destination']) ?></td>
            <td><?= htmlspecialchars($trip['driver_name']) ?></td>
            <td><?= htmlspecialchars($trip['vehicle_plate']) ?></td>
            <td>
              <?php if ($passengerCount > 0): ?>
                <button class="btn btn-sm btn-outline-success" data-bs-toggle="collapse" data-bs-target="#passengers<?= $tripId ?>">
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
                <span class="text-muted">No boarded passengers</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-warning text-center mt-4">
      <i class="fa-solid fa-circle-exclamation me-2"></i>No completed trips found.
    </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
