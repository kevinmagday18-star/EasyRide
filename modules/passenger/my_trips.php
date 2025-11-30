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

$query = "
  SELECT r.*, ro.origin, ro.destination, df.vehicle_plate 
  FROM reservations r
  JOIN routes ro ON r.route_id = ro.id
  JOIN driver_form df ON r.driver_id = df.user_id
  WHERE r.passenger_id = ?
  ORDER BY r.id DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $passenger_id);
$stmt->execute();
$res = $stmt->get_result();
?>

<div class="container mt-4">
  <h3><i class="fa-solid fa-list me-2 text-success"></i>My Trips</h3>
  <p>View your active and past reservations.</p>

  <table class="table table-striped mt-3">
    <thead>
      <tr>
        <th>Date</th>
        <th>Route</th>
        <th>Jeepney</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($res->num_rows > 0): ?>
        <?php while ($row = $res->fetch_assoc()): ?>
          <?php
          date_default_timezone_set('Asia/Manila');

          // 🕒 Format Unix timestamp to readable date & time
          $timestamp = $row['created_at'] ?? null;
          if (!empty($timestamp) && is_numeric($timestamp)) {
              $formattedDate = date('M d, Y - h:i A', $timestamp);
          } else {
              $formattedDate = "<span class='text-muted'>Not Available</span>";
          }

          ?>
          <tr>
            <td><?= $formattedDate ?></td>
            <td><?= htmlspecialchars($row['origin'] . " ➜ " . $row['destination']) ?></td> 
            <td><?= htmlspecialchars($row['vehicle_plate']) ?></td>
            <td>
              <?php
                if ($row['status'] === 'boarded') echo "<span class='text-success'>Boarded</span>";
                elseif ($row['status'] === 'not_boarded') echo "<span class='text-warning'>Not Boarded</span>";
                elseif ($row['status'] === 'cancelled') echo "<span class='text-danger'>Cancelled</span>";
                elseif ($row['status'] === 'completed') echo "<span class='text-primary'>Completed</span>";
              ?>
            </td>
            <td>
              <?php if ($row['status'] === 'not_boarded'): ?>
                <a href="update_status.php?id=<?= $row['id'] ?>&action=cancel" class="btn btn-danger btn-sm">
                  Cancel
                </a>
              <?php elseif ($row['status'] === 'boarded'): ?>
                <a href="update_status.php?id=<?= $row['id'] ?>&action=complete" class="btn btn-success btn-sm">
                  Complete
                </a>
              <?php else: ?>
                <span class="text-muted">—</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="5" class="text-center text-muted">No trips found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
