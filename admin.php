<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pawster – Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Caprasimo&family=Convergence&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="resources/css/admin.css">
</head>
<body>

<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/config/app.php';
$db   = new DatabaseConnection();
$conn = $db->conn;

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

/* ── helper: render one row ── */
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

    /* Once a final decision is made, hide the buttons */
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
      <a href="#" class="snav-link" data-section="appointments">
        <i class="bi bi-calendar3"></i><span>Appointments</span>
      </a>
      <a href="#" class="snav-link" data-section="listings">
        <i class="bi bi-list-ul"></i><span>Listings</span>
      </a>
      <a href="#" class="snav-link" data-section="users">
        <i class="bi bi-people"></i><span>Users</span>
      </a>
      <a href="#" class="snav-link" data-section="settings">
        <i class="bi bi-gear"></i><span>Settings</span>
      </a>
    </nav>
  </aside>

  <!-- MAIN -->
  <main class="admin-main">

    <div class="admin-topbar">
      <h2 class="topbar-title" id="topbar-title">Admin Overview</h2>
      <div class="topbar-user">
        <i class="bi bi-person-circle"></i>
        <span>| User</span>
        <i class="bi bi-chevron-down"></i>
      </div>
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
        <div class="stat-card">
          <p class="stat-label">Appointments Today</p>
          <p class="stat-num">6</p>
          <span class="stat-pill pill-green">3 Confirmed</span>
        </div>
        <div class="stat-card">
          <p class="stat-label">Active Listings</p>
          <p class="stat-num">312</p>
          <span class="stat-pill pill-green">↑ 9% this week</span>
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

      <div class="table-section">
        <div class="section-head">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-calendar3 sec-icon sec-icon"></i>
            <span class="section-title">Appointments</span>
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
          <p class="stat-label">Total Requests</p>
          <p class="stat-num">12</p>
          <span class="stat-pill pill-blue">Awaiting Approval</span>
        </div>
        <div class="stat-card">
          <p class="stat-label">Approved</p>
          <p class="stat-num">34</p>
          <span class="stat-pill pill-green">This Month</span>
        </div>
        <div class="stat-card">
          <p class="stat-label">Rejected</p>
          <p class="stat-num">5</p>
          <span class="stat-pill pill-red">This Month</span>
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
              <tr>
                <td>Maria Santos</td><td>Mochi</td><td>Shih Tzu</td><td>June 19</td>
                <td><span class="badge-s badge-orange">Pending</span></td>
                <td><div class="act-col"><button class="btn-app">Approve</button><button class="btn-rej">Reject</button></div></td>
              </tr>
              <tr>
                <td>Jose Reyes</td><td>Miko</td><td>Persian Cat</td><td>June 15</td>
                <td><span class="badge-s badge-blue">Under Review</span></td>
                <td><div class="act-col"><button class="btn-app">Approve</button><button class="btn-rej">Reject</button></div></td>
              </tr>
              <tr>
                <td>Ana Gomez</td><td>Luna</td><td>Kitten</td><td>June 16</td>
                <td><span class="badge-s badge-green">Approved</span></td>
                <td><span class="done-txt">Done</span></td>
              </tr>
              <tr>
                <td>Ben Torres</td><td>Doga</td><td>Pitbull</td><td>June 14</td>
                <td><span class="badge-s badge-orange">Pending</span></td>
                <td><div class="act-col"><button class="btn-app">Approve</button><button class="btn-rej">Reject</button></div></td>
              </tr>
              <tr>
                <td>Rica Flores</td><td>Dora</td><td>Chihuahua</td><td>June 12</td>
                <td><span class="badge-s badge-green">Approved</span></td>
                <td><span class="done-txt">Done</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      
    </div>

    <!-- ── APPOINTMENTS ── -->
    <div class="admin-section" id="section-appointments">
      <div class="stat-grid" style="grid-template-columns: repeat(3,1fr);">
        <div class="stat-card">
          <p class="stat-label">Appointments Today</p>
          <p class="stat-num">6</p>
          <span class="stat-pill pill-green">3 Confirmed</span>
        </div>
        <div class="stat-card">
          <p class="stat-label">This Week</p>
          <p class="stat-num">21</p>
          <span class="stat-pill pill-blue">Scheduled</span>
        </div>
        <div class="stat-card">
          <p class="stat-label">Pending</p>
          <p class="stat-num">9</p>
          <span class="stat-pill pill-red">Awaiting Confirm</span>
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
              <tr><th>User</th><th>Type</th><th>Pet</th><th>Date & Time</th><th>Location</th><th>Status</th></tr>
            </thead>
            <tbody>
              <tr>
                <td>Juan Dela Cruz</td><td>Grooming</td><td>Mochi</td><td>May 20 | 1:00 PM</td><td>Calamba Hub</td>
                <td><span class="badge-s badge-blue">Confirmed</span></td>
              </tr>
              <tr>
                <td>Juan Dela Cruz</td><td>Vet Check</td><td>Mochi</td><td>June 3 | 10:00 AM</td><td>Pawster Vet</td>
                <td><span class="badge-s badge-orange">Pending</span></td>
              </tr>
              <tr>
                <td>Juan Dela Cruz</td><td>Meet & Greet</td><td>Mochi</td><td>June 7 | 12:00 PM</td><td>Tanauan Shelter</td>
                <td><span class="badge-s badge-green">Scheduled</span></td>
              </tr>
              <tr>
                <td>Maria Santos</td><td>Grooming</td><td>Doga</td><td>June 8 | 3:00 PM</td><td>Sta. Rosa Hub</td>
                <td><span class="badge-s badge-blue">Confirmed</span></td>
              </tr>
              <tr>
                <td>Ben Torres</td><td>Vet Check</td><td>Dora</td><td>June 10 | 9:00 AM</td><td>Pawster Vet</td>
                <td><span class="badge-s badge-orange">Pending</span></td>
              </tr>
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
          <p class="stat-num">312</p>
          <span class="stat-pill pill-green">↑ 9% this week</span>
        </div>
        <div class="stat-card">
          <p class="stat-label">Pending Review</p>
          <p class="stat-num">14</p>
          <span class="stat-pill pill-red">Needs Review</span>
        </div>
        <div class="stat-card">
          <p class="stat-label">Removed</p>
          <p class="stat-num">7</p>
          <span class="stat-pill pill-blue">This Month</span>
        </div>
        <div class="stat-card">
          <p class="stat-label">Total Sellers</p>
          <p class="stat-num">47</p>
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
              <tr>
                <td>Premium Dog Chew Treats</td><td>PawBites PH</td><td>Food</td><td>₱189</td>
                <td><span class="badge-s badge-green">Active</span></td>
                <td><div class="act-col"><button class="btn-rej">Remove</button></div></td>
              </tr>
              <tr>
                <td>Interactive Dog Toy Bundle</td><td>NaturePet</td><td>Toys</td><td>₱256</td>
                <td><span class="badge-s badge-orange">Pending</span></td>
                <td><div class="act-col"><button class="btn-app">Approve</button><button class="btn-rej">Reject</button></div></td>
              </tr>
              <tr>
                <td>Hypoallergenic Pet Shampoo</td><td>PlayPaws</td><td>Grooming</td><td>₱499</td>
                <td><span class="badge-s badge-green">Active</span></td>
                <td><div class="act-col"><button class="btn-rej">Remove</button></div></td>
              </tr>
              <tr>
                <td>Cat Scratching Post Deluxe</td><td>FurBuddy Store</td><td>Accessories</td><td>₱780</td>
                <td><span class="badge-s badge-blue">Under Review</span></td>
                <td><div class="act-col"><button class="btn-app">Approve</button><button class="btn-rej">Reject</button></div></td>
              </tr>
              <tr>
                <td>Orthopedic Dog Bed</td><td>PetNest PH</td><td>Beds</td><td>₱1,250</td>
                <td><span class="badge-s badge-green">Active</span></td>
                <td><div class="act-col"><button class="btn-rej">Remove</button></div></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── USERS ── -->
    <div class="admin-section" id="section-users">
      <div class="stat-grid" style="grid-template-columns: repeat(3,1fr);">
        <div class="stat-card">
          <p class="stat-label">Total Users</p>
          <p class="stat-num">1,284</p>
          <span class="stat-pill pill-green">↑ 12% this month</span>
        </div>
        <div class="stat-card">
          <p class="stat-label">Buyers</p>
          <p class="stat-num">1,102</p>
          <span class="stat-pill pill-blue">Active</span>
        </div>
        <div class="stat-card">
          <p class="stat-label">Adopters</p>
          <p class="stat-num">182</p>
          <span class="stat-pill pill-green">Registered</span>
        </div>
      </div>

      <div class="table-section">
        <div class="section-head">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-people sec-icon"></i>
            <span class="section-title">All Users</span>
          </div>
        </div>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr><th>Name</th><th>Email</th><th>Role</th><th>Location</th><th>Joined</th><th>Action</th></tr>
            </thead>
            <tbody>
              <tr>
                <td>Juan Dela Cruz</td><td>juan.dela.cruz@gmail.com</td><td>Buyer / Adopter</td><td>Calamba, Laguna</td><td>June 2025</td>
                <td><div class="act-col"><button class="btn-rej">Suspend</button></div></td>
              </tr>
              <tr>
                <td>Maria Santos</td><td>m.santos@gmail.com</td><td>Buyer</td><td>Manila</td><td>Mar 2025</td>
                <td><div class="act-col"><button class="btn-rej">Suspend</button></div></td>
              </tr>
              <tr>
                <td>Jose Reyes</td><td>jose.reyes@gmail.com</td><td>Adopter</td><td>Biñan, Laguna</td><td>Jan 2026</td>
                <td><div class="act-col"><button class="btn-rej">Suspend</button></div></td>
              </tr>
              <tr>
                <td>Ana Gomez</td><td>ana.gomez@gmail.com</td><td>Seller</td><td>Sta. Rosa, Laguna</td><td>Apr 2025</td>
                <td><div class="act-col"><button class="btn-rej">Suspend</button></div></td>
              </tr>
              <tr>
                <td>Ben Torres</td><td>ben.torres@gmail.com</td><td>Buyer / Adopter</td><td>Los Baños, Laguna</td><td>Feb 2026</td>
                <td><div class="act-col"><button class="btn-rej">Suspend</button></div></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── SETTINGS ── -->
    <div class="admin-section" id="section-settings">
      <div class="table-section">
        <div class="section-head">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-gear sec-icon"></i>
            <span class="section-title">Platform Settings</span>
          </div>
        </div>
        <div style="display:flex; flex-direction:column; gap:0.8rem;">

          <div style="background:#FAF0E8; border-radius:0.7rem; padding:0.85rem 1rem; display:flex; justify-content:space-between; align-items:center;">
            <div>
              <p style="font-family:'Convergence',sans-serif; font-size:0.85rem; font-weight:700; color:#3D1F08; margin:0;">Adoption Auto-Notifications</p>
              <p style="font-family:'Convergence',sans-serif; font-size:0.75rem; color:#9B7050; margin:0.15rem 0 0;">Send email updates to adopters on status change</p>
            </div>
            <div class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" checked style="width:2.2rem; height:1.15rem; cursor:pointer; background-color:#AB8154; border-color:#AB8154;">
            </div>
          </div>

          <div style="background:#FAF0E8; border-radius:0.7rem; padding:0.85rem 1rem; display:flex; justify-content:space-between; align-items:center;">
            <div>
              <p style="font-family:'Convergence',sans-serif; font-size:0.85rem; font-weight:700; color:#3D1F08; margin:0;">Seller Application Alerts</p>
              <p style="font-family:'Convergence',sans-serif; font-size:0.75rem; color:#9B7050; margin:0.15rem 0 0;">Notify admin when a new seller applies</p>
            </div>
            <div class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" checked style="width:2.2rem; height:1.15rem; cursor:pointer; background-color:#AB8154; border-color:#AB8154;">
            </div>
          </div>

          <div style="background:#FAF0E8; border-radius:0.7rem; padding:0.85rem 1rem; display:flex; justify-content:space-between; align-items:center;">
            <div>
              <p style="font-family:'Convergence',sans-serif; font-size:0.85rem; font-weight:700; color:#3D1F08; margin:0;">Listing Review Required</p>
              <p style="font-family:'Convergence',sans-serif; font-size:0.75rem; color:#9B7050; margin:0.15rem 0 0;">All new listings require admin approval before going live</p>
            </div>
            <div class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" style="width:2.2rem; height:1.15rem; cursor:pointer;">
            </div>
          </div>

          <div style="background:#FAF0E8; border-radius:0.7rem; padding:0.85rem 1rem; display:flex; justify-content:space-between; align-items:center;">
            <div>
              <p style="font-family:'Convergence',sans-serif; font-size:0.85rem; font-weight:700; color:#3D1F08; margin:0;">Maintenance Mode</p>
              <p style="font-family:'Convergence',sans-serif; font-size:0.75rem; color:#9B7050; margin:0.15rem 0 0;">Take the platform offline for users temporarily</p>
            </div>
            <div class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" style="width:2.2rem; height:1.15rem; cursor:pointer;">
            </div>
          </div>

        </div>
      </div>

      <div class="table-section">
        <div class="section-head">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-person-lock sec-icon"></i>
            <span class="section-title">Admin Account</span>
          </div>
        </div>
        <div style="display:flex; flex-direction:column; gap:0.75rem;">
          <div style="display:flex; flex-direction:column; gap:0.3rem;">
            <label style="font-family:'Convergence',sans-serif; font-size:0.75rem; font-weight:700; color:#9B7050; text-transform:uppercase; letter-spacing:0.04em;">Admin Name</label>
            <input type="text" value="Admin User" style="background:#FAF0E8; border:1.5px solid #D6C0A5; border-radius:0.55rem; padding:0.5rem 0.75rem; font-family:'Convergence',sans-serif; font-size:0.85rem; color:#3D1F08; outline:none; width:100%; max-width:360px;">
          </div>
          <div style="display:flex; flex-direction:column; gap:0.3rem;">
            <label style="font-family:'Convergence',sans-serif; font-size:0.75rem; font-weight:700; color:#9B7050; text-transform:uppercase; letter-spacing:0.04em;">Email</label>
            <input type="email" value="admin@pawster.ph" style="background:#FAF0E8; border:1.5px solid #D6C0A5; border-radius:0.55rem; padding:0.5rem 0.75rem; font-family:'Convergence',sans-serif; font-size:0.85rem; color:#3D1F08; outline:none; width:100%; max-width:360px;">
          </div>
          <div style="margin-top:0.25rem;">
            <button style="background:#AB8154; color:#FAF0E8; border:none; border-radius:0.55rem; padding:0.5rem 1.25rem; font-family:'Convergence',sans-serif; font-size:0.83rem; font-weight:700; cursor:pointer;">Save Changes</button>
          </div>
        </div>
      </div>
    </div>

  </main>
</div>

<style>
.admin-section { display: none; }
.admin-section.active { display: block; }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
const SECTION_TITLES = {
  overview:     'Admin Overview',
  sellers:      'Sellers',
  adoptions:    'Adoptions',
  appointments: 'Appointments',
  listings:     'Listings',
  users:        'Users',
  settings:     'Settings',
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
</script>
</body>
</html>