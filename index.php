<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EasyRide | Home</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">

  <style>
    body {
      background-color: white;
      margin: 0;
      padding: 0;
      min-height: 100vh;
      position: relative;
      color: #f8f9fa;
    }

    /* Light fade overlay */
    body::before {
      content: "";
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.6);
      z-index: 0;
    }

    /* Keep content above overlay */
    body > * {
      position: relative;
      z-index: 1;
    }

    /* Navbar style */
    .navbar {
      background-color: #2ecc71;
      backdrop-filter: blur(8px);
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 2;
    }

    .navbar-nav .nav-link {
      transition: all 0.3s;
      border-radius: 8px;
      padding: 8px 15px;
      color: #f8f9fa;
      font-weight: 500;
    }

    .navbar-nav .nav-link:hover {
      background-color: rgba(255, 255, 255, 0.25);
      transform: scale(1.05);
      color: #f8f9fa;
    }

    /* Role button */
    .role-btn {
      background: #2ecc71;
      border: none;
      color: white;
      padding: 15px;
      border-radius: 10px;
      width: 120px;
      height: 120px;
      margin: 10px;
      transition: 0.3s;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      font-weight: 600;
    }

    .role-btn:hover {
      background: #27ae60;
    }

    .logo-top {
      width: 350px;
      margin-bottom: 15px;
    }

    /* Text styling */
    .statement {
      text-align: center;
      margin: 0 auto;
      color: #000;
    }

    /* Blur container */
    .blur-box {
      background: rgba(255, 255, 255, 0.35);
      backdrop-filter: blur(3px);
      -webkit-backdrop-filter: blur(10px);
      border-radius: 20px;
      padding: 10px;
    }

    /* Center vertically */
    .container {
      height: 100vh;
    }

    /* Reserve button */
    button, .btn-success {
      background-color: #2e7d32;
      color: white !important;
      border: none;
      padding: 12px;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover, .btn-success:hover {
      background-color: #1b5e20;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg shadow-sm">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold text-light" href="index.php">
        EasyRide
      </a>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="modules/login/login&registrion.php">Login</a>
        </li>
      </ul>
    </div>
  </nav>
    <br><br><br><br><br>
  <div class="container d-flex flex-column justify-content-center align-items-center text-center mt-5 pt-5">
    <div class="blur-box">

      <img src="logo.png" alt="EasyRide Logo" class="logo-top">
      <br>
      <a href="modules/login/login&registrion.php"
         class="btn btn-success px-4 py-2 fw-semibold text-white text-decoration-none">
         Reserve Now!
      </a>

      <h2 class="heading text-center mx-auto mt-3 mb-4 text-dark fs-1 fw-bold">
        Welcome to EasyRide!
      </h2>

      <p class="statement text-center mx-auto mt-3 mb-4 text-dark fs-4 fw-semibold">
        We help passengers book jeepney rides online in an easy and convenient way.
        No more long lines, just choose your route, book a ride, and go!
      </p>

      <p class="statement text-center mx-auto mt-3 mb-4 text-dark fs-4 fw-semibold">
        We are a group of IT students who made EasyRide Booking (Jeepney) to make commuting easier for everyone.
        Our goal is to help passengers save time and make jeepney rides more organized using technology.
      </p>

      
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
