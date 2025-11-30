<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login&registrion.php");
    exit();
}

include 'includes/header.php';
include 'includes/navbar.php';
require_once '../../config/db.php';

$dispatcher_id = $_SESSION ['user_id'] ?? null;

// 🔍 Dispatcher area
$dispatcherData = $conn->query("SELECT station FROM dispatcher_form WHERE user_id = '$dispatcher_id'")->fetch_assoc();
$dispatcher_area = $dispatcherData['station'] ?? null;

if (!$dispatcher_area) {
  echo "<div class='container mt-5'>
          <div class='alert alert-warning text-center'>
            <i class='fa-solid fa-circle-exclamation me-2'></i>
            No terminal assigned to your account. Please contact the admin.
          </div>
        </div>";
  exit;
}

// 🔍 Fetch routes from dispatcher area
$sql = "SELECT * FROM routes WHERE origin = '$dispatcher_area' ORDER BY id ASC";
$routes = $conn->query($sql);
?>

<div class="container mt-4">
  <h3><i class="fa-solid fa-gauge-high me-2 text-success"></i>Dispatcher Dashboard</h3>
  <p>Monitoring trips and scanning passenger QR codes for area: 
     <strong><?= htmlspecialchars($dispatcher_area) ?></strong>.</p>

  <div id="alertContainer"></div>

  <!-- 🟢 UNIVERSAL QR SCANNER -->
  <div class="card p-3 mb-4 shadow-sm text-center">
    <h5><i class="fa-solid fa-camera me-2 text-success"></i>Universal QR Code Scanner</h5>
    <div id="qr-reader" 
         style="width: 100%; max-width: 420px; aspect-ratio: 1; margin: auto; border-radius: 10px; overflow: hidden; background: #e9ecef;">
    </div>
    <p id="scanResult" class="mt-3 fw-bold text-success"></p>
  </div>

  <!-- 🟢 ROUTE TABS -->
  <ul class="nav nav-tabs" id="routeTabs">
    <?php
    $count = 1;
    if ($routes->num_rows > 0) {
      while ($row = $routes->fetch_assoc()) {
        $routeId = $row['id'];
        $routeName = htmlspecialchars($row['origin'] . " → " . $row['destination']);
        $isActive = ($count == 1) ? 'active' : '';
        echo "
          <li class='nav-item'>
            <a class='nav-link $isActive' data-bs-toggle='tab' href='#route$routeId' data-route-id='$routeId'>
              $routeName
            </a>
          </li>";
        $count++;
      }
    } else {
      echo "<li class='nav-item'><span class='nav-link disabled'>No routes for this area</span></li>";
    }
    ?>
  </ul>

  <!-- 🟢 ROUTE CONTENTS -->
  <div class="tab-content mt-3">
    <?php
    $routes = $conn->query($sql);
    $count = 1;
    while ($row = $routes->fetch_assoc()):
      $routeId = $row['id'];
      $origin = htmlspecialchars($row['origin']);
      $destination = htmlspecialchars($row['destination']);
      $isActive = ($count == 1) ? 'show active' : '';

      // 🔍 Active trips with trip_id
      $tripQuery = "
        SELECT t.id AS trip_id, u.name AS driver_name, df.vehicle_plate, df.seat_capacity, t.driver_id
        FROM active_trips t
        JOIN user u ON t.driver_id = u.id
        JOIN driver_form df ON u.id = df.user_id
        WHERE t.route_id = $routeId AND t.status IN ('waiting', 'active')
      ";
      $tripResult = $conn->query($tripQuery);
    ?>
    <div class="tab-pane fade <?= $isActive ?>" id="route<?= $routeId ?>">
      <div class="card p-3 mb-3 shadow-sm">
        <h5><i class="fa-solid fa-bus me-2 text-success"></i>Active Trip Information</h5>

        <?php if ($tripResult->num_rows > 0): ?>
          <?php while ($trip = $tripResult->fetch_assoc()): ?>
            <?php
            $tripId = $trip['trip_id'];
            $driverId = $trip['driver_id'];
            
            // ✅ Show passengers linked to the trip OR with matching driver & route (even if trip_id is NULL)
            // ✅ Fetch passengers for this driver and route
          $resQuery = "
            SELECT 
                r.id AS reservation_id,
                u.name AS passenger_name,
                r.status AS reservation_status
            FROM reservations r
            JOIN user u ON r.passenger_id = u.id
            WHERE 
                r.trip_id = $tripId
                AND r.status IN ('not_boarded', 'boarded')
            ORDER BY r.id DESC
          ";

            $resResult = $conn->query($resQuery);
            $remainingSeats = $trip['seat_capacity'] - $resResult->num_rows;
            ?>
            <div class="row text-center mb-3">
              <div class="col-md-3">
                <p class="fw-bold text-success"><i class="fa-solid fa-user-tie me-2"></i>Driver:</p>
                <p><?= htmlspecialchars($trip['driver_name']) ?></p>
              </div>
              <div class="col-md-3">
                <p class="fw-bold text-success"><i class="fa-solid fa-id-card me-2"></i>Jeep Plate:</p>
                <p><?= htmlspecialchars($trip['vehicle_plate']) ?></p>
              </div>
              <div class="col-md-3">
                <p class="fw-bold text-success"><i class="fa-solid fa-route me-2"></i>Route:</p>
                <p><?= "$origin → $destination" ?></p>
              </div>
              <div class="col-md-3">
                <p class="fw-bold text-success"><i class="fa-solid fa-users me-2"></i>Seats Left:</p>
                <p><?= max($remainingSeats, 0) ?> remaining</p>
              </div>
              <div class="col-md-3">
                <p class="fw-bold text-success"><i class="fa-solid fa-clock me-2"></i>Timer:</p>
                <p id="timer-<?= $driverId ?>">⏳ 30:00</p>
              </div>
              <script>
                document.addEventListener("DOMContentLoaded", function() {
                    startTimer(<?= $driverId ?>, <?= $routeId ?>);
                });
              </script>
            </div>

            <!-- Passenger List -->
            <div class="card p-3 shadow-sm">
              <h5><i class="fa-solid fa-users me-2 text-success"></i>Passenger List</h5>
              <ul class="list-group" id="passengerList<?= $routeId ?>" data-driver-id="<?= $driverId ?>">
                <?php if ($resResult->num_rows > 0): ?>
                  <?php while ($res = $resResult->fetch_assoc()): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                      <?= htmlspecialchars($res['passenger_name']) ?>
                      <span class="<?= $res['reservation_status'] === 'boarded' ? 'text-success' : 'text-danger' ?>">
                        <i class="fa-solid <?= $res['reservation_status'] === 'boarded' ? 'fa-check' : 'fa-xmark' ?>"></i>
                        <?= ucfirst($res['reservation_status']) ?>
                      </span>
                    </li>

                  <?php endwhile; ?>
                <?php else: ?>
                  <li class="list-group-item text-muted text-center">No reservations yet.</li>
                <?php endif; ?>
              </ul>
            </div>

            <button class='btn btn-success mt-3' 
                    onclick="setDeparture(<?= $driverId ?>, <?= $routeId ?>, false)">
              <i class='fa-solid fa-flag-checkered me-1'></i> Depart
            </button>
          <?php endwhile; ?>
        <?php else: ?>
          <p class="text-muted text-center">
            <i class="fa-solid fa-circle-exclamation me-2"></i>No active drivers on this route yet.
          </p>
        <?php endif; ?>
      </div>
    </div>
    <?php $count++; endwhile; ?>
  </div>
</div>

<!-- ✅ QR Code Scanner CDN -->
<script src="https://unpkg.com/html5-qrcode"></script>

<script>
let html5QrCode;

async function startScanner() {
  const qrReader = document.getElementById("qr-reader");
  if (!qrReader) return;

  if (html5QrCode) {
    try { await html5QrCode.stop(); } catch (e) {}
  }

  html5QrCode = new Html5Qrcode("qr-reader");
  const config = { fps: 10, qrbox: { width: window.innerWidth < 768 ? 220 : 320, height: window.innerWidth < 768 ? 220 : 320 } };

  try {
    const cameras = await Html5Qrcode.getCameras();
    if (!cameras.length) throw new Error("No cameras found.");

    const backCamera = cameras.find(c => c.label.toLowerCase().includes("back"))?.id || cameras[0].id;
    await html5QrCode.start(backCamera, config, handleScan);
    document.getElementById("scanResult").innerText = "📷 Scanner Ready!";
  } catch (err) {
    console.error("Camera start error:", err);
    document.getElementById("scanResult").innerText = "⚠️ Unable to access camera. Check permissions or reload.";
  }
}

async function handleScan(decodedText) {
  document.getElementById("scanResult").innerText = "✅ Scanned: " + decodedText;
  if (navigator.vibrate) navigator.vibrate(200);
  await html5QrCode.stop();

  fetch('update_boarding_status.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'qr_code=' + encodeURIComponent(decodedText)
  })
  .then(res => res.text())
  .then(data => {
    document.getElementById('scanResult').innerText = data;
    setTimeout(() => startScanner(), 2000);
  })
  .catch(err => {
    console.error(err);
    document.getElementById('scanResult').innerText = "⚠️ Error sending scan data.";
    setTimeout(() => startScanner(), 2000);
  });
}

document.addEventListener("DOMContentLoaded", startScanner);



// ✅ TIMER + DEPARTURE
let timers = {};
const timerDuration = 1800;

function startTimer(driverId, routeId) {
  clearInterval(timers[driverId]);
  let remaining = timerDuration;

  timers[driverId] = setInterval(() => {
    remaining--;
    let min = String(Math.floor(remaining / 60)).padStart(2, "0");
    let sec = String(remaining % 60).padStart(2, "0");
    document.getElementById(`timer-${driverId}`).textContent = `⏳ ${min}:${sec}`;

    if (remaining <= 0) {
      clearInterval(timers[driverId]);
      setDeparture(driverId, routeId, true);
    }
  }, 1000);
}

// ✅ Function to refresh passenger list for a specific route/driver
function refreshPassengerList(routeId, driverId) {
  fetch(`get_passenger_list.php?route_id=${routeId}&driver_id=${driverId}`)
    .then(res => res.text())
    .then(data => {
      const list = document.getElementById(`passengerList${routeId}`);
      if (list) {
        list.innerHTML = data;

        // Small visual cue (soft flash)
        list.style.background = '#e8f5e9';
        setTimeout(() => (list.style.background = 'transparent'), 500);
      }
    })
    .catch(err => console.error("Error refreshing passenger list:", err));
}

// ✅ Refresh Passenger List Function (placed OUTSIDE any other function)
function refreshPassengerList(routeId, driverId) {
  fetch(`get_passenger_list.php?route_id=${routeId}&driver_id=${driverId}`)
    .then(res => res.text())
    .then(data => {
      const list = document.getElementById(`passengerList${routeId}`);
      if (list) {
        list.innerHTML = data;
        // Small visual cue
        list.style.background = '#e8f5e9';
        setTimeout(() => (list.style.background = 'transparent'), 500);
      }
    })
    .catch(err => console.error("Error refreshing passenger list:", err));
}

// 🕒 Auto-refresh passenger lists every 5 seconds
setInterval(() => {
  document.querySelectorAll('[id^="passengerList"]').forEach(list => {
    const routeId = list.id.replace('passengerList', '');
    const driverId = list.getAttribute('data-driver-id');
    refreshPassengerList(routeId, driverId);
  });
}, 5000);

// ✅ Departure function (keep this separate)
function setDeparture(driverId, routeId, auto = false) {
  fetch('set_departure.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `driver_id=${driverId}&route_id=${routeId}&auto=${auto}`
  })
  .then(res => res.text())
  .then(data => {
    const alertContainer = document.getElementById('alertContainer');

    // Determine alert color
    let alertType = 'info';
    if (data.includes('✅')) alertType = 'success';
    else if (data.includes('⚠️')) alertType = 'warning';
    else if (data.includes('❌')) alertType = 'danger';
    else if (data.includes('🟡')) alertType = 'warning';

    // Show alert
    alertContainer.innerHTML = `
      <div class="alert alert-${alertType} alert-dismissible fade show text-center mt-3" role="alert">
        ${data}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    `;

    // Smooth scroll to alert
    alertContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });

    // Auto hide + reload full tab
    setTimeout(() => {
      if (data.includes('✅')) {
        // ✅ If trip successfully completed → reload the entire tab
        window.location.reload(true);
      } else {
        // 🟡 Otherwise, just close the alert softly
        const alert = bootstrap.Alert.getOrCreateInstance(document.querySelector('.alert'));
        alert.close();
      }
    }, 4000);
  })
  .catch(err => {
    console.error('Error:', err);
    const alertContainer = document.getElementById('alertContainer');
    alertContainer.innerHTML = `
      <div class="alert alert-danger alert-dismissible fade show text-center mt-3" role="alert">
        ❌ Something went wrong. Please try again.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    `;
  });
}



</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
