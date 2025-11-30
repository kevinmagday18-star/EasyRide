<?php
require_once '../../config/db.php';
session_start();

if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'Invalid user ID!';
    header('Location: approvals.php');
    exit();
}

$user_id = intval($_GET['id']);

// Get the role of the user
$stmt = $conn->prepare("SELECT role FROM user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION['error'] = 'User not found!';
    header('Location: approvals.php');
    exit();
}

$role = $user['role'];

// Approve in `users` table
$stmt = $conn->prepare("UPDATE user SET status = 'approved' WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Approve in respective form table
if ($role == 'driver') {
    $stmt = $conn->prepare("UPDATE driver_form SET status = 'approved' WHERE user_id = ?");
} elseif ($role == 'dispatcher') {
    $stmt = $conn->prepare("UPDATE dispatcher_form SET status = 'approved' WHERE user_id = ?");
} elseif ($role == 'admin') {
    $stmt = $conn->prepare("UPDATE admin_form SET status = 'approved' WHERE user_id = ?");
}

if (isset($stmt)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

$_SESSION['success'] = ucfirst($role) . ' approved successfully!';
$conn->close();

header('Location: approvals.php');
exit();
?>
