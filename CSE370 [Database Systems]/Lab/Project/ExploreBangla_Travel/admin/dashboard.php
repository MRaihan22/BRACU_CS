<?php
require_once('../DBConnect.php');
require_once('../auth.php');

require_admin();
$u = current_user();

// Totals
$totalBookings = 0;
$res = $conn->query("SELECT COUNT(*) AS total FROM bookings");
if ($res) { $totalBookings = (int)($res->fetch_assoc()['total'] ?? 0); }

$totalBusiness = 0.0;
$res2 = $conn->query("SELECT COALESCE(SUM(p.amount),0) AS total FROM payments p JOIN bookings b ON b.booking_id=p.booking_id WHERE p.status='Completed' AND b.status='Confirmed'");
if ($res2) { $totalBusiness = (float)($res2->fetch_assoc()['total'] ?? 0); }

// Recent bookings
$recent = [];
$q = "
SELECT b.booking_id, b.booking_date, COALESCE(GROUP_CONCAT(bs.seat_code ORDER BY bs.seat_code SEPARATOR ', '), '') AS seat_codes, b.travel_class, b.journey_gender,
       r.origin, r.destination, t.transport_name,
       p.amount, p.currency, p.payment_method, p.status AS payment_status,
       u.username
FROM bookings b
LEFT JOIN booking_seats bs ON bs.booking_id = b.booking_id
JOIN routes r ON r.route_id=b.route_id
JOIN transport_types t ON t.transport_type_id=b.transport_type_id
JOIN users u ON u.user_id=b.user_id
LEFT JOIN payments p ON p.booking_id=b.booking_id
GROUP BY b.booking_id
ORDER BY b.booking_date DESC
LIMIT 10
";
$res3 = $conn->query($q);
if ($res3) {
    while($row = $res3->fetch_assoc()) $recent[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ExploreBangla</title>
</head>
<body class="with-video">
    <video class="bg-video" autoplay muted loop playsinline>
        <source src="../assets/travel.mp4" type="video/mp4">
    </video>
    <div class="bg-overlay"></div>

    <header class="topbar glass">
        <div class="topbar-inner">
            <div class="brand">ExploreBangla</div>
            <nav class="nav">
                <a href="../index.php">Book</a>
                <a href="dashboard.php" class="active">Admin</a>
                <a href="fares.php">Manage Fares</a>
                <a href="bookings.php">Bookings</a>
                <a href="../logout.php" class="nav-right">Logout</a>
            </nav>
        </div>
    </header>

    <main class="page">
        <div class="card glass">
            <div class="row-between wrap">
                <div>
                    <h2>Admin Dashboard</h2>
                    <p class="muted">Overview of bookings & business.</p>
                </div>
                <div class="admin-chip">Logged in as: <b><?php echo htmlspecialchars($u['username']); ?></b></div>
            </div>

            <div class="stats-grid mt-16">
                <div class="stat">
                    <div class="stat-label">Total Bookings</div>
                    <div class="stat-value"><?php echo $totalBookings; ?></div>
                </div>
                <div class="stat">
                    <div class="stat-label">Total Business</div>
                    <div class="stat-value"><?php echo "৳" . number_format($totalBusiness, 2); ?></div>
                </div>
            </div>

            <div class="divider"></div>

            <div class="row-between wrap">
                <h3 style="margin:0;">Recent Bookings</h3>
                <a class="btn btn-ghost" href="bookings.php">View all</a>
            </div>

            <div class="table-wrap mt-12">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>User</th>
                            <th>Route</th>
                            <th>Transport</th>
                            <th>Class</th>
                            <th>Journey</th>
                            <th>Seat</th>
                            <th>Amount</th>
                            <th>Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent) === 0): ?>
                            <tr><td colspan="10" class="muted">No bookings yet.</td></tr>
                        <?php else: ?>
                            <?php foreach($recent as $r): ?>
                                <tr>
                                    <td><?php echo (int)$r['booking_id']; ?></td>
                                    <td><?php echo htmlspecialchars($r['booking_date']); ?></td>
                                    <td><?php echo htmlspecialchars($r['username']); ?></td>
                                    <td><?php echo htmlspecialchars($r['origin']." → ".$r['destination']); ?></td>
                                    <td><?php echo htmlspecialchars($r['transport_name']); ?></td>
                                    <td><?php echo htmlspecialchars($r['travel_class']); ?></td>
                                    <td><?php echo htmlspecialchars($r['journey_gender']); ?></td>
                                    <td><?php echo htmlspecialchars($r['seat_code']); ?></td>
                                    <td><?php echo "৳" . number_format((float)($r['amount'] ?? 0),2); ?></td>
                                    <td><?php echo htmlspecialchars($r['payment_method'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
