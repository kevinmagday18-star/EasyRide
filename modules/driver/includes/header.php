<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Driver | EasyRide</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">

  <style>
    body {
      font-family: 'Segoe UI', Tahoma, sans-serif;
      background-color: #f8f9fa;
      margin: 0;
    }

    /* Navbar */
    .navbar {
      background-color: #2ecc71;
      transition: 0.3s;
    }

    .navbar-brand {
      color: white !important;
      font-weight: 600;
      font-size: 1.2rem;
    }

    .navbar-nav .nav-link {
      color: white !important;
      font-weight: 500;
      padding: 8px 15px;
      border-radius: 8px;
      transition: all 0.3s ease;
    }

    /* Hover effect */
    .navbar-nav .nav-link:hover {
      background-color: rgba(255, 255, 255, 0.2);
      transform: scale(1.05);
    }

    /* Toggler */
    .navbar-toggler {
      border: none;
    }
    .navbar-toggler-icon {
      background-color: white;
      border-radius: 3px;
    }

    /* Mobile menu */
    @media (max-width: 768px) {
      .navbar-collapse {
        background: #2ecc71;
      }
      .navbar-nav .nav-link {
        padding: 12px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
      }
      .navbar-nav .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.15);
      }
    }

    /* Buttons & Cards */
    .card {
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .btn-green {
      background-color: #2ecc71;
      color: white;
    }
    .btn-green:hover {
      background-color: #27ae60;
      transform: translateY(-1px);
    }
  </style>
</head>
<body>
