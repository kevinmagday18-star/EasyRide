<?php
require_once '../../config/db.php';

$area = $_GET['area'] ?? '';
$result = $conn->query("SELECT * FROM routes WHERE origin = '$area'");
$routes = [];

while ($r = $result->fetch_assoc()) {
  $routes[] = $r;
}

echo json_encode($routes);
?>
