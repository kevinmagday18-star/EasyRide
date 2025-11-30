<?php
include '../../config/db.php';
include 'includes/header.php';
include 'includes/navbar.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
  header("Location: ../../modules/login/login&registrion.php");
  exit();
}

// Get user info
$user_sql = "SELECT name, mobile FROM user WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get dispatcher details
$form_sql = "SELECT dispatcher_id, station FROM dispatcher_form WHERE user_id = ?";
$stmt2 = $conn->prepare($form_sql);
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$form = $stmt2->get_result()->fetch_assoc();

// ✅ Handle station update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['station'])) {
    $new_station = $_POST['station'];

    $update_sql = "UPDATE dispatcher_form SET station = ? WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $new_station, $user_id);
    $update_stmt->execute();

    // Refresh data after update
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// ✅ Get list of available stations (from routes table)
$stations_result = $conn->query("SELECT DISTINCT origin FROM routes");
$stations = [];
while ($row = $stations_result->fetch_assoc()) {
    $stations[] = $row['origin'];
}
?>

<div class="container mt-4">
  <h3><i class="fa-solid fa-user-gear me-1 text-success"></i> My Account</h3>
  <p>View and manage your dispatcher account information.</p>

  <form method="POST" class="mt-3" style="max-width: 500px;">
    <div class="mb-3">
      <label class="form-label">Full Name</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($user['name']); ?>" readonly>
    </div>

    <div class="mb-3">
      <label class="form-label">Mobile Number</label>
      <input type="email" class="form-control" value="<?= htmlspecialchars($user['mobile']); ?>" readonly>
    </div>

    <div class="mb-3">
      <label class="form-label">Station Assigned</label>
      <select name="station" class="form-select" required>
        <option value="">-- Select Station --</option>
        <?php foreach ($stations as $station): ?>
          <option value="<?= htmlspecialchars($station); ?>"
            <?= ($form['station'] === $station) ? 'selected' : ''; ?>>
            <?= htmlspecialchars($station); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <button type="submit" class="btn btn-success">Update Station</button>
    <a href="../../modules/login/logout.php" class="btn btn-danger mt-2 ms-2">Logout</a>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
