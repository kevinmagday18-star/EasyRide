<?php

session_start();
$errors = [
    'login' => $_SESSION['login_error'] ?? '',
    'register' => $_SESSION['register_error'] ?? ''
];
$activeForm = $_SESSION['active_form'] ?? 'login';

session_unset();

function showError($error){
    return !empty($error) ? "<p class='error-message'>$error</p>" : '';
}
function isActiveForm($forname, $activeForm) {
    return $forname === $activeForm ? 'active' : '';
}
?>

 
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
  <title>EasyRide | Login & Registration</title>

  <style>
    /* GLOBAL SETTINGS */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background: url('bg.jpg') no-repeat center center/cover;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      backdrop-filter: brightness(0.9);
    }

    /* MAIN CONTAINER */
    .container {
      width: 100%;
      max-width: 400px;
      background: rgba(255, 255, 255, 0.92);
      border-radius: 15px;
      box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
      overflow: hidden;
      padding: 30px;
      transition: all 0.3s ease-in-out;
    }

    /* FORM BOX */
    .form-box {
      display: none;
    }

    .form-box.active {
      display: block;
    }

    /* FORM ELEMENTS */
    form {
      display: flex;
      flex-direction: column;
    }

    h2 {
      text-align: center;
      color: #2e7d32; /* green shade */
      margin-bottom: 20px;
      font-weight: 600;
    }

    input, select {
      margin-bottom: 15px;
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 15px;
      outline: none;
      transition: border 0.3s ease;
    }

    input:focus, select:focus {
      border-color: #2e7d32;
    }

    /* BUTTON */
    button {
      background-color: #2e7d32;
      color: white;
      border: none;
      padding: 12px;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover {
      background-color: #1b5e20;
    }

    /* LINKS */
    p {
      text-align: center;
      margin-top: 15px;
    }

    p a {
      color: #2e7d32;
      text-decoration: none;
      font-weight: 600;
    }

    p a:hover {
      text-decoration: underline;
    }

    /* RESPONSIVE DESIGN */
    @media (max-width: 768px) {
      .container {
        max-width: 90%;
        padding: 25px;
      }

      input, select, button {
        font-size: 14px;
        padding: 10px;
      }
    }

    @media (max-width: 480px) {
      h2 {
        font-size: 22px;
      }

      .container {
        border-radius: 10px;
        padding: 20px;
      }
    }

    .error-message {
      color: red;
      font-size: 14px;
      margin-bottom: 10px;
      text-align: center;
    }

    .password-wrapper {
        position: relative;
        width: 100%;
    }

    .password-wrapper input {
        width: 100%;
        padding-right: 40px; /* space for the eye inside */
        height: 45px;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 16px;
    }

    .password-wrapper i {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #555;
        font-size: 18px;
    }

    .logo-top {
      width: 110px;
      margin-bottom: 15px;
      display: block;
      margin-left: auto;
      margin-right: auto;
    }



  </style>
</head>
<body>
    <a href="../../index.php" 
      class="btn btn-success" 
      style="position: absolute; top: 20px; left: 20px; border-radius: 8px;">
          <i class="fa-solid fa-arrow-left"></i>
    </a>
    <br><br><br>
  <div class="container">
    <div class="form-box <?= isActiveForm('login', $activeForm); ?>" id="login-form">
      <form action="login_registrion.php" method="post">
        <img src="../../logo.png" alt="EasyRide Logo" class="logo-top">
        <h2>Login</h2>
        <?= showError($errors['login']); ?>
        <input type="text" name="mobile" placeholder="Mobile number or Email" required>
        <div class="password-wrapper">
            <input type="password" id="logPassword" name="password" placeholder="Password" required>
            <i class="fa-solid fa-eye-slash" id="toggleLoginPassword"></i>
        </div>

        <button type="submit" name="login">Login</button>
        <!--<p><a href="forgot_password.php">Forgot password</a></p>-->
        <p>Don't have an account? <a href="#" onclick="showform('register-form')">Register</a></p>
      </form>
    </div>

    <div class="form-box <?= isActiveForm('register', $activeForm); ?>" id="register-form">
      <form action="login_registrion.php" method="post">
        <img src="../../logo.png" alt="EasyRide Logo" class="logo-top">
        <h2>Register</h2>
        <?= showError($errors['register']); ?>
        <input type="text" name="name" placeholder="Name" required>
        <input type="text" name="address" placeholder="Address" required>
        <input type="text" name="mobile" placeholder="Mobile number" required>
        <div class="password-wrapper">
            <input type="password" id="regPassword" name="password" placeholder="Password" required>
            <i class="fa-solid fa-eye-slash" id="toggleRegisterPassword"></i>
        </div>
        <select name="role" id="" required>
          <option value="passenger">Passenger</option>
          <option value="dispatcher">Dispatcher</option>
          <option value="driver">Driver</option>
          <option value="admin">Admin</option>
        </select>
        <button type="submit" name="register">Register</button>
        <p>Already have an account? <a href="#" onclick="showform('login-form')">Login</a></p>
      </form>
    </div>
  </div>

  <script>
    function showform(formId) {
      document.querySelectorAll('.form-box').forEach(box => box.classList.remove('active'));
      document.getElementById(formId).classList.add('active');
    }

    const loginPass = document.getElementById("logPassword");
    const toggleLogin = document.getElementById("toggleLoginPassword");

    toggleLogin.addEventListener("click", () => {
        const type = loginPass.type === "password" ? "text" : "password";
        loginPass.type = type;

        toggleLogin.classList.toggle("fa-eye");
        toggleLogin.classList.toggle("fa-eye-slash");
    });

    const registerPass = document.getElementById("regPassword");
    const toggleRegister = document.getElementById("toggleRegisterPassword");

    toggleRegister.addEventListener("click", () => {
        const type = registerPass.type === "password" ? "text" : "password";
        registerPass.type = type;

        toggleRegister.classList.toggle("fa-eye");
        toggleRegister.classList.toggle("fa-eye-slash");
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
