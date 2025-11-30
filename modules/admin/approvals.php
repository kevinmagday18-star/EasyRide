<?php
include 'includes/header.php';
include 'includes/sidebar.php';
require_once '../../config/db.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login&registrion.php");
    exit();
}

?>

<h2 class="section-title mb-4">
  <i class="fa-solid fa-check me-2 text-success"></i> Account Approvals
</h2>

<div class="table-responsive">
<table class="table table-bordered table-striped align-middle">
  <thead class="table-success">
    <tr>
      <th>Name</th>
      <th>Role</th>
      <th>Details</th>
      <th>Status</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody id="approvalTableBody">
    
  </tbody>
</table>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function refreshApprovalTable() {
  fetch('fetch_approvals.php')
    .then(response => response.text())
    .then(data => {
      document.getElementById('approvalTableBody').innerHTML = data;
    })
    .catch(error => console.error("Error refreshing table:", error));
}

// refresh every 3 seconds
setInterval(refreshApprovalTable, 3000);
</script>

</body>
</html>

  