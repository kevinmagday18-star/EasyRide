<?php
require_once '../../config/db.php';
session_start();

if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'Invalid user ID!';
    header('Location: approvals.php');
    exit();
}

$user_id = intval($_GET['id']);

// Update the user's approval status to 'rejected'
$sql = "UPDATE user SET status = 'rejected' WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    $_SESSION['success'] = 'User has been rejected.';
} else {
    $_SESSION['error'] = 'Error rejecting user.';
}

$stmt->close();
$conn->close();

header('Location: approvals.php');
exit();
?>
