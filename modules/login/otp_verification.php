<?php
session_start();

require __DIR__ . '/../../PHPMailer/src/Exception.php';
require __DIR__ . '/../../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../../config/db.php';

if (!isset($_SESSION['otp_register'])) {
    header("Location: login&registrion.php");
    exit();
}

if (isset($_POST['verify_otp'])) {

    $entered_otp = trim($_POST['otp']);
    $mobile = strtolower(trim($_SESSION['otp_register']['mobile']));

    if ($mobile === '') {
        header("Location: login&registrion.php");
        exit;
    }

    // Look up OTP
    $stmt = $conn->prepare("SELECT * FROM otp_codes WHERE mobile = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("s", $mobile);
    $stmt->execute();
    $result = $stmt->get_result();
    $otpRow = ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;

    // OTP MATCHES — insert user
    if ($otpRow && $otpRow['otp'] === $entered_otp) {

        $data = $_SESSION['otp_register'];

        // Insert new user
        $stmt2 = $conn->prepare("
            INSERT INTO user (name, address, mobile, password, role, status, form_submitted)
            VALUES (?, ?, ?, ?, ?, 'approved', 0)
        ");

        $stmt2->bind_param(
            "sssss",
            $data['name'],
            $data['address'],
            $data['mobile'],
            $data['password'],
            $data['role']
        );

        if (!$stmt2->execute()) {
            die("Insert failed: " . $stmt2->error);
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = "smtp.gmail.com";
            $mail->SMTPAuth   = true;
            $mail->Username   = "easyridenotif@gmail.com";
            $mail->Password   = "jpdezqwtnoduxzkm";  // Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom("easyridenotif@gmail.com", "EasyRide");
            $mail->addAddress($mobile); // user email

            $mail->isHTML(true);
            $mail->Subject = "Registration Successful";
            $mail->Body = "
                <h2>Welcome to EasyRide!</h2>
                <p>Your registration is now <b>successful</b>.</p>
                <p>You can now log in using your credentials.</p>
                <br>
                <p>Thank you for joining us!</p>
            ";

            $mail->send();

        } catch (Exception $e) {
            echo "Mailer Error: " . $mail->ErrorInfo;
        }

        unset($_SESSION['otp_register']);

        header("Location: login&registrion.php?verified=1");
        exit();

    } else {
        $error = "Incorrect OTP. Try again.";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OTP Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center" style="height:100vh;">

<div class="card p-4" style="width: 350px;">
    <h4 class="text-center">Verify Your OTP</h4>
    <p class="text-center text-muted">
        We sent an OTP to your 
        <?php echo filter_var($_SESSION['otp_register']['mobile'], FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile number'; ?>
    </p>

    <?php if (!empty($error)) echo "<p class='text-danger text-center'>$error</p>"; ?>

    <form method="post">
        <input type="text" name="otp" class="form-control mb-3" placeholder="Enter OTP" required>
        <button type="submit" name="verify_otp" class="btn btn-success w-100">Verify</button>
    </form>
</div>

</body>
</html>
