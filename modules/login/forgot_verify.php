<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$mobile = $_SESSION['reset_email'];

if (isset($_POST['verify_otp'])) {

    $entered = trim($_POST['otp']);

    $stmt = $conn->prepare("
        SELECT * FROM otp_reset 
        WHERE mobile = ? 
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stmt->bind_param("s", $mobile);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row && $row['otp'] === $entered && strtotime($row['expires_at']) > time()) {

        $_SESSION['otp_verified'] = true;
        header("Location: reset_new_password.php");
        exit();

    } else {
        $error = "Invalid or expired OTP.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Document</title>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow p-4" style="max-width: 450px; width: 100%; border-top: 6px solid #28a745;">

        <h3 class="text-center text-success fw-bold mb-4">Verify OTP</h3>

        <form method="post">

            <div class="mb-3">
                <label class="form-label fw-semibold">Enter the OTP sent to your email</label>
                <input type="text" name="otp" class="form-control" required placeholder="Enter OTP">
            </div>

            <?php if (!empty($error)) { ?>
                <div class="alert alert-danger text-center py-2">
                    <?= $error ?>
                </div>
            <?php } ?>

            <button name="verify_otp" class="btn btn-success w-100 fw-bold">
                Verify
            </button>
        </form>

    </div>
</div>
</form>
</body>
</html>

