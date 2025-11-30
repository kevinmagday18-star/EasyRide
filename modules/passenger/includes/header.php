<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Passenger | EasyRide</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">

  <style>
    body {
      font-family: 'Segoe UI', Tahoma, sans-serif;
      background-color: #f8f9fa;
      margin: 0;
    }

    .navbar {
      background-color: #2ecc71;
    }
    .navbar-brand, .nav-link {
      color: white !important;
      font-weight: 500;
    }
    .nav-link {
      transition: all 0.3s;
      border-radius: 8px;
      padding: 8px 15px;
    }
    .nav-link:hover {
      background-color: rgba(255, 255, 255, 0.25);
      transform: scale(1.05);
    }
    .navbar-toggler {
      border: none;
    }
    .navbar-toggler-icon {
      background-color: white;
      border-radius: 3px;
    }

    .btn-green {
      background-color: #2ecc71;
      color: white;
    }
    .btn-green:hover {
      background-color: #27ae60;
    }

    .card {
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>
