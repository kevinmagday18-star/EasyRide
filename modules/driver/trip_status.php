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

// ✅ Get the route of the current driver’s active trip
$routeQuery = "
    SELECT route_id 
    FROM active_trips 
    WHERE driver_id = '$driver_id' AND status = 'active'
";
$routeResult = $conn->query($routeQuery);
$route = $routeResult && $routeResult->num_rows > 0 ? $routeResult->fetch_assoc()['route_id'] : null;

// ✅ Get all drivers currently active in the same route
if ($route) {
    $driversQuery = "
        SELECT at.id AS trip_id, u.name, df.vehicle_plate, at.start_time, u.id AS driver_id
        FROM active_trips at
        JOIN user u ON at.driver_id = u.id
        JOIN driver_form df ON df.user_id = u.id
        WHERE at.route_id = '$route' AND at.status = 'active'
        ORDER BY at.start_time ASC
    ";
    $drivers = $conn->query($driversQuery);
} else {
    $drivers = null;
}
?>

<div class="container mt-4">
  <h3><i class="fa-solid fa-route me-2 text-success"></i>Trip Queue Status</h3>
  <p>View all active drivers in your selected route.</p>

  <?php if ($route && $drivers && $drivers->num_rows > 0): ?>
    <table class="table table-bordered table-striped mt-3 align-middle">
      <thead class="table-success">
        <tr>
          <th>#</th>
          <th>Driver Name</th>
          <th>Plate Number</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $position = 1;
        while ($row = $drivers->fetch_assoc()): 
          $isCurrentDriver = ($row['driver_id'] == $driver_id);
        ?>
          <tr class="<?= $isCurrentDriver ? 'table-warning' : '' ?>">
            <td><?= $position++ ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['vehicle_plate']) ?></td>
            <td><?= $isCurrentDriver ? '<span class="badge bg-primary">You</span>' : '<span class="badge bg-secondary">Waiting</span>' ?></td>
            <td>
              <?php if ($isCurrentDriver): ?>
                <!-- Cancel Trip -->
                <form action="cancel_trip.php" method="POST" class="d-inline">
                  <input type="hidden" name="trip_id" value="<?= $row['trip_id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-ban me-1"></i> Cancel
                  </button>
                </form>

                <!-- Switch Position -->
                <form action="switch_trip.php" method="POST" class="d-inline">
                  <input type="hidden" name="trip_id" value="<?= $row['trip_id'] ?>">
                  <button type="submit" class="btn btn-warning btn-sm">
                    <i class="fa-solid fa-arrows-rotate me-1"></i> Switch
                  </button>
                </form>
              <?php else: ?>
                <span class="text-muted">—</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-warning mt-3">
      <i class="fa-solid fa-circle-info me-1"></i> You don’t have any active trips at the moment.
    </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
