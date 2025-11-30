<?php
require_once '../../config/db.php';

$sql = "SELECT u.id, u.name, u.role, u.status,
               df.license_no, df.vehicle_plate,
               dis.station,
               ad.admin_code
        FROM user u
        LEFT JOIN driver_form df ON u.id = df.user_id
        LEFT JOIN dispatcher_form dis ON u.id = dis.user_id
        LEFT JOIN admin_form ad ON u.id = ad.user_id
        WHERE u.role IN ('driver', 'dispatcher', 'admin')
        AND u.status IN ('pending', 'rejected')
        ORDER BY FIELD(u.role, 'driver', 'dispatcher', 'admin'), u.id ASC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        $badge = match ($row['status']) {
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'warning'
        };

        echo "<tr>
                <td>" . htmlspecialchars($row['name']) . "</td>
                <td>" . ucfirst($row['role']) . "</td>
                <td>";
        
        if ($row['role'] == 'driver') {
            echo "<strong>License:</strong> " . htmlspecialchars($row['license_no']) . "<br>
                  <strong>Plate:</strong> " . htmlspecialchars($row['vehicle_plate']);
        } elseif ($row['role'] == 'dispatcher') {
            echo "<strong>Assigned Terminal:</strong> " . htmlspecialchars($row['station']);
        } elseif ($row['role'] == 'admin') {
            echo "<strong>Admin Code:</strong> " . htmlspecialchars($row['admin_code']);
        }

        echo "</td>
              <td><span class='badge bg-$badge'>" . ucfirst($row['status']) . "</span></td>
              <td>";

        if ($row['status'] == 'pending') {
            echo "<a href='approve_user.php?id={$row['id']}' class='btn btn-sm btn-success me-1'>Approve</a>
                  <a href='reject_user.php?id={$row['id']}' class='btn btn-sm btn-danger'>Reject</a>";
        } elseif ($row['status'] == 'rejected') {
            echo "<a href='approve_user.php?id={$row['id']}' class='btn btn-sm btn-success'>Re-Approve</a>";
        } else {
            echo "<span class='text-muted'>Approved</span>";
        }

        echo "</td></tr>";
    }
} else {
    echo "<tr><td colspan='5' class='text-center text-muted'>No pending approvals found.</td></tr>";
}
?>