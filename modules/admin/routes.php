<?php
session_start();
include '../../config/db.php';
include 'includes/header.php';
include 'includes/sidebar.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login&registrion.php");
    exit();
}

// 🟩 Handle route addition
if (isset($_POST['add_route'])) {
    $area = trim($_POST['area']);
    $destination = trim($_POST['destination']);

    if (!empty($area) && !empty($destination)) {
        $origin = $area; // origin auto-fills from area
        $stmt = $conn->prepare("INSERT INTO routes (area, origin, destination) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $area, $origin, $destination);
        $stmt->execute();
        $message = "<div class='alert alert-success mt-3'>✅ Route added successfully for $area!</div>";
    } else {
        $message = "<div class='alert alert-danger mt-3'>⚠️ Please select both area and destination.</div>";
    }
}

// 🟥 Handle route deletion
if (isset($_POST['delete_route'])) {
    $id = $_POST['id'];
    $conn->query("DELETE FROM routes WHERE id = $id");
    $message = "<div class='alert alert-success mt-3'>🗑️ Route deleted successfully!</div>";
}

// 🟨 Fetch routes
$routes = $conn->query("SELECT * FROM routes ORDER BY area, origin, destination ASC");
?>

<div class="main-content p-4">
  <div class="container-fluid">
    <h2 class="mb-4 text-success"><i class="fas fa-route"></i> Manage Preselected Routes</h2>

    <?php if (isset($message)) echo $message; ?>

    <!-- Add Route Form -->
    <form method="POST" class="mb-4 p-3 bg-light rounded shadow-sm">
      <div class="row g-3">
        <!-- Select Area -->
        <div class="col-md-4">
          <label class="form-label">Select Area</label>
          <select name="area" id="area" class="form-select" required onchange="autoSetOrigin()">
            <option value="">Select Area</option>
            <option value="Aritao">Aritao</option>
            <option value="Bambang">Bambang</option>
            <option value="Bayombong">Bayombong</option>
            <option value="Solano">Solano</option>
            <option value="Sta Fe">Sta Fe</option>
          </select>
        </div>

        <!-- Auto-filled Origin -->
        <div class="col-md-4">
          <label class="form-label">Origin</label>
          <input type="text" id="origin" name="origin" class="form-control" readonly placeholder="Auto-filled from Area">
        </div>

        <!-- Select Destination -->
        <div class="col-md-3">
          <label class="form-label">Destination</label>
          <select name="destination" class="form-select" required>
            <option value="">Select Destination</option>
            <option value="Aritao">Aritao</option>
            <option value="Bambang">Bambang</option>
            <option value="Bayombong">Bayombong</option>
            <option value="Solano">Solano</option>
            <option value="Sta Fe">Sta Fe</option>
          </select>
        </div>

        <div class="col-md-1 d-flex align-items-end">
          <button type="submit" name="add_route" class="btn btn-success w-100">
            <i class="fas fa-plus"></i>
          </button>
        </div>
      </div>
    </form>

    <!-- Routes Table -->
    <div class="table-responsive">
      <table class="table table-hover table-striped align-middle">
        <thead class="table-success">
          <tr>
            <th>Area</th>
            <th>Origin</th>
            <th>Destination</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = $routes->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['area']) ?></td>
              <td><?= htmlspecialchars($row['origin']) ?></td>
              <td><?= htmlspecialchars($row['destination']) ?></td>
              <td>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  <button type="submit" name="delete_route" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash-alt"></i> Delete
                  </button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ✅ Auto-fill origin based on area -->
<script>
function autoSetOrigin() {
  const areaSelect = document.getElementById('area');
  const originInput = document.getElementById('origin');
  originInput.value = areaSelect.value || '';
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
