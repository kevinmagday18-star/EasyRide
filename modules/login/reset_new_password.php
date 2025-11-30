<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['otp_verified']) || !isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$mobile = $_SESSION['reset_email'];

if (isset($_POST['reset_pass'])) {

    $pass = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($pass !== $confirm) {
        $error = "Passwords do not match.";
    } else {

        $hash = password_hash($pass, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE user SET password = ? WHERE mobile = ?");
        $stmt->bind_param("ss", $hash, $mobile);
        $stmt->execute();

        unset($_SESSION['otp_verified']);
        unset($_SESSION['reset_email']);

        header("Location: login&registrion.php?reset=1");
        exit();
    }
}
?>

<form method="post">
    <h3>New Password</h3>
    <input type="password" name="password" class="form-control" required placeholder="New Password">
    <br>
    <input type="password" name="confirm" class="form-control" required placeholder="Confirm Password">
    <br>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <button name="reset_pass" class="btn btn-primary w-100">Reset Password</button>
</form>
