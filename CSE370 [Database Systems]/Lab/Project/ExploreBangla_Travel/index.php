<?php
require_once('DBConnect.php');
require_once('auth.php');

require_login();
$u = current_user();

// Load dropdown data
$transport_types = mysqli_query($conn, "SELECT transport_type_id, transport_name FROM transport_types ORDER BY transport_name ASC");
$routes = mysqli_query($conn, "SELECT route_id, origin, destination FROM routes ORDER BY origin ASC, destination ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExploreBangla Travel - Book a Trip</title>
</head>
<body class="with-video">
    <!-- Background video (add your own mp4 file at /assets/travel.mp4) -->
    <video class="bg-video" autoplay muted loop playsinline>
        <source src="assets/travel.mp4" type="video/mp4">
    </video>
    <div class="bg-overlay"></div>

    <header class="topbar glass">
        <div class="topbar-inner">
            <div class="brand">ExploreBangla</div>
            <nav class="nav">
                <a href="index.php" class="active">Book</a>
                <?php if (($u['user_type'] ?? '') === 'admin'): ?>
                    <a href="admin/dashboard.php">Admin</a>
                <?php endif; ?>
                <a href="logout.php" class="nav-right">Logout</a>
            </nav>
        </div>
    </header>

    <main class="page">
        <div class="hero">
            <div class="hero-card glass card">
                <div class="hero-badge">Bangladesh • Routes • Seats</div>
                <h1>Plan your next journey</h1>
                <p class="muted">Pick transport, destination, class, and journey preference — then choose your seat.</p>

                <form action="booking.php" method="POST" class="form-grid">
                    <div class="field">
                        <label for="transport_type_id">Transport</label>
                        <select name="transport_type_id" id="transport_type_id" required>
                            <option value="" disabled selected>Select transport</option>
                            <?php while($t = mysqli_fetch_assoc($transport_types)): ?>
                                <option value="<?php echo (int)$t['transport_type_id']; ?>"><?php echo htmlspecialchars($t['transport_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="field">
                        <label for="route_id">Destination</label>
                        <select name="route_id" id="route_id" required>
                            <option value="" disabled selected>Select route</option>
                            <?php while($r = mysqli_fetch_assoc($routes)): ?>
                                <option value="<?php echo (int)$r['route_id']; ?>">
                                    <?php echo htmlspecialchars($r['origin'] . " → " . $r['destination']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="field">
                        <label for="travel_class">Class</label>
                        <select name="travel_class" id="travel_class" required>
                            <option value="Economy">Economy</option>
                            <option value="Business">Business Class</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="journey_gender">Journey Option</label>
                        <select name="journey_gender" id="journey_gender" required>
                            <option value="Mixed">Mixed</option>
                            <option value="All Male">All Male</option>
                            <option value="All Female">All Female</option>
                        </select>
                    </div>

                    <div class="actions">
                        <button type="submit" class="btn btn-primary">Continue to Seat Selection</button>
                    </div>
                </form>

                <div class="tips">
                    <div class="tip">
                        <div class="tip-title">🇧🇩 Currency</div>
                        <div class="tip-text">Prices are shown in Bangladeshi Taka (৳).</div>
                    </div>
                    <div class="tip">
                        <div class="tip-title">🪑 Seat Layout</div>
                        <div class="tip-text">Bus, Train, and Plane have different seat maps.</div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer glass">
        <span>© <?php echo date('Y'); ?> ExploreBangla Travel</span>
        <span class="muted">Made for modern journeys ✈️🚌🚆</span>
    </footer>
</body>
</html>
