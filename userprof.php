<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/config/app.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$current_userid   = $_SESSION['auth_user']['userid'] ?? 0;
$current_fullname = trim(($_SESSION['auth_user']['first_name'] ?? '') . ' ' . ($_SESSION['auth_user']['last_name'] ?? ''));
$current_fullname = $current_fullname ?: 'Guest User';
$is_logged_in     = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;


$appointments = [];
if ($is_logged_in && $current_userid) {
  $db   = new DatabaseConnection();
  $stmt = $db->conn->prepare(
    "SELECT service_type, select_pet, date, available_time, status
         FROM tblappointment
         WHERE userid = ?
         ORDER BY date ASC"
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
}

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
          <button class="btn-edit-prof"><i class="bi bi-pencil-fill"></i> Edit Profile</button>
        </div>
        <div class="prof-tags">
          <span class="ptag ptag-adopter"><i class="bi bi-heart-fill"></i> Adopter</span>
          <span class="ptag ptag-buyer"><i class="bi bi-bag-fill"></i> Buyer</span>
          <span class="ptag ptag-member">Member since June 2025</span>
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

        <?php if (!empty($appointments)): ?>
          <div class="notif-banner">
            You have <?= count($appointments) ?> appointment<?= count($appointments) !== 1 ? 's' : '' ?> on record.
          </div>
        <?php endif; ?>

        <div class="two-col">

          <!-- Recent Orders – wire to tblorder when ready -->
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

          <!-- Upcoming Appointments – LIVE -->
          <div class="info-card">
            <div class="icard-head">
              <span class="icard-title">Upcoming Appointments</span>
              <a href="#" class="view-all-lnk" id="go-appt-tab">View All →</a>
            </div>

            <?php if (empty($upcoming_preview)): ?>
              <p class="row-meta" style="padding:.5rem 0;">No upcoming appointments.</p>
              <?php else: foreach ($upcoming_preview as $appt):
                $label = date('M j, Y', strtotime($appt['date']));
              ?>
                <div class="appt-row">
                  <div class="thumb-sq"></div>
                  <div class="row-info">
                    <p class="row-name">
                      <?= htmlspecialchars($appt['service_type']) ?> | <?= htmlspecialchars($appt['select_pet']) ?>
                    </p>
                    <p class="row-meta"><?= $label ?> | <?= htmlspecialchars($appt['available_time']) ?></p>
                  </div>
                  <span class="pill <?= pillClass($appt['status']) ?>">
                    <?= htmlspecialchars(ucfirst(strtolower($appt['status']))) ?>
                  </span>
                </div>
            <?php endforeach;
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

      <!-- ── APPOINTMENTS TAB – FULLY LIVE ── -->
      <div class="tab-pane" id="tab-appointments">
        <div class="info-card">
          <div class="icard-head">
            <span class="icard-title">All Appointments</span>
          </div>

          <?php if (empty($appointments)): ?>
            <p class="row-meta" style="padding:.5rem 0;">No appointments booked yet.</p>
            <?php else: foreach ($appointments as $appt):
              $label = date('M j, Y', strtotime($appt['date']));
            ?>
              <div class="appt-row">
                <div class="thumb-sq"></div>
                <div class="row-info">
                  <p class="row-name">
                    <?= htmlspecialchars($appt['service_type']) ?> | <?= htmlspecialchars($appt['select_pet']) ?>
                  </p>
                  <p class="row-meta"><?= $label ?> | <?= htmlspecialchars($appt['available_time']) ?></p>
                </div>
                <span class="pill <?= pillClass($appt['status']) ?>">
                  <?= htmlspecialchars(ucfirst(strtolower($appt['status']))) ?>
                </span>
              </div>
          <?php endforeach;
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Tab switching
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

    // "View All →" on the appointments preview card
    document.getElementById('go-appt-tab')
      .addEventListener('click', e => {
        e.preventDefault();
        switchTab('appointments');
      });
  </script>
</body>

</html>