<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/config/app.php';

// Initialize the session handler dynamically if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. MATCH YOUR NAVBAR: Dynamically read from the multi-dimensional auth arrays
$current_userid   = $_SESSION['auth_user']['userid'] ?? 0;
$current_fullname = $_SESSION['auth_user']['first_name'] . ' ' . $_SESSION['auth_user']['last_name'] ?? 'Guest User';
$is_logged_in     = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;

// ── BACKEND API ROUTER FOR SAVING APPOINTMENTS ──
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

        // SQL Query utilizing dynamic parameters
        $query = "INSERT INTO tblappointment (userid, service_type, date, select_pet, available_time, status) 
                  VALUES (?, ?, ?, ?, ?, ?)";
                  
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isssss", $userid, $service, $date, $pet, $time, $status);

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
</head>

<body>

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
                                <button class="svc-btn w-100 active" data-service="Grooming" data-fee="450" onclick="selectService(this)">
                                    <img src="resources/images/groom icon.png" alt="Grooming" class="svc-img">
                                    <span class="svc-label">Grooming</span>
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="svc-btn w-100" data-service="Vet check-up" data-fee="600" onclick="selectService(this)">
                                    <img src="resources/images/check-up icon.png" alt="Vet check-up" class="svc-img">
                                    <span class="svc-label">Vet check-up</span>
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="svc-btn w-100" data-service="Vaccination" data-fee="350" onclick="selectService(this)">
                                    <img src="resources/images/vaccine icon.png" alt="Vaccination" class="svc-img">
                                    <span class="svc-label">Vaccination</span>
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="svc-btn w-100" data-service="Meet & greet" data-fee="200" onclick="selectService(this)">
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
                            <p class="card-section-title mb-0" id="cal-month-label">Pick a date – May 2026</p>
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
                        </div>
                    </div>
                    <div class="col-md-6 d-flex flex-column justify-content-between">
                        <div class="summary-table">
                            <div class="summary-row">
                                <span class="s-kaey">Service fee</span>
                                <span class="s-val" id="s-fee">PHP 450</span>
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
</body>

</html>