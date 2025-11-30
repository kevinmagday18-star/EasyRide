<?php
include '../../config/db.php';

$name = $_POST['name'];
$address = $_POST['address'];
$mobile = $_POST['mobile'] ?? null;
$password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
$role = 'driver';
$status = 'pending';
$form_submitted = 0;

$stmt = $conn->prepare("INSERT INTO user (name, address, mobile, password, role, status, form_submitted) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssi", $name, $address, $mobile, $password, $role, $status, $form_submitted);
$stmt->execute();

echo json_encode(["user_id" => $conn->insert_id]);
?>
