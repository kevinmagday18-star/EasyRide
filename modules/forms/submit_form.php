<?php
session_start();
require_once '../../config/db.php';

$temp = $_SESSION['temp_register'] ?? null;

if (!$temp) {
  header("Location: ../../modules/login/login&registrion.php");
  exit();
}

$name = $temp['name'];
$address = $temp['address'];
$mobile = $temp['mobile'];
$password = $temp['password'];
$role = $temp['role'];

try {
  // Insert new user (status pending)
  $stmt = $conn->prepare("INSERT INTO user (name, address, mobile, password, role, status, form_submitted)
                          VALUES (?, ?, ?, ?, ?, 'pending', 1)");
  $stmt->bind_param("sssss", $name, $address, $mobile, $password, $role);
  $stmt->execute();
  $user_id = $conn->insert_id;

  // Insert into the specific form table
  if ($role === 'driver') {
    $license_no = $_POST['license_no'];
    $vehicle_plate = $_POST['vehicle_plate'];
    $seat_capacity = $_POST['seat_capacity'];

    $f = $conn->prepare("INSERT INTO driver_form (user_id, license_no, vehicle_plate, seat_capacity, status)
                         VALUES (?, ?, ?, ?, 'pending')");
    $f->bind_param("issi", $user_id, $license_no, $vehicle_plate, $seat_capacity);
    $f->execute();
  } elseif ($role === 'dispatcher') {
    $terminal = $_POST['station'];

    $f = $conn->prepare("INSERT INTO dispatcher_form (user_id, station, status)
                         VALUES (?, ?, 'pending')");
    $f->bind_param("is", $user_id, $terminal);
    $f->execute();
  } elseif ($role === 'admin') {
    $admin_code = $_POST['admin_code'];
    $f = $conn->prepare("INSERT INTO admin_form (user_id, admin_code, status) VALUES (?, ?, 'pending')");
    $f->bind_param("is", $user_id, $admin_code);
    $f->execute();
  }

  unset($_SESSION['temp_register']);
  $_SESSION['register_success'] = "Registration submitted for admin approval. Please wait.";
  header("Location: ../../modules/login/login&registrion.php");
  exit();

} catch (Exception $e) {
  echo "<p style='color:red;padding:20px;'>Error: " . $e->getMessage() . "</p>";
}
?>
