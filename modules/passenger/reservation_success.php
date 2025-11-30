<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>
<?php
$qr = $_GET['qr'] ?? '';
$code = $_GET['code'] ?? '';
?>

<div class="container text-center mt-5">
  <h3>Your Reservation QR Code</h3>
  <img src="<?= htmlspecialchars($qr) ?>" alt="QR Code" class="mt-3">
  <p class="mt-2 text-muted">Show this QR code to the dispatcher to board.<br>Code: <?= htmlspecialchars($code) ?></p>
</div>
