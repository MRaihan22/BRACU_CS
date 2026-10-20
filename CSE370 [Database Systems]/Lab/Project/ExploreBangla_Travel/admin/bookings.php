<?php
require_once('../DBConnect.php');
require_once('../auth.php');

require_admin();
$u = current_user();

// Cancel booking (admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking_id'])) {
    $bid = (int)$_POST['cancel_booking_id'];
    if ($bid > 0) {
        $stmt = $conn->prepare("UPDATE bookings SET status='Cancelled' WHERE booking_id=?");
        $stmt->bind_param('i', $bid);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: bookings.php');
    exit;
}

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$sql = "
SELECT 
    b.booking_id, b.booking_date, b.status, b.travel_class, b.journey_gender,
    u.username, u.name,
    r.origin, r.destination,
    t.transport_name,
    COALESCE(GROUP_CONCAT(bs.seat_code ORDER BY bs.seat_code SEPARATOR ', '), '') AS seat_codes,
    g.name AS guide_name, g.trips_done,
    p.amount, p.currency, p.payment_method, p.status AS payment_status
FROM bookings b
JOIN users u ON u.user_id = b.user_id
JOIN routes r ON r.route_id = b.route_id
JOIN transport_types t ON t.transport_type_id = b.transport_type_id
LEFT JOIN booking_seats bs ON bs.booking_id = b.booking_id
LEFT JOIN guides g ON g.GID = b.guide_id
LEFT JOIN payments p ON p.booking_id = b.booking_id
";

$params = [];
$types = "";

if ($q !== "") {
    $sql .= " WHERE (u.username LIKE ? OR u.name LIKE ? OR r.origin LIKE ? OR r.destination LIKE ? OR t.transport_name LIKE ? OR bs.seat_code LIKE ? OR g.name LIKE ?) ";
    $like = "%{$q}%";
    $params = [$like,$like,$like,$like,$like,$like,$like];
    $types = "sssssss";
}

$sql .= " GROUP BY b.booking_id ORDER BY b.booking_date DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Bookings</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header class="topbar glass">
    <div class="container row-between">
        <div class="brand">
            <span class="logo">🧭</span>
            <span class="brand-text">ExploreBangla Admin</span>
        </div>
        <nav class="nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="fares.php">Manage Fares</a>
            <a href="bookings.php" class="active">Bookings</a>
            <a href="../logout.php" class="nav-right">Logout</a>
        </nav>
    </div>
</header>

<main class="page">
    <div class="card glass">
        <div class="row-between wrap">
            <div>
                <h2>All Bookings</h2>
                <p class="muted">Search by user, route, transport, seat, guide.</p>
            </div>
            <form method="GET" class="search">
                <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Search...">
                <button class="btn btn-primary" type="submit">Search</button>
                <a class="btn btn-ghost" href="bookings.php">Reset</a>
            </form>
        </div>

        <div class="table-wrap mt-16">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>When</th>
                        <th>User</th>
                        <th>Route</th>
                        <th>Transport</th>
                        <th>Class</th>
                        <th>Journey</th>
                        <th>Seats</th>
                        <th>Guide</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo (int)$row['booking_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['booking_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['origin'] . " → " . $row['destination']); ?></td>
                        <td><?php echo htmlspecialchars($row['transport_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['travel_class']); ?></td>
                        <td><?php echo htmlspecialchars($row['journey_gender']); ?></td>
                        <td><?php echo htmlspecialchars($row['seat_codes']); ?></td>
                        <td>
                            <?php if (!empty($row['guide_name'])): ?>
                                <?php echo htmlspecialchars($row['guide_name']); ?>
                                <span class="muted">(Trips: <?php echo (int)$row['trips_done']; ?>)</span>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($row['currency'] ?? 'BDT'); ?> 
                            <?php echo htmlspecialchars($row['amount'] ?? '0'); ?>
                            <span class="muted">(<?php echo htmlspecialchars($row['payment_status'] ?? 'Pending'); ?>)</span>
                        </td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td>
                            <?php if ($row['status'] === 'Confirmed'): ?>
                                <form method="POST" style="margin:0" onsubmit="return confirm('Cancel this booking?');">
                                    <input type="hidden" name="cancel_booking_id" value="<?php echo (int)$row['booking_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                                </form>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>
</main>

<footer class="footer glass">
    <span>© <?php echo date('Y'); ?> ExploreBangla Travel</span>
</footer>
</body>
</html>
