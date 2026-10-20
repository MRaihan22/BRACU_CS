<?php
require_once('DBConnect.php');
require_once('auth.php');

require_login();
$u = current_user();

$booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
if ($booking_id <= 0) {
    header('Location: index.php');
    exit;
}

$uid = (int)($u['user_id'] ?? 0);

$conn->begin_transaction();

try {
    // Verify booking belongs to current user and is cancellable
    $stmt = $conn->prepare("SELECT booking_id, guide_id, status FROM bookings WHERE booking_id = ? AND user_id = ? LIMIT 1");
    $stmt->bind_param('ii', $booking_id, $uid);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$booking) {
        throw new Exception('Booking not found.');
    }

    if (($booking['status'] ?? '') === 'Cancelled') {
        // Already cancelled - just show message
        $conn->commit();
    } else if (($booking['status'] ?? '') !== 'Confirmed') {
        throw new Exception('This booking cannot be cancelled.');
    } else {
        // Cancel booking
        $stmt = $conn->prepare("UPDATE bookings SET status='Cancelled' WHERE booking_id = ? AND user_id = ?");
        $stmt->bind_param('ii', $booking_id, $uid);
        $stmt->execute();
        $stmt->close();

        // Mark payment as cancelled (keeps audit trail; also frees seats from availability query)
        $stmt = $conn->prepare("UPDATE payments SET status='Failed' WHERE booking_id = ?");
        $stmt->bind_param('i', $booking_id);
        $stmt->execute();
        $stmt->close();

        // Roll back guide trip counter (best-effort)
        $gid = (int)($booking['guide_id'] ?? 0);
        if ($gid > 0) {
            $stmt = $conn->prepare("UPDATE guides SET trips_done = GREATEST(trips_done - 1, 0) WHERE GID = ?");
            $stmt->bind_param('i', $gid);
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();
    }

    $message = "Your trip has been cancelled successfully.";
} catch (Exception $e) {
    $conn->rollback();
    $message = "Unable to cancel this trip: " . $e->getMessage();
}

// Fetch booking+route info for display (optional)
$route_text = null;
$transport_text = null;
$guide_text = null;

$stmt = $conn->prepare("
    SELECT r.origin, r.destination, tt.transport_name, g.name AS guide_name
    FROM bookings b
    JOIN routes r ON r.route_id = b.route_id
    JOIN transport_types tt ON tt.transport_type_id = b.transport_type_id
    LEFT JOIN guides g ON g.GID = b.guide_id
    WHERE b.booking_id = ? AND b.user_id = ?
    LIMIT 1
");
$stmt->bind_param('ii', $booking_id, $uid);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($row) {
    $route_text = ($row['origin'] ?? '') . " → " . ($row['destination'] ?? '');
    $transport_text = $row['transport_name'] ?? null;
    $guide_text = $row['guide_name'] ?? null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Cancelled - ExploreBangla Travel</title>
</head>
<body class="with-video">
    <video class="bg-video" autoplay muted loop playsinline>
        <source src="assets/travel.mp4" type="video/mp4">
    </video>
    <div class="bg-overlay"></div>

    <header class="topbar">
        <div class="container nav">
            <div class="brand">ExploreBangla Travel</div>
            <nav class="links">
                <a href="index.php">Home</a>
                <?php if (($u['user_type'] ?? '') === 'admin'): ?>
                    <a href="admin/dashboard.php">Admin</a>
                <?php endif; ?>
                <a href="logout.php" class="nav-right">Logout</a>
            </nav>
        </div>
    </header>

    <main class="page">
        <div class="card glass">
            <h2>Trip status</h2>
            <p class="muted"><?php echo htmlspecialchars($message); ?></p>

            <?php if (!empty($route_text)): ?>
                <div class="summary mt-16">
                    <div class="summary-item">
                        <div class="label">Route</div>
                        <div class="value"><?php echo htmlspecialchars($route_text); ?></div>
                    </div>
                    <?php if (!empty($transport_text)): ?>
                        <div class="summary-item">
                            <div class="label">Transport</div>
                            <div class="value"><?php echo htmlspecialchars($transport_text); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($guide_text)): ?>
                        <div class="summary-item">
                            <div class="label">Guide</div>
                            <div class="value"><?php echo htmlspecialchars($guide_text); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="actions mt-16">
                <a class="btn btn-primary" href="index.php">Book Another Trip</a>
            </div>
        </div>
    </main>
</body>
</html>
