<?php
require_once('../DBConnect.php');
require_once('../auth.php');

require_admin();
$u = current_user();

// Update fare
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fare_id'], $_POST['amount'])) {
    $fare_id = (int)$_POST['fare_id'];
    $amount = (float)$_POST['amount'];

    if ($fare_id > 0 && $amount >= 0) {
        $stmt = $conn->prepare("UPDATE route_fares SET amount=? WHERE fare_id=?");
        $stmt->bind_param('di', $amount, $fare_id);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: fares.php');
    exit;
}

$sql = "
SELECT f.fare_id, r.origin, r.destination, t.transport_name, f.travel_class, f.amount, f.currency
FROM route_fares f
JOIN routes r ON r.route_id = f.route_id
JOIN transport_types t ON t.transport_type_id = f.transport_type_id
ORDER BY r.origin ASC, r.destination ASC, t.transport_name ASC, f.travel_class ASC
";
$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Manage Fares</title>
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
            <a href="fares.php" class="active">Manage Fares</a>
            <a href="bookings.php">Bookings</a>
            <a href="../logout.php" class="nav-right">Logout</a>
        </nav>
    </div>
</header>

<main class="page">
    <div class="card glass">
        <div class="row-between wrap">
            <div>
                <h2>Manage Fares</h2>
                <p class="muted">Admin can change route price anytime (currency: ৳ / BDT).</p>
            </div>
        </div>

        <div class="table-wrap mt-16">
            <table class="table">
                <thead>
                    <tr>
                        <th>Route</th>
                        <th>Transport</th>
                        <th>Class</th>
                        <th>Amount</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['origin'] . " → " . $row['destination']); ?></td>
                        <td><?php echo htmlspecialchars($row['transport_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['travel_class']); ?></td>
                        <td><?php echo "৳ " . htmlspecialchars($row['amount']); ?></td>
                        <td>
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="fare_id" value="<?php echo (int)$row['fare_id']; ?>">
                                <input type="number" step="0.01" min="0" name="amount" value="<?php echo htmlspecialchars($row['amount']); ?>" class="input-sm" required>
                                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="notice mt-16">
            <b>Tip:</b> Total payable = fare × number of seats (max 6 seats).
        </div>
    </div>
</main>

<footer class="footer glass">
    <span>© <?php echo date('Y'); ?> ExploreBangla Travel</span>
</footer>
</body>
</html>
