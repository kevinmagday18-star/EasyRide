<?php
include 'includes/header.php'; 
include 'includes/sidebar.php'; 
include '../../config/db.php'; 

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login&registrion.php");
    exit();
}

// ROLE + SEARCH
$roleFilter = $_GET['role'] ?? 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// PAGINATION
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;
?>


<h2 class="section-title"><i class="fa-solid fa-user me-1 text-success"></i> Manage Users</h2>

<!-- ROLE FILTER TABS -->
<ul class="nav nav-tabs mb-3">
  <li class="nav-item">
    <a class="nav-link <?= $roleFilter == 'all' ? 'active' : '' ?>" href="?role=all">All Users</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?= $roleFilter == 'passenger' ? 'active' : '' ?>" href="?role=passenger">Passengers</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?= $roleFilter == 'driver' ? 'active' : '' ?>" href="?role=driver">Drivers</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?= $roleFilter == 'dispatcher' ? 'active' : '' ?>" href="?role=dispatcher">Dispatchers</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?= $roleFilter == 'admin' ? 'active' : '' ?>" href="?role=admin">Admins</a>
  </li>
</ul>

<!-- SEARCH FILTER -->
<form method="GET" class="row g-2 mb-3">
    <input type="hidden" name="role" value="<?= htmlspecialchars($roleFilter) ?>">
    <div class="col-auto">
        <input type="text" name="search" class="form-control" placeholder="Search by name, mobile, address" 
               value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-success">Search</button>
    </div>
</form>

<table class="table table-striped">
  <thead>
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Address</th>
      <th>Mobile number</th>
      <th>Role</th>
    </tr>
  </thead>
  <tbody>

<?php
// Base SQL
$sql = "SELECT * FROM user";
$countSql = "SELECT COUNT(*) AS total FROM user";

$where = [];
$params = [];
$types = "";

// Role filter
if ($roleFilter != 'all') {
    $where[] = "role = ?";
    $params[] = $roleFilter;
    $types .= "s";
}

// Search filter
if (!empty($search)) {
    $where[] = "(name LIKE ? OR mobile LIKE ? OR address LIKE ?)";
    $searchLike = "%$search%";
    $params[] = $searchLike;
    $params[] = $searchLike;
    $params[] = $searchLike;
    $types .= "sss";
}

// Add WHERE if needed
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
    $countSql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY id DESC LIMIT $limit OFFSET $offset";

// Prepare COUNT (total users)
$countStmt = $conn->prepare($countSql);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalUsers = $countResult->fetch_assoc()['total'];

$totalPages = ceil($totalUsers / $limit);

// Fetch users
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Display users
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "
        <tr>
            <td>{$row['id']}</td>
            <td>{$row['name']}</td>
            <td>{$row['address']}</td>
            <td>{$row['mobile']}</td>
            <td>" . ucfirst($row['role']) . "</td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='7' class='text-center text-muted'>No users found.</td></tr>";
}
?>

  </tbody>
</table>

<!-- PAGINATION -->
<nav>
  <ul class="pagination justify-content-center">

    <!-- Previous -->
    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
      <a class="page-link" 
         href="?role=<?= $roleFilter ?>&search=<?= $search ?>&page=<?= $page - 1 ?>">
        Previous
      </a>
    </li>

    <!-- Page numbers -->
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <li class="page-item <?= $i == $page ? 'active' : '' ?>">
        <a class="page-link"
           href="?role=<?= $roleFilter ?>&search=<?= $search ?>&page=<?= $i ?>">
           <?= $i ?>
        </a>
      </li>
    <?php endfor; ?>

    <!-- Next -->
    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
      <a class="page-link" 
         href="?role=<?= $roleFilter ?>&search=<?= $search ?>&page=<?= $page + 1 ?>">
        Next
      </a>
    </li>

  </ul>
</nav>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
