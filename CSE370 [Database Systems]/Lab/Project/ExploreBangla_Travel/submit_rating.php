<?php
require_once('DBConnect.php');
require_once('auth.php');

require_login();
$u = current_user();
$user_id = (int)($u['user_id'] ?? 0);

// Only travellers can rate
if (($u['user_type'] ?? '') === 'admin') {
    http_response_code(403);
    echo "Admins cannot submit ratings.";
    exit;
}

$booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$feedback = isset($_POST['feedback']) ? trim($_POST['feedback']) : '';

if ($booking_id <= 0) {
    http_response_code(400);
    echo "Invalid booking.";
    exit;
}
if ($rating < 1 || $rating > 5) {
    http_response_code(400);
    echo "Rating must be between 1 and 5.";
    exit;
}
if (mb_strlen($feedback) > 1000) {
    http_response_code(400);
    echo "Feedback too long (max 1000 characters).";
    exit;
}

// Verify booking belongs to user and is confirmed
$stmt = $conn->prepare("SELECT booking_id, user_id, route_id, transport_type_id, status
                        FROM bookings
                        WHERE booking_id=? LIMIT 1");
$stmt->bind_param('i', $booking_id);
$stmt->execute();
$bk = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$bk) {
    http_response_code(404);
    echo "Booking not found.";
    exit;
}
if ((int)$bk['user_id'] !== $user_id) {
    http_response_code(403);
    echo "You can only rate your own booking.";
    exit;
}
if (($bk['status'] ?? '') !== 'Confirmed') {
    http_response_code(400);
    echo "Only confirmed bookings can be rated.";
    exit;
}

// Prevent duplicates (one rating per booking)
$stmt = $conn->prepare("SELECT rating_id FROM trip_ratings WHERE booking_id=? LIMIT 1");
$stmt->bind_param('i', $booking_id);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existing) {
    header("Location: payment.php?booking_id=".$booking_id."&rated=already");
    exit;
}

$stmt = $conn->prepare("INSERT INTO trip_ratings (booking_id, user_id, route_id, transport_type_id, rating, feedback)
                        VALUES (?,?,?,?,?,?)");
$route_id = (int)$bk['route_id'];
$transport_type_id = (int)$bk['transport_type_id'];
$stmt->bind_param('iiiiss', $booking_id, $user_id, $route_id, $transport_type_id, $rating, $feedback);
if (!$stmt->execute()) {
    $stmt->close();
    http_response_code(500);
    echo "Failed to save rating.";
    exit;
}
$stmt->close();

header("Location: payment.php?booking_id=".$booking_id."&rated=1");
exit;
?>