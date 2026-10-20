<?php
require_once('DBConnect.php');
require_once('auth.php');

require_login();
$u = current_user();

$is_view_only = false;
$rating_existing = null;
$rated_flag = isset($_GET['rated']) ? $_GET['rated'] : null;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET' && isset($_GET['booking_id'])) {
    $is_view_only = true;
    $booking_id = (int)$_GET['booking_id'];

    if ($booking_id <= 0) {
        http_response_code(400);
        echo "Invalid booking.";
        exit;
    }

    // Load booking summary from DB
    $stmt = $conn->prepare("
        SELECT b.booking_id, b.user_id, b.route_id, b.transport_type_id, b.travel_class, b.journey_gender, b.seat_count, b.booking_date, b.status,
               r.source AS route_source, r.destination AS route_destination,
               t.type_name AS transport_name,
               g.name AS guide_name, g.phone AS guide_phone
        FROM bookings b
        JOIN routes r ON b.route_id = r.route_id
        JOIN transport_types t ON b.transport_type_id = t.transport_type_id
        LEFT JOIN guides g ON b.guide_id = g.GID
        WHERE b.booking_id = ?
        LIMIT 1
    ");
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $bk = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$bk) {
        http_response_code(404);
        echo "Booking not found.";
        exit;
    }

    // Only owner or admin can view
    $uid = (int)($u['user_id'] ?? 0);
    if (($u['user_type'] ?? '') !== 'admin' && (int)$bk['user_id'] !== $uid) {
        http_response_code(403);
        echo "You are not allowed to view this booking.";
        exit;
    }

    $route_id = (int)$bk['route_id'];
    $transport_type_id = (int)$bk['transport_type_id'];
    $travel_class = (string)$bk['travel_class'];
    $journey_gender = (string)$bk['journey_gender'];
    $transport_name = (string)$bk['transport_name'];
    $route_source = (string)$bk['route_source'];
    $route_destination = (string)$bk['route_destination'];
    $booking_status = (string)$bk['status'];
    $route_name = $route_source . " → " . $route_destination;

    $guide_name = $bk['guide_name'] ?? null;
    $guide_phone = $bk['guide_phone'] ?? null;

    $seat_codes = [];
    $stmt = $conn->prepare("SELECT seat_code FROM booking_seats WHERE booking_id=? ORDER BY id ASC");
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $seat_codes[] = $row['seat_code'];
    }
    $stmt->close();

    // Payment info (latest)
    $stmt = $conn->prepare("SELECT amount, currency, payment_method, status FROM payments WHERE booking_id=? ORDER BY payment_id DESC LIMIT 1");
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $amount = isset($p['amount']) ? (float)$p['amount'] : 0.0;
    $currency = $p['currency'] ?? 'BDT';
    $payment_method = $p['payment_method'] ?? '';
    $payment_status = $p['status'] ?? '';

    // Existing rating (if any)
    $stmt = $conn->prepare("SELECT rating, feedback FROM trip_ratings WHERE booking_id=? LIMIT 1");
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $rating_existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (!$is_view_only) {
$route_id = isset($_POST['route_id']) ? (int)$_POST['route_id'] : 0;
$transport_type_id = isset($_POST['transport_type_id']) ? (int)$_POST['transport_type_id'] : 0;
$travel_class = isset($_POST['travel_class']) ? trim($_POST['travel_class']) : '';
$journey_gender = isset($_POST['journey_gender']) ? trim($_POST['journey_gender']) : 'Mixed';
$seat_codes_raw = isset($_POST['seat_codes']) ? trim($_POST['seat_codes']) : '';
$payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';

$valid_class = in_array($travel_class, ['Economy','Business'], true);
$valid_gender = in_array($journey_gender, ['Mixed','All Male','All Female'], true);

// Parse selected seats (1 to 6)
$seat_codes = array_values(array_filter(array_map('trim', explode(',', $seat_codes_raw))));
$seat_codes = array_values(array_unique($seat_codes));
$seat_count = count($seat_codes);


if ($route_id <= 0 || $transport_type_id <= 0 || !$valid_class || !$valid_gender || $seat_count < 1 || $seat_count > 6 || $payment_method === '') {
    http_response_code(400);
    echo "Invalid request. Please go back and try again.";
    exit;
}

// Validate each seat code (row 1-15, column A-F)
foreach ($seat_codes as $sc) {
    if (!preg_match('/^[0-9]{1,2}[A-F]$/', $sc)) {
        http_response_code(400);
        echo "Invalid seat code: " . htmlspecialchars($sc);
        exit;
    }
}

// Recompute fare server-side to prevent tampering
$stmt = $conn->prepare("SELECT amount, currency FROM route_fares WHERE route_id=? AND transport_type_id=? AND travel_class=? LIMIT 1");
$stmt->bind_param('iis', $route_id, $transport_type_id, $travel_class);
$stmt->execute();
$fare = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$fare || (float)$fare['amount'] <= 0) {
    http_response_code(400);
    echo "Fare not configured for this selection.";
    exit;
}

$amount = (float)$fare['amount']; // per-seat fare
$currency = $fare['currency'] ?: 'BDT';

$total_amount = $amount * $seat_count;

$conn->begin_transaction();

try {
    // Ensure seats aren't already booked (Confirmed + Completed payment)
foreach ($seat_codes as $sc) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS c
        FROM booking_seats bs
        JOIN bookings b ON b.booking_id = bs.booking_id
        JOIN payments p ON p.booking_id = b.booking_id
        WHERE b.route_id=? AND b.transport_type_id=? AND b.travel_class=?
          AND bs.seat_code=? AND b.status='Confirmed' AND p.status='Completed'
        LIMIT 1
    ");
    $stmt->bind_param('iiss', $route_id, $transport_type_id, $travel_class, $sc);
    $stmt->execute();
    $c = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
    $stmt->close();

    if ($c > 0) {
        throw new Exception("Sorry, one of your selected seats ($sc) is already booked. Please go back and select different seats.");
    }
}

// Find assigned guide for this route+transport (traveler doesn't choose)
$guide_id = null;
$stmt = $conn->prepare("SELECT GID FROM route_guides WHERE route_id=? AND transport_type_id=? LIMIT 1");
$stmt->bind_param('ii', $route_id, $transport_type_id);
$stmt->execute();
$rowg = $stmt->get_result()->fetch_assoc();
$guide_id = $rowg['GID'] ?? null;
$stmt->close();


// If no mapping exists, fallback-assign the least busy guide (so every trip has a guide)
if (empty($guide_id)) {
    $stmt = $conn->prepare("SELECT GID FROM guides ORDER BY trips_done ASC, GID ASC LIMIT 1");
    $stmt->execute();
    $rowg2 = $stmt->get_result()->fetch_assoc();
    $guide_id = $rowg2['GID'] ?? null;
    $stmt->close();
}

// Fetch guide details for display
$guide_name = null;
$guide_phone = null;
if (!empty($guide_id)) {
    $stmt = $conn->prepare("SELECT name, phone FROM guides WHERE GID = ? LIMIT 1");
    $stmt->bind_param('i', $guide_id);
    $stmt->execute();
    $gd = $stmt->get_result()->fetch_assoc();
    $guide_name = $gd['name'] ?? null;
    $guide_phone = $gd['phone'] ?? null;
    $stmt->close();
}

// Insert booking

    $stmt = $conn->prepare("
        INSERT INTO bookings (
            user_id, route_id, transport_type_id, travel_class, journey_gender, guide_id, seat_count, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Confirmed')
    ");
    $uid = (int)($u['user_id'] ?? 0);
    // guide_id can be NULL
    $stmt->bind_param('iiissii', $uid, $route_id, $transport_type_id, $travel_class, $journey_gender, $guide_id, $seat_count);
    $stmt->execute();
    $booking_id = $stmt->insert_id;
    $stmt->close();
// Insert seats for this booking
$stmt = $conn->prepare("INSERT INTO booking_seats (booking_id, seat_code) VALUES (?, ?)");
foreach ($seat_codes as $sc) {
    $stmt->bind_param('is', $booking_id, $sc);
    $stmt->execute();
}
$stmt->close();

// Increment guide trip count (once per booking)
if (!empty($guide_id)) {
    $stmt = $conn->prepare("UPDATE guides SET trips_done = trips_done + 1 WHERE GID = ?");
    $stmt->bind_param('i', $guide_id);
    $stmt->execute();
    $stmt->close();
}


    // Insert payment (demo: completed)
    $stmt = $conn->prepare("
        INSERT INTO payments (booking_id, amount, currency, payment_method, status)
        VALUES (?, ?, ?, ?, 'Completed')
    ");
    $stmt->bind_param('idss', $booking_id, $total_amount, $currency, $payment_method);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo $e->getMessage();
    exit;
}

// Fetch booking summary
$stmt = $conn->prepare("SELECT origin, destination FROM routes WHERE route_id=? LIMIT 1");
$stmt->bind_param('i', $route_id);
$stmt->execute();
$route = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("SELECT transport_name FROM transport_types WHERE transport_type_id=? LIMIT 1");
$stmt->bind_param('i', $transport_type_id);
$stmt->execute();
$transport = $stmt->get_result()->fetch_assoc();
$stmt->close();

$route_label = ($route ? ($route['origin'].' → '.$route['destination']) : 'Route');
$transport_name = ($transport ? $transport['transport_name'] : 'Transport');

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - ExploreBangla Travel</title>

    <style>
        .rating-card { margin-top: 18px; }
        .stars { display: inline-flex; flex-direction: row-reverse; gap: 6px; }
        .stars input { display:none; }
        .stars label { cursor:pointer; font-size: 26px; line-height: 1; color: rgba(255,255,255,0.45); text-shadow: 0 1px 0 rgba(0,0,0,0.35); }
        .stars input:checked ~ label,
        .stars label:hover,
        .stars label:hover ~ label { color: #ffd166; }
        .feedback-box { width: 100%; min-height: 90px; resize: vertical; }
        .notice { padding: 10px 12px; border-radius: 12px; margin: 12px 0; }
        .notice.ok { background: rgba(46, 204, 113, 0.15); border: 1px solid rgba(46, 204, 113, 0.35); }
        .notice.warn { background: rgba(255, 209, 102, 0.12); border: 1px solid rgba(255, 209, 102, 0.35); }
    </style>

</head>
<body class="with-video">
    <video class="bg-video" autoplay muted loop playsinline>
        <source src="assets/travel.mp4" type="video/mp4">
    </video>
    <div class="bg-overlay"></div>

    <header class="topbar glass">
        <div class="topbar-inner">
            <div class="brand">ExploreBangla</div>
            <nav class="nav">
                <a href="index.php">Book</a>
                <?php if (($u['user_type'] ?? '') === 'admin'): ?>
                    <a href="admin/dashboard.php">Admin</a>
                <?php endif; ?>
                <a href="logout.php" class="nav-right">Logout</a>
            </nav>
        </div>
    </header>

    <main class="page">
        <div class="card glass">
            <div class="success">
                <div class="success-icon">✅</div>
                <div>
                    <h2>Booking Confirmed!</h2>
                    <p class="muted">Your seat has been reserved successfully.</p>
                </div>
            </div>

            <div class="summary-grid">
                <div class="summary-item">
                    <div class="label">Route</div>
                    <div class="value"><?php echo htmlspecialchars($route_label); ?></div>
                </div>
                <div class="summary-item">
                    <div class="label">Transport</div>
                    <div class="value"><?php echo htmlspecialchars($transport_name); ?></div>
                </div>
                <div class="summary-item">
                    <div class="label">Class</div>
                    <div class="value"><?php echo htmlspecialchars($travel_class); ?></div>
                </div>
                <div class="summary-item">
                    <div class="label">Journey</div>
                    <div class="value"><?php echo htmlspecialchars($journey_gender); ?></div>
                </div>
                <div class="summary-item">
                    <div class="label">Seat</div>
                    <div class="value"><?php echo htmlspecialchars(implode(', ', $seat_codes)); ?></div>
                </div>
                
                <div class="summary-item">
                    <div class="label">Guide</div>
                    <div class="value">
                        <?php if (!empty($guide_name)): ?>
                            <?php echo htmlspecialchars($guide_name); ?>
                            <?php if (!empty($guide_phone)): ?>
                                <span class="muted"> (<?php echo htmlspecialchars($guide_phone); ?>)</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="muted">Assigned at check-in</span>
                        <?php endif; ?>
                    </div>
                </div>
<div class="summary-item">
                    <div class="label">Amount</div>
                    <div class="value"><?php echo "৳" . number_format($total_amount, 2); ?></div>
                </div>
                <div class="summary-item">
                    <div class="label">Method</div>
                    <div class="value"><?php echo htmlspecialchars($payment_method); ?></div>
                </div>
                <div class="summary-item">
                    <div class="label">Status</div>
                    <div class="value">Completed</div>
                </div>
            </div>

            
            <?php
                // Rating UI (traveller only, confirmed booking only)
                $can_rate = (($u['user_type'] ?? '') !== 'admin') && (($booking_status ?? 'Confirmed') === 'Confirmed');
                if (!isset($booking_status)) {
                    // In POST flow booking status is always Confirmed on success
                    $booking_status = 'Confirmed';
                }

                // Fetch existing rating in POST-flow too
                if ($can_rate && empty($rating_existing) && !empty($booking_id)) {
                    $stmt = $conn->prepare("SELECT rating, feedback FROM trip_ratings WHERE booking_id=? LIMIT 1");
                    $stmt->bind_param('i', $booking_id);
                    $stmt->execute();
                    $rating_existing = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                }
            ?>

            <?php if (!empty($rated_flag) && $rated_flag === '1'): ?>
                <div class="notice ok">Thanks! Your rating has been submitted.</div>
            <?php elseif (!empty($rated_flag) && $rated_flag === 'already'): ?>
                <div class="notice warn">You already rated this trip.</div>
            <?php endif; ?>

            <?php if ($can_rate): ?>
                <div class="card glass rating-card">
                    <h3 class="title" style="margin-bottom:10px;">Rate Your Trip</h3>

                    <?php if (!empty($rating_existing)): ?>
                        <div class="muted">You rated this trip: <strong><?php echo (int)$rating_existing['rating']; ?>/5</strong></div>
                        <?php if (!empty($rating_existing['feedback'])): ?>
                            <p style="margin-top:10px;"><?php echo nl2br(htmlspecialchars($rating_existing['feedback'])); ?></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <form action="submit_rating.php" method="POST">
                            <input type="hidden" name="booking_id" value="<?php echo (int)$booking_id; ?>">

                            <div style="margin-bottom:10px;">
                                <div class="label" style="margin-bottom:6px;">Stars</div>
                                <div class="stars" aria-label="5 star rating">
                                    <input type="radio" id="star5" name="rating" value="5" required><label for="star5" title="5 stars">★</label>
                                    <input type="radio" id="star4" name="rating" value="4"><label for="star4" title="4 stars">★</label>
                                    <input type="radio" id="star3" name="rating" value="3"><label for="star3" title="3 stars">★</label>
                                    <input type="radio" id="star2" name="rating" value="2"><label for="star2" title="2 stars">★</label>
                                    <input type="radio" id="star1" name="rating" value="1"><label for="star1" title="1 star">★</label>
                                </div>
                            </div>

                            <div style="margin-bottom:12px;">
                                <div class="label" style="margin-bottom:6px;">Feedback</div>
                                <textarea class="feedback-box" name="feedback" maxlength="1000" placeholder="Write your feedback (optional)"></textarea>
                                <div class="muted" style="margin-top:6px;">Max 1000 characters.</div>
                            </div>

                            <button class="btn btn-primary" type="submit">Submit Rating</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

<div class="actions mt-16">
                <a class="btn btn-primary" href="index.php">Book Another Trip</a>
                
                <form action="cancel_booking.php" method="POST" style="display:inline;">
                    <input type="hidden" name="booking_id" value="<?php echo (int)$booking_id; ?>">
                    <button class="btn btn-danger" type="submit" onclick="return confirm('Cancel this trip? This action cannot be undone.');">
                        Cancel Trip
                    </button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>