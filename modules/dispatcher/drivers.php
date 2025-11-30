<?php
include 'includes/header.php';
include 'includes/navbar.php';
include '../../config/db.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login&registrion.php");
    exit();
}
// Handle Step 2: Add Driver Details (driver_form)
if (isset($_POST['add_driver_form'])) {
    $user_id = $_POST['user_id'];
    $license_no = $_POST['license_no'];
    $vehicle_plate = $_POST['vehicle_plate'];
    $seat_capacity = $_POST['seat_capacity'];
    $status = 'pending';

    $stmt = $conn->prepare("INSERT INTO driver_form (user_id, license_no, vehicle_plate, seat_capacity, status)
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issis", $user_id, $license_no, $vehicle_plate, $seat_capacity, $status);
    $stmt->execute();

    header("Location: drivers.php?success=driver_added");
    exit;
}

// Fetch Drivers (join with user table to show name)
$drivers = $conn->query("SELECT d.*, u.name 
                         FROM driver_form d 
                         JOIN user u ON d.user_id = u.id 
                         ORDER BY d.status DESC, d.id ASC");
?>

<body>
<div class="main p-3">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
    <h3 class="text-success mb-2"><i class="fa-solid fa-id-card me-2"></i>Driver Management</h3>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#registerUserModal">
      <i class="fa-solid fa-plus"></i> Add Driver
    </button>
  </div>
  <div id="softMessage"></div>

  <?php if (isset($_GET['success']) && $_GET['success'] === 'driver_added'): ?>
    <div class="alert alert-success alert-dismissible fade show mt-3 text-center" role="alert">
      <i class="fa-solid fa-circle-check me-2"></i>
      Driver successfully added. <strong>Waiting for admin approval.</strong>
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
          }, 4000);
        }
      });
    </script>
  <?php endif; ?>

  <!-- Search bar -->
  <div class="input-group mb-3" style="max-width:400px;">
    <span class="input-group-text bg-success text-white"><i class="fa fa-search"></i></span>
    <input type="text" id="searchDriver" class="form-control" placeholder="Search by name, ID, or plate...">
  </div>

  <!-- Drivers Table -->
  <div class="table-responsive">
    <table class="table table-hover align-middle" id="driversTable">
      <thead class="table-success">
        <tr>
          <th>Name</th>
          <th>License No</th>
          <th>Vehicle Plate</th>
          <th>Seat Capacity</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $drivers->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['license_no']) ?></td>
            <td><?= htmlspecialchars($row['vehicle_plate']) ?></td>
            <td><?= htmlspecialchars($row['seat_capacity']) ?></td>
            <td>
              <?php if ($row['status'] == 'approved'): ?>
                <span class="badge bg-success">Approved</span>
              <?php else: ?>
                <span class="badge bg-warning text-dark">Pending</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- STEP 1: Register User Modal -->
<div class="modal fade" id="registerUserModal" tabindex="-1" aria-labelledby="registerUserLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered custom-modal">
    <div class="modal-content">
      <form id="registerUserForm"> <!-- form moved inside -->
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="registerUserLabel">Register New Driver</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="text" name="name" class="form-control mb-3" placeholder="Full Name" required>
          <input type="text" name="address" class="form-control mb-3" placeholder="Address" required>
          <input type="text" name="mobile" class="form-control mb-3" placeholder="Mobile number">
          <input type="password" name="password" class="form-control mb-3" placeholder="Password">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success w-100">Next</button>
        </div>
      </form>
    </div>
  </div>
</div>




<!-- STEP 2: Driver Form Modal -->
<div class="modal fade" id="addDriverModal" tabindex="-1" aria-labelledby="addDriverLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="addDriverLabel">Driver Details</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="user_id" id="driver_user_id">
          <input type="text" name="license_no" class="form-control mb-2" placeholder="License No" required>
          <input type="text" name="vehicle_plate" class="form-control mb-2" placeholder="Vehicle Plate" required>
          <input type="number" name="seat_capacity" class="form-control mb-2" placeholder="Seat Capacity" required>
        </div>
        <div class="modal-footer">
          <button type="submit" name="add_driver_form" class="btn btn-success w-100">Submit for Approval</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Bootstrap + JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Handle user registration (Step 1)
document.getElementById('registerUserForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);

  fetch('register_driver_user.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    // Hide first modal
    const registerModal = bootstrap.Modal.getInstance(document.getElementById('registerUserModal'));
    registerModal.hide();

    // Set user_id in next modal
    document.getElementById('driver_user_id').value = data.user_id;

    // Show next modal
    const driverModal = new bootstrap.Modal(document.getElementById('addDriverModal'));
    driverModal.show();
  });
});

// Search filter
document.getElementById('searchDriver').addEventListener('keyup', function() {
  const filter = this.value.toLowerCase();
  const rows = document.querySelectorAll('#driversTable tbody tr');
  rows.forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
  });
});
</script>
</body>
</html>
