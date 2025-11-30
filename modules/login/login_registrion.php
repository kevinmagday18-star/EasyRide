<?php
session_start();

require __DIR__ . '/../../PHPMailer/src/Exception.php';
require __DIR__ . '/../../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../../config/db.php';

// ------------------------- REGISTER -------------------------
if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $mobile = strtolower(trim($_POST['mobile'])); // This can be number OR email
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Detect if input is email or mobile
    $isEmail = filter_var($mobile, FILTER_VALIDATE_EMAIL);
    $isMobile = preg_match('/^[0-9]{11}$/', $mobile);

    // Validate type
    if (!$isEmail && !$isMobile) {
        $_SESSION['register_error'] = 'Enter a valid email or 11-digit mobile number.';
        $_SESSION['active_form'] = 'register';
        header("Location: login&registrion.php");
        exit();
    }

    // Check for duplicate
    $stmt = $conn->prepare("SELECT mobile FROM user WHERE mobile = ?");
    $stmt->bind_param("s", $mobile);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['register_error'] = 'Email/Mobile is already registered!';
        $_SESSION['active_form'] = 'register';
        header("Location: login&registrion.php");
        exit();
    }

    // If passenger → OTP verification first
    if ($role === 'passenger') {

        $otp = rand(100000, 999999);

        // Save OTP to database
        $stmt = $conn->prepare("INSERT INTO otp_codes (mobile, otp) VALUES (?, ?)");
        $stmt->bind_param("ss", $mobile, $otp);
        $stmt->execute();

        // SEND OTP
        if ($isMobile) {
            // SEND SMS OTP
            $ch = curl_init();
            $sms_data = array(
                'apikey' => 'e8914f0dd30565c91f6a12b03f65f088',
                'number' => $mobile,
                'message' => 'Your EasyRide OTP is: ' . $otp,
                'sender_name' => 'EasyRide'
            );

            curl_setopt($ch, CURLOPT_URL, 'https://api.semaphore.co/api/v4/messages');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($sms_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            curl_close($ch);

        } else if ($isEmail) {

            $mail = new PHPMailer(true);

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'easyridenotif@gmail.com';
            $mail->Password = 'jpdezqwtnoduxzkm'; 
            $mail->SMTPSecure = 'tls'; // or PHPMailer::ENCRYPTION_STARTTLS
            $mail->Port = 587;

            // IMPORTANT FIX FOR XAMPP SSL ERROR
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $mail->setFrom('easyridenotif@gmail.com', 'EasyRide Verification');
            $mail->addAddress($mobile);
            $mail->isHTML(true);
            $mail->Subject = 'Registration Successful';
            $mail->Body = "
                    <h2>Welcome to EasyRide!</h2>
                    <p>Your registration is now <b>successful</b>.</p>
                    <p>You may now log in, you just need to verify your email using this OTP</p>
                    <br> 
                    <p>Your OTP is: <b>$otp</b></p>>";
            $mail->send();


        }

        // Store registration info temporarily
        $_SESSION['otp_register'] = [
            'name' => $name,
            'address' => $address,
            'mobile' => $mobile,
            'password' => $password,
            'role' => $role
        ];

        header("Location: otp_verification.php");
        exit();
    }

    // For drivers/admin/dispatcher → go to form
    $_SESSION['temp_register'] = [
        'name' => $name,
        'address' => $address,
        'mobile' => $mobile,
        'password' => $password,
        'role' => $role
    ];

    header("Location: ../../modules/forms/form_popup.php");
    exit();
}


// ------------------------- LOGIN -------------------------
if (isset($_POST['login'])) {
    $mobile = trim($_POST['mobile']); // can be mobile OR email
    $password = $_POST['password'];

    // Detect if email or mobile
    $isEmail = filter_var($mobile, FILTER_VALIDATE_EMAIL);
    $isMobile = preg_match('/^[0-9]{11}$/', $mobile);

    if (!$isEmail && !$isMobile) {
        $_SESSION['login_error'] = 'Enter a valid email or mobile number.';
        $_SESSION['active_form'] = 'login';
        header("Location: login&registrion.php");
        exit();
    }

    // Fetch user
    $stmt = $conn->prepare("SELECT * FROM user WHERE mobile = ?");
    $stmt->bind_param("s", $mobile);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['login_error'] = "Incorrect email/mobile or password!";
        $_SESSION['active_form'] = "login";
        header("Location: login&registrion.php");
        exit();
    }

    $user = $result->fetch_assoc();

    if (!password_verify($password, $user['password'])) {
        $_SESSION['login_error'] = "Incorrect email/mobile or password!";
        $_SESSION['active_form'] = "login";
        header("Location: login&registrion.php");
        exit();
    }

    // Restrict pending and rejected accounts for specific roles
    if ($user['status'] === 'pending' && $user['role'] !== 'passenger' && $user['mobile'] !== '09999999999') {
        $_SESSION['login_error'] = 'Your account is pending admin approval.';
        $_SESSION['active_form'] = 'login';
        header("Location: login&registrion.php");
        exit();
    }

    if ($user['status'] === 'rejected' && $user['role'] !== 'passenger' && $user['mobile'] !== '09999999999') {
        $_SESSION['login_error'] = 'Your account has been rejected by the admin.';
        $_SESSION['active_form'] = 'login';
        header("Location: login&registrion.php");
        exit();
    }

    // Redirect if form not yet submitted
    if (in_array($user['role'], ['admin', 'dispatcher']) && $user['form_submitted'] == 0 && $user['mobile'] !== '09999999999' && $user['status'] === 'approved') {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        header("Location: modules/forms/form_popup.php");
        exit();
    }

    // Successful login
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['mobile'] = $user['mobile'];
    $_SESSION['role'] = $user['role'];

    // Redirect based on role
    switch ($user['role']) {
        case 'admin':
            header("Location: ../admin/users.php");
            break;
        case 'driver':
            header("Location: ../driver/trip_status.php");
            break;
        case 'dispatcher':
            header("Location: ../dispatcher/dashboard.php");
            break;
        default:
            header("Location: ../passenger/reserve.php");
            break;
    }
    exit();
}

?>
