<?php
require_once('DBConnect.php');
require_once('auth.php');

require_login();
$u = current_user();

$route_id = isset($_POST['route_id']) ? (int)$_POST['route_id'] : 0;
$transport_type_id = isset($_POST['transport_type_id']) ? (int)$_POST['transport_type_id'] : 0;
$travel_class = isset($_POST['travel_class']) ? trim($_POST['travel_class']) : '';
$journey_gender = isset($_POST['journey_gender']) ? trim($_POST['journey_gender']) : 'Mixed';

$valid_class = in_array($travel_class, ['Economy','Business'], true);
$valid_gender = in_array($journey_gender, ['Mixed','All Male','All Female'], true);

if ($route_id <= 0 || $transport_type_id <= 0 || !$valid_class || !$valid_gender) {
    header('Location: index.php');
    exit;
}

// Route + transport names
$stmt = $conn->prepare("SELECT origin, destination FROM routes WHERE route_id = ? LIMIT 1");
$stmt->bind_param('i', $route_id);
$stmt->execute();
$route = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("SELECT transport_name FROM transport_types WHERE transport_type_id = ? LIMIT 1");
$stmt->bind_param('i', $transport_type_id);
$stmt->execute();
$transport = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$route || !$transport) {
    header('Location: index.php');
    exit;
}

// Fare lookup
$stmt = $conn->prepare("SELECT amount, currency FROM route_fares WHERE route_id = ? AND transport_type_id = ? AND travel_class = ? LIMIT 1");
$stmt->bind_param('iis', $route_id, $transport_type_id, $travel_class);
$stmt->execute();
$fare = $stmt->get_result()->fetch_assoc();
$stmt->close();

$amount = $fare ? (float)$fare['amount'] : 0.0;
$currency = $fare ? $fare['currency'] : 'BDT';

// Booked seats for this combination (Confirmed + Completed payment)
$stmt = $conn->prepare("
    SELECT bs.seat_code
    FROM booking_seats bs
    JOIN bookings b ON b.booking_id = bs.booking_id
    JOIN payments p ON p.booking_id = b.booking_id
    WHERE b.route_id = ? AND b.transport_type_id = ? AND b.travel_class = ?
      AND b.status = 'Confirmed' AND p.status = 'Completed'
");
$stmt->bind_param('iis', $route_id, $transport_type_id, $travel_class);
$stmt->execute();
$res = $stmt->get_result();
$booked = [];
while ($res && ($row = $res->fetch_assoc())) { $booked[] = $row['seat_code']; }
$stmt->close();

$booked_json = json_encode($booked);
$transport_name = $transport['transport_name'];

// Auto-assigned guide (traveler does not choose)
$guide = null;
$stmt = $conn->prepare("SELECT g.GID, g.name, g.trips_done FROM route_guides rg JOIN guides g ON g.GID = rg.GID WHERE rg.route_id = ? AND rg.transport_type_id = ? LIMIT 1");
$stmt->bind_param('ii', $route_id, $transport_type_id);
$stmt->execute();
$guide = $stmt->get_result()->fetch_assoc();
$stmt->close();

$route_label = $route['origin'] . " → " . $route['destination'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seat Selection - ExploreBangla Travel</title>
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
            <div class="row-between wrap">
                <div>
                    <h2>Choose your seat</h2>
                    <p class="muted"><?php echo htmlspecialchars($transport_name); ?> • <?php echo htmlspecialchars($route_label); ?> • <?php echo htmlspecialchars($travel_class); ?> • <?php echo htmlspecialchars($journey_gender); ?></p>
                </div>
                <div class="price-pill">
                    <span class="muted">Fare</span>
                    <span class="price"><?php echo "৳" . number_format($amount, 2); ?></span>
                </div>
            </div>

            <?php if ($amount <= 0): ?>
                <div class="alert alert-error">
                    Fare is not configured for this route/transport/class yet. Please update <b>route_fares</b> table in phpMyAdmin.
                </div>
            <?php endif; ?>

            <div class="seat-legend">
                <span class="legend-item"><span class="dot available"></span>Available</span>
                <span class="legend-item"><span class="dot booked"></span>Booked</span>
                <span class="legend-item"><span class="dot selected"></span>Selected</span>
                <span class="legend-item muted">Layout: <?php echo htmlspecialchars($transport_name); ?></span>
            </div>

            <form action="payment.php" method="POST" id="seatForm" class="mt-16">
                <input type="hidden" name="route_id" value="<?php echo (int)$route_id; ?>">
                <input type="hidden" name="transport_type_id" value="<?php echo (int)$transport_type_id; ?>">
                <input type="hidden" name="travel_class" value="<?php echo htmlspecialchars($travel_class); ?>">
                <input type="hidden" name="journey_gender" value="<?php echo htmlspecialchars($journey_gender); ?>">
                <input type="hidden" name="seat_codes" id="seat_codes" value="">
                <input type="hidden" name="seat_count" id="seat_count" value="0">
                <input type="hidden" name="amount" value="<?php echo htmlspecialchars((string)$amount); ?>">

                <div id="seatMap" class="seat-map" aria-label="Seat layout"></div>

                <div class="divider"></div>

                <div class="form-grid pay-grid">
                    <div class="field">
                        <label for="payment_method">Payment Method</label>
                        <select name="payment_method" id="payment_method" required>
                            <option value="" disabled selected>Select method</option>
                            <option value="Bkash">bKash</option>
                            <option value="Nagad">Nagad</option>
                            <option value="Card">Card</option>
                            <option value="Cash">Cash</option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Selected Seats (1–6)</label>
                        <input type="text" id="seatPreview" value="" placeholder="Choose 1 to 6 seats" readonly>
                    </div>

                    <div class="actions">
                        <button type="submit" class="btn btn-primary" <?php echo ($amount<=0?'disabled':''); ?>>Pay & Confirm (৳<?php echo number_format($amount,2); ?>)</button>
                        <a class="btn btn-ghost" href="index.php">Change Options</a>
                    </div>
                </div>
            </form>
        </div>
    </main>

<script>
const bookedSeats = <?php echo $booked_json ?: '[]'; ?>;
const transportName = <?php echo json_encode($transport_name); ?>;
const travelClass = <?php echo json_encode($travel_class); ?>;

function buildLayout(transport, tClass){
  // Different seat maps for different transports
  // Bus: 2+2 (A B aisle C D), 10 rows economy, 6 rows business
  // Train: 3+2 (A B C aisle D E), 12 rows economy, 8 rows business
  // Plane: 3+3 (A B C aisle D E F), 15 rows economy, 10 rows business
  if(transport.toLowerCase() === 'bus'){
    const rows = (tClass === 'Business') ? 6 : 10;
    return {rows, cols: ['A','B','_','C','D']};
  }
  if(transport.toLowerCase() === 'train'){
    const rows = (tClass === 'Business') ? 8 : 12;
    return {rows, cols: ['A','B','C','_','D','E']};
  }
  // plane default
  const rows = (tClass === 'Business') ? 10 : 15;
  return {rows, cols: ['A','B','C','_','D','E','F']};
}

function seatCode(row, col){
  return row + col;
}

function renderSeatMap(){
  const map = document.getElementById('seatMap');
  map.innerHTML = '';
  const layout = buildLayout(transportName, travelClass);
  map.style.setProperty('--cols', layout.cols.length);

  for(let r=1; r<=layout.rows; r++){
    for(const c of layout.cols){
      if(c === '_'){
        const gap = document.createElement('div');
        gap.className = 'aisle';
        gap.setAttribute('aria-hidden','true');
        map.appendChild(gap);
        continue;
      }
      const code = seatCode(r, c);
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'seat-tile ' + (bookedSeats.includes(code) ? 'booked' : 'available');
      btn.textContent = code;
      btn.dataset.code = code;
      btn.disabled = bookedSeats.includes(code);
      btn.onclick = () => selectSeat(code);
      map.appendChild(btn);
    }
  }
}

const MAX_SEATS = 6;
let selectedSeats = [];

function updateSelectionUI(){
  const input = document.getElementById('seat_codes');
  const count = document.getElementById('seat_count');
  const preview = document.getElementById('seatPreview');

  input.value = selectedSeats.join(',');
  count.value = String(selectedSeats.length);
  preview.value = selectedSeats.length ? selectedSeats.join(', ') : '';
}

function toggleSeat(code){
  if(bookedSeats.includes(code)) return;

  const idx = selectedSeats.indexOf(code);
  if(idx >= 0){
    selectedSeats.splice(idx, 1);
    const el = document.querySelector('.seat-tile[data-code="'+code+'"]');
    if(el) el.classList.remove('selected');
    updateSelectionUI();
    return;
  }

  if(selectedSeats.length >= MAX_SEATS){
    alert('You can select maximum ' + MAX_SEATS + ' seats.');
    return;
  }

  selectedSeats.push(code);
  const el = document.querySelector('.seat-tile[data-code="'+code+'"]');
  if(el) el.classList.add('selected');
  updateSelectionUI();
}

function selectSeat(code){ // backward-compat alias
  toggleSeat(code);
}

// Validate before submit
document.getElementById('seatForm').addEventListener('submit', function(e){
  if(selectedSeats.length < 1){
    e.preventDefault();
    alert('Please select at least 1 seat.');
  }
});

renderSeatMap();
updateSelectionUI();

</script>
</body>
</html>
