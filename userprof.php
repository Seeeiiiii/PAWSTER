<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/config/app.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Must run before any HTML — handles the seller/buyer navbar mode toggle redirect.
include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/controllers/navbar_mode_handler.php';

$current_userid   = $_SESSION['auth_user']['userid'] ?? 0;
$current_fullname = trim(($_SESSION['auth_user']['first_name'] ?? '') . ' ' . ($_SESSION['auth_user']['last_name'] ?? ''));
$current_fullname = $current_fullname ?: 'Guest User';
$is_logged_in     = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;

// ── AJAX: CANCEL APPOINTMENT (handled inline, no separate action file) ──
// The cancel modal posts here with JSON: { action: 'cancel_appointment', appointmentID, reason }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $rawInput = file_get_contents('php://input');
  $input    = json_decode($rawInput, true);

  if (is_array($input) && ($input['action'] ?? '') === 'cancel_appointment') {
    header('Content-Type: application/json');

    if (!$is_logged_in || !$current_userid) {
      echo json_encode(['success' => false, 'message' => 'You must be logged in to cancel an appointment.']);
      exit;
    }

    $apptId = (int)($input['appointmentID'] ?? 0);
    $reason = trim((string)($input['reason'] ?? ''));

    if (!$apptId || $reason === '') {
      echo json_encode(['success' => false, 'message' => 'Missing appointment ID or reason.']);
      exit;
    }

    $db = new DatabaseConnection();

    // Only cancel if it belongs to the logged-in user and isn't already in a final state
    $stmt = $db->conn->prepare(
      "UPDATE tblappointment
       SET status = 'Cancelled', cancel_reason = ?
       WHERE appointmentID = ? AND userid = ?
         AND LOWER(status) NOT IN ('cancelled', 'completed', 'rejected')"
    );
    $stmt->bind_param('sii', $reason, $apptId, $current_userid);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
      echo json_encode(['success' => true]);
    } else {
      echo json_encode([
        'success' => false,
        'message' => 'Could not cancel this appointment. It may not belong to you, or it was already cancelled/completed.'
      ]);
    }

    $stmt->close();
    exit;
  }
}

$appointments = [];
if ($is_logged_in && $current_userid) {
  $db   = new DatabaseConnection();
  $stmt = $db->conn->prepare(
    "SELECT appointmentID, service_type, select_pet, date, available_time, status
         FROM tblappointment
         WHERE userid = ?
         ORDER BY date DESC"
  );
  $stmt->bind_param("i", $current_userid);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
  }
  $stmt->close();

  $orders = [];
  $ostmt = $db->conn->prepare(
    "SELECT o.orderid, o.order_status, o.total_price, o.updated_at,
            p.brand_name, p.productimage
     FROM tblorder o
     JOIN tblorder_items oi ON oi.orderid = o.orderid
     JOIN tblsellerproduct p ON p.productid = oi.productid
     WHERE o.userid = ?
     ORDER BY o.updated_at DESC"
  );
  $ostmt->bind_param("i", $current_userid);
  $ostmt->execute();
  $ores = $ostmt->get_result();
  while ($row = $ores->fetch_assoc()) {
    $orders[] = $row;
  }
  $ostmt->close();

  // ── ADOPTION REQUESTS ──
  $adoptions = [];
  $adstmt = $db->conn->prepare(
    "SELECT ar.requestid, ar.status, ar.created_at,
            p.name AS pet_name, p.breed, p.image
     FROM tbladoptionrequest ar
     JOIN tblpets p ON p.petid = ar.petid
     WHERE ar.userid = ?
     ORDER BY ar.created_at DESC"
  );
  $adstmt->bind_param("i", $current_userid);
  $adstmt->execute();
  $adres = $adstmt->get_result();
  while ($row = $adres->fetch_assoc()) {
    $adoptions[] = $row;
  }
  $adstmt->close();


  $ustmt = $db->conn->prepare(
    "SELECT u.contact_number,
            CONCAT_WS(', ', da.secondary_address, da.barangay, da.city, da.province, da.region) AS full_address
     FROM users u
     LEFT JOIN tbldelivery_address da ON da.accountid = u.userid
     WHERE u.userid = ?
     ORDER BY da.addressid DESC
     LIMIT 1"
  );
  $ustmt->bind_param("i", $current_userid);
  $ustmt->execute();
  $ustmt->bind_result($user_contact, $user_address);
  $ustmt->fetch();
  $ustmt->close();

  // Member-since date, pulled straight from the user's account creation timestamp
  $mstmt = $db->conn->prepare("SELECT created_at FROM users WHERE userid = ? LIMIT 1");
  $mstmt->bind_param("i", $current_userid);
  $mstmt->execute();
  $mstmt->bind_result($user_created_at);
  $mstmt->fetch();
  $mstmt->close();
}

$member_since = !empty($user_created_at) ? date('F Y', strtotime($user_created_at)) : null;
$is_adopter   = !empty($adoptions);
$is_buyer     = !empty($orders);

$upcoming = array_values(array_filter(
  $appointments,
  fn($a) => strtotime($a['date']) >= strtotime('today')
));
$upcoming_preview = array_slice($upcoming, 0, 3);

// ── HELPERS ──
function pillClass(string $status): string
{
  return match (strtolower(trim($status))) {
    'approved', 'confirmed' => 'pill-blue',
    'rejected', 'cancelled' => 'pill-red',
    'scheduled'             => 'pill-tan',
    default                 => 'pill-orange',
  };
}

function initials(string $name): string
{
  $parts = array_filter(explode(' ', $name));
  return strtoupper(substr(implode('', array_map(fn($p) => $p[0], $parts)), 0, 2));
}

function isCancellable(string $status): bool
{
  return !in_array(strtolower(trim($status)), ['cancelled', 'completed', 'rejected']);
}

// Maps a service_type value (as stored in tblappointment) to the same icon
// used for that service on the booking page (grooming.php).
function serviceIcon(string $serviceType): string
{
  return match (strtolower(trim($serviceType))) {
    'grooming'      => 'groom icon.png',
    'vet check-up'  => 'check-up icon.png',
    'vaccination'   => 'vaccine icon.png',
    'meet & greet'  => 'heart icon.png',
    default         => 'appointment.png',
  };
}

// Renders one appointment row (used by both preview + full list)
function renderApptRow(array $appt): string
{
  $label       = date('M j, Y', strtotime($appt['date']));
  $cancellable = isCancellable($appt['status']);
  $id          = (int)$appt['appointmentID'];
  $iconFile    = serviceIcon($appt['service_type']);

  ob_start();
  ?>
  <div class="appt-row" data-id="<?= $id ?>">
    <div class="thumb-sq appt-thumb">
      <img src="/PAWSTER/resources/images/<?= htmlspecialchars($iconFile) ?>"
        alt="<?= htmlspecialchars($appt['service_type']) ?>">
    </div>
    <div class="row-info">
      <p class="row-name">
        <?= htmlspecialchars($appt['service_type']) ?> | <?= htmlspecialchars($appt['select_pet']) ?>
      </p>
      <p class="row-meta"><?= $label ?> | <?= htmlspecialchars($appt['available_time']) ?></p>
    </div>
    <div class="appt-right">
      <span class="pill <?= pillClass($appt['status']) ?>">
        <?= htmlspecialchars(ucfirst(strtolower($appt['status']))) ?>
      </span>
      <?php if ($cancellable): ?>
        <button class="pill pill-cancel btn-cancel-appt" data-id="<?= $id ?>">Cancel</button>
      <?php endif; ?>
    </div>
  </div>
  <?php
  return ob_get_clean();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pawster – Profile</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Caprasimo&family=Convergence&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/headlinks.php'); ?>
  <link rel="stylesheet" href="resources/css/userprof.css">
  <style>
    /* ── Appointment row: status + cancel centered as one column ── */
    .appt-row {
      display: flex;
      align-items: center;
    }

    .appt-right {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 6px;
      min-width: 92px;
      margin-left: auto;
      text-align: center;
    }

    .appt-right .pill {
      width: 100%;
      text-align: center;
      box-sizing: border-box;
    }

    /* Cancel pill — same shape/font as the other status pills,
       only the color is different (no more font/box mismatch) */
    .pill-cancel {
      border: none;
      cursor: pointer;
      background: #f8d7da;
      color: #c0392b;
    }

    .pill-cancel:hover {
      background: #f1b0b7;
    }

    /* Service-type icon inside the appointment row thumbnail */
    .appt-thumb {
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    .appt-thumb img {
      width: 60%;
      height: 60%;
      object-fit: contain;
    }

    /* ── TikTok-style cancellation modal ── */
    #cancelApptModal .modal-content {
      border-radius: 16px;
      overflow: hidden;
    }

    #cancelApptModal .modal-header {
      border-bottom: none;
      padding-bottom: 0;
    }

    #cancelApptModal .modal-title {
      font-weight: 700;
      font-size: 1.05rem;
    }

    .cancel-prompt {
      font-size: .85rem;
      color: #6b6b6b;
      margin: 0 0 .5rem;
    }

    .cancel-reasons {
      border-top: 1px solid #ececec;
    }

    .reason-option {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 13px 4px;
      border-bottom: 1px solid #ececec;
      margin: 0;
      cursor: pointer;
      font-size: .92rem;
      color: #222;
    }

    .reason-option:hover {
      background: #fafafa;
    }

    .reason-option span {
      flex: 1;
    }

    .reason-option input[type="radio"] {
      appearance: none;
      -webkit-appearance: none;
      width: 20px;
      height: 20px;
      border: 2px solid #c9c9c9;
      border-radius: 50%;
      margin: 0 0 0 12px;
      position: relative;
      cursor: pointer;
      flex-shrink: 0;
    }

    .reason-option input[type="radio"]:checked {
      border-color: #FF5A36;
    }

    .reason-option input[type="radio"]:checked::after {
      content: '';
      position: absolute;
      inset: 3px;
      background: #FF5A36;
      border-radius: 50%;
    }

    .reason-option.selected {
      background: #fff6f4;
    }

    #otherReasonText {
      display: none;
      margin-top: 10px;
    }
  </style>
</head>

<body>

  <header>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/navbar.php'); ?>
  </header>

  <div class="profile-page p-3">

    <!-- PROFILE HEADER -->
    <div class="prof-header">
      <div class="prof-avatar"><?= htmlspecialchars(initials($current_fullname)) ?></div>
      <div class="prof-details">
        <div class="prof-top-row">
          <div>
            <h3 class="prof-name"><?= htmlspecialchars($current_fullname) ?></h3>
            <p class="prof-meta">
              <?= htmlspecialchars($_SESSION['auth_user']['email']   ?? '') ?> |
              <?= htmlspecialchars($_SESSION['auth_user']['address'] ?? '') ?>
            </p>
          </div>
        </div>
        <div class="prof-tags">
          <?php if ($is_adopter): ?>
          <span class="ptag ptag-adopter"><i class="bi bi-heart-fill"></i> Adopter</span>
          <?php endif; ?>
          <?php if ($is_buyer): ?>
          <span class="ptag ptag-buyer"><i class="bi bi-bag-fill"></i> Buyer</span>
          <?php endif; ?>
          <?php if ($member_since): ?>
          <span class="ptag ptag-member">Member since <?= htmlspecialchars($member_since) ?></span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- TABS -->
    <div class="tabs-row">
      <button class="tab-btn active" data-tab="orders">Orders</button>
      <button class="tab-btn" data-tab="appointments">Appointments</button>
      <button class="tab-btn" data-tab="adoptions">Adoptions</button>
      <button class="tab-btn" data-tab="account">Account Info</button>
    </div>

    <!-- TAB CONTENT -->
    <div class="tab-area">

      <!-- ── ORDERS TAB ── -->
      <div class="tab-pane active" id="tab-orders">


        <div class="two-col">

          <!-- Recent Orders -->
          <div class="info-card">
            <div class="icard-head">
              <span class="icard-title">Recent Orders</span>
              <a href="#" class="view-all-lnk">View All →</a>
            </div>
            <div class="order-row d-flex flex-column align-items-start">
              <?php if (empty($orders)): ?>
                <p class="row-meta" style="padding:.5rem 0;">No orders yet.</p>
                <?php else: foreach (array_slice($orders, 0, 3) as $ord):
                  $odate = date('M j', strtotime($ord['updated_at']));
                  $opill = match (strtolower(trim($ord['order_status']))) {
                    'delivered'  => 'pill-green',
                    'in transit', 'shipped' => 'pill-blue',
                    'cancelled'  => 'pill-red',
                    default      => 'pill-orange',
                  };
                ?>
                  <div class="order-row">
                    <div class="thumb-sq">
                      <?php if (!empty($ord['productimage'])): ?>
                        <img src="/PAWSTER/resources/images/<?= htmlspecialchars($ord['productimage']) ?>"
                          alt="<?= htmlspecialchars($ord['brand_name']) ?>"
                          style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">
                      <?php endif; ?>
                    </div>
                    <div class="row-info">
                      <p class="row-name"><?= htmlspecialchars($ord['brand_name']) ?></p>
                      <p class="row-meta">Order #<?= (int)$ord['orderid'] ?> | <?= $odate ?></p>
                    </div>
                    <div class="order-right">
                      <p class="order-price">₱<?= number_format((float)$ord['total_price'], 2) ?></p>
                      <span class="pill <?= $opill ?>"><?= htmlspecialchars($ord['order_status']) ?></span>
                    </div>
                  </div>
              <?php endforeach;
              endif; ?>
            </div>
          </div>

          <!-- Upcoming Appointments – LIVE, cancellable -->
          <div class="info-card">
            <div class="icard-head">
              <span class="icard-title">Upcoming Appointments</span>
              <a href="#" class="view-all-lnk" id="go-appt-tab">View All →</a>
            </div>

            <?php if (empty($upcoming_preview)): ?>
              <p class="row-meta" style="padding:.5rem 0;">No upcoming appointments.</p>
              <?php else: foreach ($upcoming_preview as $appt):
                echo renderApptRow($appt);
              endforeach;
            endif; ?>
          </div>

        </div><!-- /two-col -->

        <!-- Account Details snapshot -->
        <div class="info-card mt-row">
          <div class="icard-head" style="margin-bottom:.7rem;">
            <span class="icard-title">Account Details</span>
          </div>
          <div class="acct-row">
            <div class="acct-key"><i class="bi bi-person"></i> Full Name</div>
            <div class="acct-val"><?= htmlspecialchars($current_fullname) ?></div>
          </div>
          <div class="acct-row">
            <div class="acct-key"><i class="bi bi-envelope"></i> Email</div>
            <div class="acct-val"><?= htmlspecialchars($_SESSION['auth_user']['email']   ?? '—') ?></div>
          </div>
          <div class="acct-row">
            <div class="acct-key"><i class="bi bi-telephone"></i> Phone</div>
            <div class="acct-val">+63-<?= htmlspecialchars($user_contact ?? '—') ?></div>
          </div>
          <div class="acct-row acct-last">
            <div class="acct-key"><i class="bi bi-geo-alt"></i> Address</div>
            <div class="acct-val"><?= htmlspecialchars($user_address ?? '—') ?></div>
          </div>
        </div>

      </div><!-- /tab-orders -->

      <!-- ── APPOINTMENTS TAB – FULLY LIVE, cancellable ── -->
      <div class="tab-pane" id="tab-appointments">
        <div class="info-card">
          <div class="icard-head">
            <span class="icard-title">All Appointments</span>
          </div>

          <?php if (empty($appointments)): ?>
            <p class="row-meta" style="padding:.5rem 0;">No appointments booked yet.</p>
            <?php else: foreach ($appointments as $appt):
              echo renderApptRow($appt);
            endforeach;
          endif; ?>

        </div>
      </div><!-- /tab-appointments -->

      <!-- ── ADOPTIONS TAB ── -->
      <div class="tab-pane" id="tab-adoptions">
        <div class="info-card">
          <div class="icard-head"><span class="icard-title">Adoption Requests</span></div>
          <?php if (empty($adoptions)): ?>
            <p class="row-meta" style="padding:.5rem 0;">No adoption requests yet.</p>
          <?php else: foreach ($adoptions as $ad):
            $adDate = date('M j, Y', strtotime($ad['created_at']));
            $adPill = match(strtolower($ad['status'])) {
              'approved' => 'pill-blue',
              'rejected' => 'pill-red',
              default    => 'pill-orange',
            };
            $adImg = $ad['image'] ? '/PAWSTER/uploads/pets/' . htmlspecialchars($ad['image']) : '';
          ?>
          <div class="order-row">
            <div class="thumb-sq" style="<?= $adImg ? 'background-image:url('.$adImg.');background-size:cover;background-position:center;' : 'background:#C4A882;' ?>"></div>
            <div class="row-info">
              <p class="row-name"><?= htmlspecialchars($ad['pet_name']) ?> – <?= htmlspecialchars($ad['breed']) ?></p>
              <p class="row-meta">Applied <?= $adDate ?></p>
            </div>
            <div class="order-right">
              <span class="pill <?= $adPill ?>"><?= htmlspecialchars(ucfirst(strtolower($ad['status']))) ?></span>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div><!-- /tab-adoptions -->

      <!-- ── ACCOUNT INFO TAB ── -->
      <div class="tab-pane" id="tab-account">
        <div class="info-card">
          <div class="icard-head" style="margin-bottom:.7rem;">
            <span class="icard-title">Account Details</span>
          </div>
          <div class="acct-row">
            <div class="acct-key"><i class="bi bi-person"></i> Full Name</div>
            <div class="acct-val"><?= htmlspecialchars($current_fullname) ?></div>
          </div>
          <div class="acct-row">
            <div class="acct-key"><i class="bi bi-envelope"></i> Email</div>
            <div class="acct-val"><?= htmlspecialchars($_SESSION['auth_user']['email']   ?? '—') ?></div>
          </div>
          <div class="acct-row">
            <div class="acct-key"><i class="bi bi-telephone"></i> Phone</div>
            <div class="acct-val"><?= htmlspecialchars($_SESSION['auth_user']['phone']   ?? '—') ?></div>
          </div>
          <div class="acct-row acct-last">
            <div class="acct-key"><i class="bi bi-geo-alt"></i> Address</div>
            <div class="acct-val"><?= htmlspecialchars($_SESSION['auth_user']['address'] ?? '—') ?></div>
          </div>
        </div>
      </div><!-- /tab-account -->

    </div><!-- /tab-area -->
  </div><!-- /profile-page -->

  <!-- CANCEL APPOINTMENT MODAL — TikTok-style reason picker -->
  <div class="modal fade" id="cancelApptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Cancel Appointment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="cancel-prompt">Please select a reason for cancelling</p>

          <div class="cancel-reasons" id="cancelReasonsList">
            <label class="reason-option">
              <span>Schedule conflict</span>
              <input type="radio" name="cancelReason" value="Schedule conflict">
            </label>
            <label class="reason-option">
              <span>Found another service provider</span>
              <input type="radio" name="cancelReason" value="Found another service provider">
            </label>
            <label class="reason-option">
              <span>Pet is unwell / can't make it</span>
              <input type="radio" name="cancelReason" value="Pet is unwell / can't make it">
            </label>
            <label class="reason-option">
              <span>Changed my mind</span>
              <input type="radio" name="cancelReason" value="Changed my mind">
            </label>
            <label class="reason-option">
              <span>Other</span>
              <input type="radio" name="cancelReason" value="other">
            </label>
          </div>

          <textarea id="otherReasonText" class="form-control" rows="2" placeholder="Tell us more..."></textarea>

          <div class="alert alert-warning mt-3 mb-0" style="font-size:.9rem;">
            <i class="bi bi-exclamation-triangle-fill"></i>
            Cancelling this appointment will incur a <strong>10% deduction</strong> on your payment as a cancellation fee. Refund will be sent in <strong>3-5<strong> business days.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Go Back</button>
          <button type="button" class="btn btn-danger" id="confirmCancelBtn">Confirm Cancellation</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function switchTab(name) {
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
      const btn = document.querySelector(`.tab-btn[data-tab="${name}"]`);
      const pane = document.getElementById(`tab-${name}`);
      if (btn) btn.classList.add('active');
      if (pane) pane.classList.add('active');
    }

    document.querySelectorAll('.tab-btn').forEach(btn => {
      btn.addEventListener('click', () => switchTab(btn.dataset.tab));
    });

    document.getElementById('go-appt-tab')
      .addEventListener('click', e => {
        e.preventDefault();
        switchTab('appointments');
      });

    // ── CANCEL APPOINTMENT FLOW ──
    let cancelTargetId = null;
    const cancelModalEl   = document.getElementById('cancelApptModal');
    const cancelModal     = new bootstrap.Modal(cancelModalEl);
    const confirmCancelBtn = document.getElementById('confirmCancelBtn');
    const otherReasonText  = document.getElementById('otherReasonText');
    const reasonRadios     = document.querySelectorAll('input[name="cancelReason"]');
    const reasonOptions    = document.querySelectorAll('.reason-option');

    function resetCancelModal() {
      reasonRadios.forEach(r => r.checked = false);
      reasonOptions.forEach(o => o.classList.remove('selected'));
      otherReasonText.value = '';
      otherReasonText.style.display = 'none';
    }

    reasonRadios.forEach(radio => {
      radio.addEventListener('change', () => {
        reasonOptions.forEach(o => o.classList.remove('selected'));
        radio.closest('.reason-option').classList.add('selected');
        otherReasonText.style.display = radio.value === 'other' ? 'block' : 'none';
        if (radio.value === 'other') otherReasonText.focus();
      });
    });

    document.querySelectorAll('.btn-cancel-appt').forEach(btn => {
      btn.addEventListener('click', () => {
        cancelTargetId = btn.dataset.id;
        resetCancelModal();
        cancelModal.show();
      });
    });

    function getSelectedReason() {
      const checked = document.querySelector('input[name="cancelReason"]:checked');
      if (!checked) return null;
      if (checked.value === 'other') {
        const other = otherReasonText.value.trim();
        return other ? other : null;
      }
      return checked.value;
    }

    confirmCancelBtn.addEventListener('click', async () => {
      const reason = getSelectedReason();
      if (!reason) {
        alert('Please select a reason for cancellation.');
        return;
      }

      confirmCancelBtn.disabled = true;
      confirmCancelBtn.textContent = 'Cancelling...';

      try {
        const res = await fetch(window.location.href, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'cancel_appointment', appointmentID: cancelTargetId, reason })
        });

        // Read the raw response first so a non-JSON / 500 error from PHP
        // doesn't get masked as a generic "Something went wrong".
        const raw = await res.text();
        let data;
        try {
          data = JSON.parse(raw);
        } catch (parseErr) {
          console.error('cancel_appointment.php did not return valid JSON:', raw);
          alert('The server returned an unexpected response. Check the browser console / PHP error log for details.');
          return;
        }

        if (!res.ok) {
          console.error('cancel_appointment.php returned HTTP', res.status, data);
          alert(data.message || `Cancellation failed (HTTP ${res.status}).`);
          return;
        }

        if (data.success) {
          cancelModal.hide();
          location.reload();
        } else {
          alert(data.message || 'Cancellation failed. Please try again.');
        }
      } catch (err) {
        console.error('Cancel appointment request failed:', err);
        alert('Something went wrong while sending the request. Please check your connection and try again.');
      } finally {
        confirmCancelBtn.disabled = false;
        confirmCancelBtn.textContent = 'Confirm Cancellation';
      }
    });
  </script>
</body>

</html>