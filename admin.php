<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/config/app.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pawster – Admin</title>
<link rel="icon"  href="/PAWSTER/resources/images/logo white.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Caprasimo&family=Convergence&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="resources/css/admin.css">
</head>
<body>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/navbar.php'; ?>

<?php
$conn = $db->conn;

// ── FETCH LISTINGS ──
$listings_sql = "
    SELECT sp.productid, sp.brand_name, sp.primary_category, sp.price, sp.listing_status,
           CONCAT(u.first_name, ' ', u.last_name) AS seller_name
    FROM tblsellerproduct sp
    JOIN tblsellerprofile sp2 ON sp2.sellerid = sp.sellerid
    JOIN users u ON u.userid = sp2.sellerid
    WHERE sp.listing_status != 'deleted'
    ORDER BY sp.productid DESC
";
$listings_result = $db->conn->query($listings_sql);
$all_listings = $listings_result ? $listings_result->fetch_all(MYSQLI_ASSOC) : [];
$listings_active  = count(array_filter($all_listings, fn($l) => strtolower($l['listing_status'] ?? 'active') === 'active'));
$listings_pending = count(array_filter($all_listings, fn($l) => strtolower($l['listing_status'] ?? '') === 'pending'));
$listings_removed = count(array_filter($all_listings, fn($l) => strtolower($l['listing_status'] ?? '') === 'removed'));
$total_sellers_sql = "SELECT COUNT(DISTINCT sellerid) AS cnt FROM tblsellerprofile";
$ts_res = $db->conn->query($total_sellers_sql);
$total_sellers = $ts_res ? (int)$ts_res->fetch_assoc()['cnt'] : 0;

// ── FETCH PETS ──
$pets_sql = "SELECT * FROM tblpets ORDER BY created_at DESC";
$pets_result = $conn->query($pets_sql);
$all_pets = $pets_result ? $pets_result->fetch_all(MYSQLI_ASSOC) : [];

// ── FETCH ADOPTION REQUESTS ──
$adopt_sql = "
    SELECT
        ar.requestid,
        ar.petid,
        ar.userid,
        ar.status,
        ar.created_at,
        CONCAT(u.first_name, ' ', u.last_name) AS adopter_name,
        p.name AS pet_name,
        p.breed AS pet_breed
    FROM tbladoptionrequest ar
    JOIN users u ON u.userid = ar.userid
    JOIN tblpets p ON p.petid = ar.petid
    ORDER BY ar.created_at DESC
";
$adopt_result = $conn->query($adopt_sql);
$adoption_requests = $adopt_result ? $adopt_result->fetch_all(MYSQLI_ASSOC) : [];

$adopt_pending  = count(array_filter($adoption_requests, fn($r) => strtolower($r['status']) === 'pending'));
$adopt_approved = count(array_filter($adoption_requests, fn($r) => strtolower($r['status']) === 'approved'));
$adopt_rejected = count(array_filter($adoption_requests, fn($r) => strtolower($r['status']) === 'rejected'));

/**
 * JOIN query: pulls seller name from users, business name from tblapplicationform,
 * submission date + status from tblsellerstatus — all linked by formid / userid.
 */
$sql = "
    SELECT
        ss.status_id,
        ss.formid,
        ss.userid,
        CONCAT(u.first_name, ' ', u.last_name)  AS seller_name,
        af.business_name,
        DATE_FORMAT(ss.created_at, '%b %e')      AS submitted,
        ss.status
    FROM tblsellerstatus  ss
    JOIN tblapplicationform af ON af.formid  = ss.formid
    JOIN users              u  ON u.userid   = ss.userid
    ORDER BY ss.created_at DESC
";
$result      = $conn->query($sql);
$applications = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// ── FETCH APPOINTMENTS FROM DATABASE ──
// JOIN with users table to get the booker's full name
$appt_sql = "
    SELECT
        a.appointmentID,
        a.userid,
        CONCAT(u.first_name, ' ', u.last_name) AS booked_by,
        a.service_type,
        a.select_pet,
        DATE_FORMAT(a.date, '%b %e, %Y')        AS appt_date,
        a.available_time,
        a.status
    FROM tblappointment a
    LEFT JOIN users u ON u.userid = a.userid
    ORDER BY a.appointmentID DESC
";
$appt_result  = $conn->query($appt_sql);
$appointments = $appt_result ? $appt_result->fetch_all(MYSQLI_ASSOC) : [];

// Stat counts for appointments
$appt_total     = count($appointments);
$appt_pending   = count(array_filter($appointments, fn($r) => strtolower($r['status']) === 'pending'));
$appt_approved  = count(array_filter($appointments, fn($r) => strtolower($r['status']) === 'approved'));
$appt_cancelled = count(array_filter($appointments, fn($r) => strtolower($r['status']) === 'cancelled'));

// Today's appointment count
$today_sql = "SELECT COUNT(*) AS cnt FROM tblappointment WHERE date = CURDATE()";
$today_res = $conn->query($today_sql);
$appt_today = $today_res ? (int)$today_res->fetch_assoc()['cnt'] : 0;

/* ── helper: render one seller row ── */
function renderAppRow(array $row): string {
    $statusBadge = match(strtolower($row['status'])) {
        'verified'  => '<span class="badge-s badge-green">Verified</span>',
        'rejected'  => '<span class="badge-s badge-red">Rejected</span>',
        default     => '<span class="badge-s badge-orange">Pending</span>',
    };

    $sid  = (int) $row['status_id'];
    $name = htmlspecialchars($row['seller_name']);
    $biz  = htmlspecialchars($row['business_name']);
    $sub  = htmlspecialchars($row['submitted']);

    $actions = in_array(strtolower($row['status']), ['verified', 'rejected'])
        ? '<span class="done-txt">Done</span>'
        : '
          <div class="act-col">
            <form method="POST" action="/PAWSTER/controllers/update_seller_status.php" style="display:inline;">
              <input type="hidden" name="status_id" value="' . $sid . '">
              <input type="hidden" name="new_status" value="verified">
              <button type="submit" class="btn-app">Approve</button>
            </form>
            <form method="POST" action="/PAWSTER/controllers/update_seller_status.php" style="display:inline;">
              <input type="hidden" name="status_id" value="' . $sid . '">
              <input type="hidden" name="new_status" value="rejected">
              <button type="submit" class="btn-rej">Reject</button>
            </form>
          </div>';

    return "
        <tr>
          <td>{$name}</td>
          <td>{$biz}</td>
          <td>{$sub}</td>
          <td>{$statusBadge}</td>
          <td>{$actions}</td>
        </tr>";
}

/* ── helper: render one appointment row ── */
function renderApptRow(array $row): string {
    $status = strtolower($row['status']);

    $statusBadge = match($status) {
        'approved'  => '<span class="badge-s badge-green" id="appt-badge-' . $row['appointmentID'] . '">Approved</span>',
        'rejected'  => '<span class="badge-s badge-red"   id="appt-badge-' . $row['appointmentID'] . '">Rejected</span>',
        'cancelled' => '<span class="badge-s badge-gray"  id="appt-badge-' . $row['appointmentID'] . '">Cancelled</span>',
        default     => '<span class="badge-s badge-orange" id="appt-badge-' . $row['appointmentID'] . '">Pending</span>',
    };

    $apptID  = (int) $row['appointmentID'];
    $user    = htmlspecialchars($row['booked_by'] ?? '—');
    $service = htmlspecialchars($row['service_type']);
    $pet     = htmlspecialchars($row['select_pet']);
    $date    = htmlspecialchars($row['appt_date']);
    $time    = htmlspecialchars($row['available_time']);

    if (in_array($status, ['approved', 'rejected'])) {
        $actions = '<span class="done-txt" id="appt-action-' . $apptID . '">Done</span>';
    } elseif ($status === 'cancelled') {
        $actions = '<span class="done-txt" id="appt-action-' . $apptID . '">Cancelled by user</span>';
    } else {
        $actions = '<div class="act-col" id="appt-action-' . $apptID . '">
             <button class="btn-app" onclick="updateApptStatus(' . $apptID . ', \'Approved\')">Approve</button>
             <button class="btn-rej" onclick="updateApptStatus(' . $apptID . ', \'Rejected\')">Reject</button>
           </div>';
    }

    return "
        <tr id=\"appt-row-{$apptID}\">
          <td>{$user}</td>
          <td>{$service}</td>
          <td>{$pet}</td>
          <td>{$date} | {$time}</td>
          <td>{$statusBadge}</td>
          <td>{$actions}</td>
        </tr>";
}
?>

<div class="admin-layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-brand">
      <img src="resources/images/Logo.png" alt="Logo" class="brand-logo">
      <span class="brand-name">PAWSTER</span>
    </div>
    <nav class="sidebar-nav">
      <a href="#" class="snav-link active" data-section="overview">
        <i class="bi bi-grid-fill"></i><span>Overview</span>
      </a>
      <a href="#" class="snav-link" data-section="sellers">
        <i class="bi bi-shop"></i><span>Sellers</span>
      </a>
      <a href="#" class="snav-link" data-section="adoptions">
        <i class="bi bi-heart"></i><span>Adoptions</span>
      </a>
      <a href="#" class="snav-link" data-section="pets">
        <i class="bi bi-emoji-smile"></i><span>Pets</span>
      </a>
      <a href="#" class="snav-link" data-section="appointments">
        <i class="bi bi-calendar3"></i><span>Appointments</span>
      </a>
      <a href="#" class="snav-link" data-section="listings">
        <i class="bi bi-list-ul"></i><span>Listings</span>
      </a>
    </nav>
  </aside>

  <!-- MAIN -->
  <main class="admin-main">

    <div class="admin-topbar">
      <h2 class="topbar-title" id="topbar-title">Admin Overview</h2>
    </div>

    <!-- ── OVERVIEW ── -->
    <div class="admin-section active" id="section-overview">
      <div class="stat-grid">
        <div class="stat-card">
          <p class="stat-label">Pending Sellers</p>
          <p class="stat-num">
            <?= count(array_filter($applications, fn($r) => strtolower($r['status']) === 'pending')) ?>
          </p>
          <span class="stat-pill pill-red">Needs Review</span>
        </div>
        <div class="stat-card">
          <p class="stat-label">Adoption Requests</p>
          <p class="stat-num">12</p>
          <span class="stat-pill pill-blue">Awaiting Approval</span>
        </div>
        <!-- Live appointment stats -->
        <div class="stat-card">
          <p class="stat-label">Appointments Today</p>
          <p class="stat-num"><?= $appt_today ?></p>
          <span class="stat-pill pill-green"><?= $appt_approved ?> Approved</span>
        </div>
        <div class="stat-card">
          <p class="stat-label">Active Listings</p>
          <p class="stat-num"><?= $listings_active ?></p>
          <span class="stat-pill pill-green">Live Products</span>
        </div>
      </div>

      <!-- Seller Applications (overview – latest 5) -->
      <div class="table-section">
        <div class="section-head">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-person-check-fill sec-icon"></i>
            <span class="section-title">Seller Applications</span>
          </div>
          <a href="#" class="view-all-link" onclick="switchSection('sellers'); return false;">View All →</a>
        </div>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr><th>Seller Name</th><th>Business</th><th>Submitted</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
              <?php
              $preview = array_slice($applications, 0, 5);
              if (empty($preview)): ?>
                <tr><td colspan="5" style="text-align:center; color:#9B7050;">No applications yet.</td></tr>
              <?php else:
                foreach ($preview as $row) echo renderAppRow($row);
              endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="table-section">
        <div class="section-head">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-heart-fill sec-icon" style="color:#C0735A;"></i>
            <span class="section-title">Adoption Requests</span>
          </div>
          <a href="#" class="view-all-link" onclick="switchSection('adoptions'); return false;">View All →</a>
        </div>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr><th>Adopter</th><th>Pet</th><th>Date</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
              <tr>
                <td>Maria Santos</td><td>Dog</td><td>June 19</td>
                <td><span class="badge-s badge-orange">Pending</span></td>
                <td><div class="act-col"><button class="btn-app">Approve</button><button class="btn-rej">Reject</button></div></td>
              </tr>
              <tr>
                <td>Jose Reyes</td><td>Cat</td><td>June 15</td>
                <td><span class="badge-s badge-blue">Under Review</span></td>
                <td><div class="act-col"><button class="btn-app">Approve</button><button class="btn-rej">Reject</button></div></td>
              </tr>
              <tr>
                <td>Ana Gomez</td><td>Kitten</td><td>June 16</td>
                <td><span class="badge-s badge-green">Approved</span></td>
                <td><span class="done-txt">Done</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Overview: latest 5 appointments (live) -->
      <div class="table-section">
        <div class="section-head">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-calendar3 sec-icon"></i>
            <span class="section-title">Appointments</span>
          </div>
          <a href="#" class="view-all-link" onclick="switchSection('appointments'); return false;">View All →</a>
        </div>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr><th>Booked By</th><th>Service</th><th>Pet</th><th>Date &amp; Time</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
              <?php
              $appt_preview = array_slice($appointments, 0, 5);
              if (empty($appt_preview)): ?>
                <tr><td colspan="6" style="text-align:center; color:#9B7050;">No appointments yet.</td></tr>
              <?php else:
                foreach ($appt_preview as $row) echo renderApptRow($row);
              endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── SELLERS ── -->
    <div class="admin-section" id="section-sellers">
      <?php
      $total    = count($applications);
      $pending  = count(array_filter($applications, fn($r) => strtolower($r['status']) === 'pending'));
      $rejected = count(array_filter($applications, fn($r) => strtolower($r['status']) === 'rejected'));
      ?>
      <div class="stat-grid" style="grid-template-columns: repeat(3,1fr);">
        <div class="stat-card">
          <p class="stat-label">Total Applications</p>
          <p class="stat-num"><?= $total ?></p>
          <span class="stat-pill pill-green">All Time</span>
        </div>
        <div class="stat-card">
          <p class="stat-label">Pending Review</p>
          <p class="stat-num"><?= $pending ?></p>
          <span class="stat-pill pill-red">Needs Action</span>
        </div>
        <div class="stat-card">
          <p class="stat-label">Rejected</p>
          <p class="stat-num"><?= $rejected ?></p>
          <span class="stat-pill pill-blue">This Month</span>
        </div>
      </div>

      <div class="table-section">
        <div class="section-head">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-shop sec-icon"></i>
            <span class="section-title">All Seller Applications</span>
          </div>
        </div>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr><th>Seller Name</th><th>Business</th><th>Submitted</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
              <?php if (empty($applications)): ?>
                <tr><td colspan="5" style="text-align:center; color:#9B7050;">No applications yet.</td></tr>
              <?php else:
                foreach ($applications as $row) echo renderAppRow($row);
              endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── ADOPTIONS ── -->
    <div class="admin-section" id="section-adoptions">
      <div class="stat-grid" style="grid-template-columns: repeat(3,1fr);">
        <div class="stat-card">
          <p class="stat-label">Pending Requests</p>
          <p class="stat-num"><?= $adopt_pending ?></p>
          <span class="stat-pill pill-blue">Awaiting Approval</span>
        </div>
        <div class="stat-card">
          <p class="stat-label">Approved</p>
          <p class="stat-num"><?= $adopt_approved ?></p>
          <span class="stat-pill pill-green">All Time</span>
        </div>
        <div class="stat-card">
          <p class="stat-label">Rejected</p>
          <p class="stat-num"><?= $adopt_rejected ?></p>
          <span class="stat-pill pill-red">All Time</span>
        </div>
      </div>

      <div class="table-section">
        <div class="section-head">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-heart-fill sec-icon" style="color:#C0735A;"></i>
            <span class="section-title">All Adoption Requests</span>
          </div>
        </div>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr><th>Adopter</th><th>Pet</th><th>Breed</th><th>Date</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
              <?php if (empty($adoption_requests)): ?>
                <tr><td colspan="6" style="text-align:center;color:#9B7050;">No adoption requests yet.</td></tr>
              <?php else: foreach ($adoption_requests as $ar):
                $arStatus = strtolower($ar['status']);
                $badge = match($arStatus) {
                  'approved' => '<span class="badge-s badge-green">Approved</span>',
                  'rejected' => '<span class="badge-s badge-red">Rejected</span>',
                  default    => '<span class="badge-s badge-orange" id="adopt-badge-' . $ar['requestid'] . '">Pending</span>',
                };
                $actions = in_array($arStatus, ['approved','rejected'])
                  ? '<span class="done-txt">Done</span>'
                  : '<div class="act-col" id="adopt-action-' . $ar['requestid'] . '">
                       <button class="btn-app" onclick="updateAdoptStatus(' . $ar['requestid'] . ',\'Approved\')">Approve</button>
                       <button class="btn-rej" onclick="updateAdoptStatus(' . $ar['requestid'] . ',\'Rejected\')">Reject</button>
                     </div>';
              ?>
              <tr>
                <td><?= htmlspecialchars($ar['adopter_name']) ?></td>
                <td><?= htmlspecialchars($ar['pet_name']) ?></td>
                <td><?= htmlspecialchars($ar['pet_breed']) ?></td>
                <td><?= date('M j', strtotime($ar['created_at'])) ?></td>
                <td><?= $badge ?></td>
                <td><?= $actions ?></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── PETS ── -->
    <div class="admin-section" id="section-pets">
      <div class="table-section">
        <div class="section-head">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-plus-circle sec-icon" style="color:#AB8154;"></i>
            <span class="section-title">Add New Pet</span>
          </div>
        </div>
        <div id="petFormMsg" style="display:none;padding:.55rem .85rem;border-radius:.55rem;font-family:'Convergence',sans-serif;font-size:.83rem;margin-bottom:.75rem;"></div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:.75rem 1rem;">
          <div style="display:flex;flex-direction:column;gap:.25rem;">
            <label class="pet-lbl">Category *</label>
            <select id="pCat" class="pet-inp">
              <option value="Dogs">Dogs</option>
              <option value="Cats">Cats</option>
              <option value="Rabbits">Rabbits</option>
              <option value="Birds">Birds</option>
              <option value="Others">Others</option>
            </select>
          </div>
          <div style="display:flex;flex-direction:column;gap:.25rem;">
            <label class="pet-lbl">Name *</label>
            <input id="pName" type="text" class="pet-inp" placeholder="e.g. Mochi">
          </div>
          <div style="display:flex;flex-direction:column;gap:.25rem;">
            <label class="pet-lbl">Breed *</label>
            <input id="pBreed" type="text" class="pet-inp" placeholder="e.g. Shih Tzu">
          </div>
          <div style="display:flex;flex-direction:column;gap:.25rem;">
            <label class="pet-lbl">Age *</label>
            <input id="pAge" type="text" class="pet-inp" placeholder="e.g. 2 years old">
          </div>
          <div style="display:flex;flex-direction:column;gap:.25rem;">
            <label class="pet-lbl">Sex *</label>
            <input id="pSex" type="text" class="pet-inp" placeholder="e.g. Female">
          </div>
          <div style="display:flex;flex-direction:column;gap:.25rem;">
            <label class="pet-lbl">Color</label>
            <input id="pColor" type="text" class="pet-inp" placeholder="e.g. White & Brown">
          </div>
          <div style="display:flex;flex-direction:column;gap:.25rem;">
            <label class="pet-lbl">Weight</label>
            <input id="pWeight" type="text" class="pet-inp" placeholder="e.g. 4.2kg">
          </div>
          <div style="display:flex;flex-direction:column;gap:.25rem;">
            <label class="pet-lbl">Temperament</label>
            <input id="pTemp" type="text" class="pet-inp" placeholder="e.g. Gentle, Playful">
          </div>
          <div style="display:flex;flex-direction:column;gap:.25rem;">
            <label class="pet-lbl">Good With Kids</label>
            <input id="pKids" type="text" class="pet-inp" placeholder="e.g. Yes">
          </div>
          <div style="display:flex;flex-direction:column;gap:.25rem;">
            <label class="pet-lbl">Location</label>
            <input id="pLoc" type="text" class="pet-inp" placeholder="e.g. Calamba, Laguna">
          </div>
          <div style="display:flex;flex-direction:column;gap:.25rem;">
            <label class="pet-lbl">Pet Photo</label>
            <input id="pImg" type="file" accept="image/*" style="font-family:'Convergence',sans-serif;font-size:.82rem;color:#3D1F08;">
          </div>
        </div>
        <div style="margin-top:.85rem;">
          <label class="pet-lbl" style="display:block;margin-bottom:.4rem;">Health Documents</label>
          <div style="display:flex;flex-wrap:wrap;gap:.5rem .9rem;">
            <?php foreach([
              ['vacc',   'Vaccination Certificate'],
              ['deworm', 'Deworming Record'],
              ['neuter', 'Neutering Certificate'],
              ['vet',    'Vet Health Certificate'],
              ['agree',  'Adoption Agreement Form'],
            ] as [$k,$l]): ?>
            <label style="font-family:'Convergence',sans-serif;font-size:.82rem;color:#3D1F08;display:flex;align-items:center;gap:.3rem;cursor:pointer;">
              <input type="checkbox" class="pet-doc-chk" value="<?= $k ?>"> <?= $l ?>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
        <div style="margin-top:1rem;">
          <button onclick="submitPet()" style="background:#AB8154;color:#FAF0E8;border:none;border-radius:.6rem;padding:.55rem 1.4rem;font-family:'Convergence',sans-serif;font-size:.85rem;font-weight:700;cursor:pointer;">Add Pet</button>
        </div>
      </div>

      <div class="table-section" style="margin-top:1.2rem;">
        <div class="section-head">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-grid sec-icon" style="color:#AB8154;"></i>
            <span class="section-title">All Pets (<?= count($all_pets) ?>)</span>
          </div>
        </div>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr><th>Photo</th><th>Name</th><th>Breed</th><th>Age</th><th>Sex</th><th>Location</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody id="petsTableBody">
              <?php if (empty($all_pets)): ?>
                <tr><td colspan="8" style="text-align:center;color:#9B7050;">No pets added yet.</td></tr>
              <?php else: foreach ($all_pets as $p):
                $imgSrc = $p['image'] ? '/PAWSTER/uploads/pets/' . htmlspecialchars($p['image']) : '';
              ?>
              <tr id="pet-row-<?= $p['petid'] ?>">
                <td><?= $imgSrc ? '<img src="'.$imgSrc.'" style="width:40px;height:40px;border-radius:.4rem;object-fit:cover;">' : '<span style="color:#C4A882;">—</span>' ?></td>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= htmlspecialchars($p['breed']) ?></td>
                <td><?= htmlspecialchars($p['age']) ?></td>
                <td><?= htmlspecialchars($p['sex']) ?></td>
                <td><?= htmlspecialchars($p['location']) ?></td>
                <td><span class="badge-s <?= strtolower($p['status']) === 'available' ? 'badge-green' : 'badge-red' ?>"><?= htmlspecialchars($p['status']) ?></span></td>
                <td><button class="btn-rej" onclick="deletePet(<?= $p['petid'] ?>)">Remove</button></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── APPOINTMENTS (fully live from tblappointment) ── -->
    <div class="admin-section" id="section-appointments">
      <div class="stat-grid" style="grid-template-columns: repeat(4,1fr);">
        <div class="stat-card">
          <p class="stat-label">Appointments Today</p>
          <p class="stat-num"><?= $appt_today ?></p>
          <span class="stat-pill pill-green"><?= $appt_approved ?> Approved</span>
        </div>
        <div class="stat-card">
          <p class="stat-label">Total All Time</p>
          <p class="stat-num"><?= $appt_total ?></p>
          <span class="stat-pill pill-blue">Scheduled</span>
        </div>
        <div class="stat-card">
          <p class="stat-label">Pending</p>
          <p class="stat-num"><?= $appt_pending ?></p>
          <span class="stat-pill pill-red">Awaiting Approval</span>
        </div>
        <div class="stat-card">
          <p class="stat-label">Cancelled</p>
          <p class="stat-num"><?= $appt_cancelled ?></p>
          <span class="stat-pill pill-blue">By Users</span>
        </div>
      </div>

      <div class="table-section">
        <div class="section-head">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-calendar3 sec-icon"></i>
            <span class="section-title">All Appointments</span>
          </div>
        </div>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>Booked By</th>
                <th>Service</th>
                <th>Pet</th>
                <th>Date &amp; Time</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($appointments)): ?>
                <tr><td colspan="6" style="text-align:center; color:#9B7050;">No appointments yet.</td></tr>
              <?php else:
                foreach ($appointments as $row) echo renderApptRow($row);
              endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── LISTINGS ── -->
    <div class="admin-section" id="section-listings">
      <div class="stat-grid">
        <div class="stat-card">
          <p class="stat-label">Active Listings</p>
          <p class="stat-num"><?= $listings_active ?></p>
          <span class="stat-pill pill-green">Live</span>
        </div>
        <div class="stat-card">
          <p class="stat-label">Pending Review</p>
          <p class="stat-num"><?= $listings_pending ?></p>
          <span class="stat-pill pill-red">Needs Review</span>
        </div>
        <div class="stat-card">
          <p class="stat-label">Removed</p>
          <p class="stat-num"><?= $listings_removed ?></p>
          <span class="stat-pill pill-blue">All Time</span>
        </div>
        <div class="stat-card">
          <p class="stat-label">Total Sellers</p>
          <p class="stat-num"><?= $total_sellers ?></p>
          <span class="stat-pill pill-green">Active</span>
        </div>
      </div>

      <div class="table-section">
        <div class="section-head">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-list-ul sec-icon"></i>
            <span class="section-title">All Listings</span>
          </div>
        </div>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr><th>Product</th><th>Seller</th><th>Category</th><th>Price</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
              <?php if (empty($all_listings)): ?>
                <tr><td colspan="6" style="text-align:center;color:#9B7050;">No listings yet.</td></tr>
              <?php else: foreach ($all_listings as $lst):
                $lstStatus = strtolower($lst['listing_status'] ?? 'active');
                $badge = match($lstStatus) {
                  'active'  => '<span class="badge-s badge-green">Active</span>',
                  'pending' => '<span class="badge-s badge-orange">Pending</span>',
                  'removed' => '<span class="badge-s badge-red">Removed</span>',
                  default   => '<span class="badge-s badge-green">Active</span>',
                };
                $actions = $lstStatus === 'removed'
                  ? '<span class="done-txt">Removed</span>'
                  : '<div class="act-col"><button class="btn-rej" onclick="removeListing('.$lst['productid'].')">Remove</button></div>';
              ?>
              <tr id="listing-row-<?= $lst['productid'] ?>">
                <td><?= htmlspecialchars($lst['brand_name']) ?></td>
                <td><?= htmlspecialchars($lst['seller_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($lst['primary_category']) ?></td>
                <td>₱<?= number_format((float)$lst['price'], 2) ?></td>
                <td id="listing-badge-<?= $lst['productid'] ?>"><?= $badge ?></td>
                <td><?= $actions ?></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </main>
</div>

<style>
.admin-section { display: none; }
.admin-section.active { display: block; }
.pet-lbl { font-family:'Convergence',sans-serif; font-size:.75rem; font-weight:700; color:#9B7050; text-transform:uppercase; letter-spacing:.04em; }
.pet-inp { background:#FAF0E8; border:1.5px solid #D6C0A5; border-radius:.55rem; padding:.45rem .75rem; font-family:'Convergence',sans-serif; font-size:.83rem; color:#3D1F08; outline:none; width:100%; }
.pet-inp:focus { border-color:#AB8154; }
.badge-gray { background:#E8E0D4; color:#8A7560; }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
const SECTION_TITLES = {
  overview:     'Admin Overview',
  sellers:      'Sellers',
  adoptions:    'Adoptions',
  appointments: 'Appointments',
  listings:     'Listings',
};

function switchSection(name) {
  document.querySelectorAll('.snav-link').forEach(l => l.classList.remove('active'));
  document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));

  const link = document.querySelector(`.snav-link[data-section="${name}"]`);
  const section = document.getElementById(`section-${name}`);
  if (link) link.classList.add('active');
  if (section) section.classList.add('active');
  document.getElementById('topbar-title').textContent = SECTION_TITLES[name] || name;
}

document.querySelectorAll('.snav-link').forEach(link => {
  link.addEventListener('click', e => {
    e.preventDefault();
    switchSection(link.dataset.section);
  });
});

// ── PETS ──
function submitPet() {
  const msg = document.getElementById('petFormMsg');
  const fd = new FormData();
  fd.append('name',         document.getElementById('pName').value.trim());
  fd.append('category',     document.getElementById('pCat').value);
  fd.append('breed',        document.getElementById('pBreed').value.trim());
  fd.append('age',          document.getElementById('pAge').value.trim());
  fd.append('sex',          document.getElementById('pSex').value.trim());
  fd.append('color',        document.getElementById('pColor').value.trim());
  fd.append('weight',       document.getElementById('pWeight').value.trim());
  fd.append('temperament',  document.getElementById('pTemp').value.trim());
  fd.append('good_with_kids', document.getElementById('pKids').value.trim());
  fd.append('location',     document.getElementById('pLoc').value.trim());
  const docs = [...document.querySelectorAll('.pet-doc-chk:checked')].map(c => c.value).join(',');
  fd.append('docs', docs);
  const imgFile = document.getElementById('pImg').files[0];
  if (imgFile) fd.append('image', imgFile);

  fetch('/PAWSTER/controllers/add_pet_controller.php', { method:'POST', body:fd })
    .then(r => r.json())
    .then(data => {
      msg.style.display = 'block';
      if (data.status === 'success') {
        msg.style.background = '#d4edda'; msg.style.color = '#2d6a2d';
        msg.textContent = data.message + ' Refresh to see the updated list.';
        ['pName','pBreed','pAge','pSex','pColor','pWeight','pTemp','pKids','pLoc'].forEach(id => document.getElementById(id).value = '');
        document.getElementById('pImg').value = '';
        document.querySelectorAll('.pet-doc-chk').forEach(c => c.checked = false);
      } else {
        msg.style.background = '#fde8e4'; msg.style.color = '#8b2500';
        msg.textContent = data.message;
      }
    })
    .catch(() => { msg.style.display='block'; msg.style.background='#fde8e4'; msg.style.color='#8b2500'; msg.textContent='Network error.'; });
}

function deletePet(petId) {
  if (!confirm('Remove this pet from the adoption listing?')) return;
  const fd = new FormData();
  fd.append('petid', petId);
  fetch('/PAWSTER/controllers/delete_pet_controller.php', { method:'POST', body:fd })
    .then(r => r.json())
    .then(data => {
      if (data.status === 'success') {
        const row = document.getElementById('pet-row-' + petId);
        if (row) row.remove();
      } else { alert(data.message); }
    });
}

// ── ADOPTIONS ──
function removeListing(productId) {
  if (!confirm('Remove this listing?')) return;
  const fd = new FormData();
  fd.append('productid', productId);
  fetch('/PAWSTER/controllers/remove_listing_controller.php', { method:'POST', body:fd })
    .then(r => r.json())
    .then(data => {
      if (data.status === 'success') {
        const badge = document.getElementById('listing-badge-' + productId);
        if (badge) badge.innerHTML = '<span class="badge-s badge-red">Removed</span>';
        const row = document.getElementById('listing-row-' + productId);
        if (row) row.querySelector('td:last-child').innerHTML = '<span class="done-txt">Removed</span>';
      } else { alert(data.message); }
    })
    .catch(() => alert('Network error.'));
}

function updateAdoptStatus(requestId, newStatus) {
  const fd = new FormData();
  fd.append('requestid', requestId);
  fd.append('new_status', newStatus);
  fetch('/PAWSTER/controllers/update_adopt_status.php', { method:'POST', body:fd })
    .then(r => r.json())
    .then(data => {
      if (data.status === 'success') {
        const badge = document.getElementById('adopt-badge-' + requestId);
        const action = document.getElementById('adopt-action-' + requestId);
        if (badge) { badge.className = 'badge-s ' + (newStatus === 'Approved' ? 'badge-green' : 'badge-red'); badge.textContent = newStatus; }
        if (action) action.outerHTML = '<span class="done-txt">Done</span>';
      } else { alert(data.message || 'Could not update status.'); }
    })
    .catch(() => alert('Network error.'));
}

function updateApptStatus(apptId, newStatus) {
  const formData = new FormData();
  formData.append('appointment_id', apptId);
  formData.append('new_status', newStatus);

  fetch('/PAWSTER/controllers/update_appointment_status.php', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.status === 'success') {
      // Swap badge
      const badge = document.getElementById('appt-badge-' + apptId);
      if (badge) {
        const isApproved = newStatus === 'Approved';
        badge.className   = 'badge-s ' + (isApproved ? 'badge-green' : 'badge-red');
        badge.textContent = newStatus;
      }
      // Swap action buttons → Done
      const action = document.getElementById('appt-action-' + apptId);
      if (action) {
        action.outerHTML = '<span class="done-txt" id="appt-action-' + apptId + '">Done</span>';
      }
    } else {
      alert('Error: ' + (data.message || 'Could not update status.'));
    }
  })
  .catch(() => alert('Network error. Please try again.'));
}
</script>
</body>
</html>