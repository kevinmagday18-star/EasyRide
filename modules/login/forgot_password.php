<?php
session_start();
require_once '../../config/db.php';

require_once '../../PHPMailer/src/PHPMailer.php';
require_once '../../PHPMailer/src/SMTP.php';
require_once '../../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST['send_otp'])) {

    $mobile = strtolower(trim($_POST['mobile']));

    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM user WHERE mobile = ?");
    $stmt->bind_param("s", $mobile);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) {
        $error = "This email or mobile number is not registered.";
    } else {

        $otp = rand(100000, 999999);
        $expires = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        // Store OTP
        $stmt2 = $conn->prepare("
            INSERT INTO otp_reset (mobile, otp, expires_at) VALUES (?, ?, ?)
        ");
        $stmt2->bind_param("sss", $mobile, $otp, $expires);
        $stmt2->execute();

        // Send Email
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = 'html';

        try {
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = "easyridenotif@gmail.com";
            $mail->Password = "jpdezqwtnoduxzkm";
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 465;

            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true
                ]
            ];

            $mail->setFrom("easyridenotif@gmail.com", "EasyRide Support");
            $mail->addAddress($mobile);

            $mail->isHTML(true);
            $mail->Subject = "EasyRide Password Reset OTP";
            $mail->Body = "Your OTP to reset your password is: <b>$otp</b><br>It expires in 5 minutes.";

            $mail->send();

            $_SESSION['reset_email'] = $mobile;
            header("Location: forgot_verify.php");
            exit();

        } catch (Exception $e) {
            $error = "Error sending message. " . $mail->ErrorInfo;
        }
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
    <div class="container d-flex justify-content-center mt-5">
    <div class="card shadow-lg p-4" style="max-width: 420px; width: 100%; border-top: 5px solid #28a745;">
        
        <h3 class="text-center text-success mb-4 fw-bold">Forgot Password</h3>

        <form method="post">
            
            <label class="form-label fw-semibold">Email Address</label>
            <input type="text" name="mobile" class="form-control" required placeholder="Enter your email">

            <?php if (!empty($error)) { ?>
                <div class="alert alert-danger mt-3 p-2 text-center">
                    <?= $error ?>
                </div>
            <?php } ?>

            <button name="send_otp" class="btn btn-success w-100 mt-4 fw-bold">
                Send OTP
            </button>
        </form>
    </div>
</div>
</body>
</html>
