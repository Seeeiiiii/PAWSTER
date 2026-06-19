<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/config/app.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/controllers/navbar_mode_handler.php';

$current_userid   = $_SESSION['auth_user']['userid'] ?? 0;
$current_fullname = $_SESSION['auth_user']['first_name'] . ' ' . $_SESSION['auth_user']['last_name'] ?? 'Guest User';
$is_logged_in     = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'save_appt') {
    header('Content-Type: application/json');

    // Grab raw JSON payload from the request body
    $inputData = file_get_contents('php://input');
    $booking = json_decode($inputData, true);

    if ($booking) {
        $db = new DatabaseConnection();
        $conn = $db->conn;

        // Escape input strings and parse parameters dynamically
        $userid  = intval($booking['userid']);
        $service = $conn->real_escape_string($booking['service']);
        $pet     = $conn->real_escape_string($booking['pet']);

        // Handle conversion of human-readable dates if date_raw is unavailable
        $date_raw = !empty($booking['date_raw']) ? $booking['date_raw'] : date("Y-m-d", strtotime($booking['date']));
        $date    = $conn->real_escape_string($date_raw);
        $time    = $conn->real_escape_string($booking['time']);
        $status  = 'Pending';

        // Amount paid: comes from the selected service's fee on the front end.
        // Falls back to 150 if the client didn't send one, so older calls don't break.
        $amount_paid = isset($booking['fee']) ? floatval($booking['fee']) : 150.00;

        // SQL Query utilizing dynamic parameters
        $query = "INSERT INTO tblappointment (userid, service_type, date, select_pet, available_time, status, amount_paid) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("isssssd", $userid, $service, $date, $pet, $time, $status, $amount_paid);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database execution failed: " . $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid or empty data received."]);
    }
    exit; // Stop executing to prevent HTML pollution inside the JSON pipeline response
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_booked_slots') {
    header('Content-Type: application/json');

    $date = $_GET['date'] ?? '';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        echo json_encode(["status" => "error", "message" => "Invalid date."]);
        exit;
    }

    $db = new DatabaseConnection();
    $conn = $db->conn;

    $query = "SELECT available_time FROM tblappointment WHERE date = ? AND status IN ('Approved', 'Confirmed')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $bookedTimes = [];
    while ($row = $result->fetch_assoc()) {
        $formatted = date("g:i a", strtotime($row['available_time']));
        $bookedTimes[] = $formatted;
    }

    echo json_encode(["status" => "success", "booked" => $bookedTimes]);
    $stmt->close();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/headlinks.php'); ?>
    <link rel="stylesheet" href="resources/css/grooming.css">
    <link rel="stylesheet" href="resources/css/global.css">

    <!-- Safely pass dynamic global session details to your JavaScript file -->
    <script>
        const AUTH_USER_CONTEXT = {
            id: <?= json_encode($current_userid) ?>,
            name: <?= json_encode($current_fullname) ?>,
            isLoggedIn: <?= json_encode($is_logged_in) ?>
        };
    </script>
    <script src="resources/js/grooming.js"></script>

    <style>
        /* ── Cancellation / lateness reminder styles ── */
        .policy-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .policy-modal-box {
            background: #fff;
            max-width: 480px;
            width: 100%;
            border-radius: 14px;
            padding: 28px 26px 22px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
            position: relative;
        }

        .policy-modal-box h4 {
            margin: 0 0 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .policy-modal-box ul {
            margin: 0 0 18px;
            padding-left: 20px;
        }

        .policy-modal-box li {
            margin-bottom: 10px;
            line-height: 1.4;
            font-size: 0.95rem;
        }

        .policy-modal-close-btn {
            background: #AB8154;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 18px;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
        }

        .policy-modal-close-btn:hover {
            background: #AB8154;
        }

        .policy-banner {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: #fff7e6;
            border: 1px solid #f3d9a4;
            border-radius: 10px;
            padding: 12px 16px;
            margin: 0 0 16px;
            font-size: 0.9rem;
            color: #6b5320;
        }

        .policy-banner strong {
            color: #4a3a13;
        }

        .policy-banner-icon {
            font-size: 1.1rem;
            line-height: 1;
        }
    </style>
</head>

<body>

    <!-- ── Cancellation / lateness policy modal (shown once per session) ── -->
    <div class="policy-modal-overlay" id="policy-modal-overlay" style="display:none;">
        <div class="policy-modal-box">
            <h4>📋 Before you book</h4>
            <ul>
                <li><strong>Cancellations</strong> must be made at least <strong>24 hours</strong> before your scheduled appointment time to avoid a cancellation charge.</li>
                <li>Cancelling with less than 24 hours' notice may forfeit part or all of your service fee.</li>
                <li>If you arrive <strong>more than 15 minutes late</strong>, your slot may be given to the next client and may need to be rescheduled.</li>
                <li>No-shows without prior notice may affect your ability to book future appointments.</li>
            </ul>
            <button class="policy-modal-close-btn" onclick="closePolicyModal()">Got it, continue booking</button>
        </div>
    </div>

    <div class="curve">
        <header>
            <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/navbarlight.php'); ?>
        </header>

        <div class="title text-center">
            <img src="resources/images/appointment.png" alt="appointment" style="width: 80px; height: 60px;">
            <h2>Book an Appointment</h2>
            <h3>Schedule grooming, vet check-ups & meet-and-greet for adoptions</h3>
        </div>
    </div>

    <div class="booking-section">
        <div class="container py-4">


            <div class="row g-3 mb-3">

                <div class="col-md-4">
                    <div class="booking-card h-100">
                        <p class="card-section-title">Service type:</p>
                        <div class="row g-2">
                            <div class="col-6">
                                <button class="svc-btn w-100 active" data-service="Grooming" data-fee="150" onclick="selectService(this)">
                                    <img src="resources/images/groom icon.png" alt="Grooming" class="svc-img">
                                    <span class="svc-label">Grooming</span>
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="svc-btn w-100" data-service="Vet check-up" data-fee="150" onclick="selectService(this)">
                                    <img src="resources/images/check-up icon.png" alt="Vet check-up" class="svc-img">
                                    <span class="svc-label">Vet check-up</span>
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="svc-btn w-100" data-service="Vaccination" data-fee="150" onclick="selectService(this)">
                                    <img src="resources/images/vaccine icon.png" alt="Vaccination" class="svc-img">
                                    <span class="svc-label">Vaccination</span>
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="svc-btn w-100" data-service="Meet & greet" data-fee="150" onclick="selectService(this)">
                                    <img src="resources/images/heart icon.png" alt="Meet & greet" class="svc-img">
                                    <span class="svc-label">Meet &amp; greet</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="booking-card h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="card-section-title mb-0" id="cal-month-label">Pick a date – June 2026</p>
                            <div class="d-flex gap-2">
                                <button class="cal-nav-btn" onclick="prevMonth()"><i class="bi bi-chevron-left"></i></button>
                                <button class="cal-nav-btn" onclick="nextMonth()"><i class="bi bi-chevron-right"></i></button>
                            </div>
                        </div>
                        <div class="cal-grid" id="cal-grid"></div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">

                <div class="col-md-4">
                    <div class="booking-card h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <p class="card-section-title mb-0">Select pet:</p>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-3" id="pet-list">
                            <button class="pet-pill active" data-pet="Dog" onclick="selectPet(this)">
                                <span class="pet-pill-icon">🐶</span> Dog
                            </button>
                            <button class="pet-pill" data-pet="Cat" onclick="selectPet(this)">
                                <span class="pet-pill-icon">🐱</span> Cat
                            </button>
                            <button class="pet-pill" data-pet="Bird" onclick="selectPet(this)">
                                <span class="pet-pill-icon">🐦</span> Bird
                            </button>
                        </div>

                        <textarea class="notes-area w-100 mt-2" placeholder="Additional notes" id="booking-notes" rows="3"></textarea>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="booking-card h-100">
                        <p class="card-section-title" id="slots-label">Available time slots</p>
                        <div class="d-flex flex-wrap gap-2" id="slots-container"></div>
                    </div>
                </div>
            </div>

            <div class="booking-card summary-card">
                <p class="summary-title text-center fw-bold mb-3">Application Summary</p>
                <div class="row">
                    <div class="col-md-6">
                        <div class="summary-table">
                            <div class="summary-row">
                                <span class="s-key">Service</span>
                                <span class="s-val" id="s-service">Grooming</span>
                            </div>
                            <!-- Displays the active logged-in user profile name dynamically -->
                            <div class="summary-row">
                                <span class="s-key">Booked by</span>
                                <span class="s-val" id="s-booked-by"><?= htmlspecialchars($current_fullname) ?></span>
                            </div>
                            <div class="summary-row">
                                <span class="s-key">Date &amp; time</span>
                                <span class="s-val" id="s-datetime">–</span>
                            </div>
                            <div class="summary-row">
                                <span class="s-key">Pet</span>
                                <span class="s-val" id="s-pet">Dog</span>
                            </div>
                            <div class="summary-row">
                                <span class="s-key">Location</span>
                                <span class="s-val">PAWSTER</span>
                            </div>
                            <div class="summary-row">
                                <span class="s-key">Service fee</span>
                                <span class="s-val" id="s-fee">PHP 150</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex flex-column justify-content-between">

                        <!-- Payment Method -->
                        <p class="card-section-title mt-1 mb-2">Payment method:</p>
                        <div class="d-flex gap-2 mb-2">
                            <button class="pay-btn active" id="pay-btn-card" onclick="selectPayment('card')">
                                <span class="pay-icon">💳</span> Card
                            </button>
                            <button class="pay-btn" id="pay-btn-gcash" onclick="selectPayment('gcash')">
                                <span class="pay-icon">
                                    <img src="resources/images/gcash.png"
                                        alt="GCash" style="height:16px;vertical-align:middle;">
                                </span> Gcash
                            </button>
                        </div>

                        <!-- Card Dropdown -->
                        <div class="pay-dropdown" id="pay-dropdown-card">
                            <div class="pay-field">
                                <label>Cardholder Name</label>
                                <input type="text" id="card-name" placeholder="Juan dela Cruz" autocomplete="cc-name">
                            </div>
                            <div class="pay-field">
                                <label>Card Number</label>
                                <input type="text" id="card-number" placeholder="•••• •••• •••• ••••" maxlength="19" oninput="formatCardNumber(this)" autocomplete="cc-number">
                            </div>
                            <div class="d-flex gap-2">
                                <div class="pay-field flex-fill">
                                    <label>Expiry</label>
                                    <input type="text" id="card-expiry" placeholder="MM / YY" maxlength="7" oninput="formatExpiry(this)" autocomplete="cc-exp">
                                </div>
                                <div class="pay-field" style="width:90px;">
                                    <label>CVV</label>
                                    <input type="password" id="card-cvv" placeholder="•••" maxlength="4" autocomplete="cc-csc">
                                </div>
                            </div>
                        </div>

                        <!-- GCash Dropdown -->
                        <div class="pay-dropdown" id="pay-dropdown-gcash" style="display:none;">
                            <div class="pay-field">
                                <label>GCash Number</label>
                                <input type="tel" id="gcash-number" placeholder="09XX XXX XXXX" maxlength="13" oninput="formatGcash(this)">
                            </div>
                            <div class="pay-field">
                                <label>Account Name</label>
                                <input type="text" id="gcash-name" placeholder="Full name on GCash">
                            </div>
                        </div>

                        <button class="confirm-btn w-100 mt-3" onclick="confirmBooking()">Confirm booking</button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/footer.php'); ?>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    function closePolicyModal() {
        document.getElementById('policy-modal-overlay').style.display = 'none';
    }

    (function () {
        document.getElementById('policy-modal-overlay').style.display = 'flex';
    })();
</script>
</body>

</html>