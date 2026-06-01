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
<link rel="stylesheet" href="resources/css/userprof.css">
</head>
<body>

<div class="profile-page">

  <!-- COVER -->
  <div class="cover-bar">
    <button class="cover-menu"><i class="bi bi-three-dots"></i></button>
  </div>

  <!-- PROFILE HEADER -->
  <div class="prof-header">
    <div class="prof-avatar">JD</div>
    <div class="prof-details">
      <div class="prof-top-row">
        <div>
          <h3 class="prof-name">Juan Dela Cruz</h3>
          <p class="prof-meta">juan.dela.cruz@gmail.com | Calamba, Laguna</p>
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

      <div class="notif-banner">
        Adoption in progress, your application for Mochi (Shih Tzu) is currently under review. We'll notify you within 3–5 business days.
      </div>

      <div class="two-col">

        <!-- Recent Orders -->
        <div class="info-card">
          <div class="icard-head">
            <span class="icard-title">Recent Orders</span>
            <a href="#" class="view-all-lnk">View All →</a>
          </div>

          <div class="order-row">
            <div class="thumb-sq"></div>
            <div class="row-info">
              <p class="row-name">Premium Dog Chew Treats</p>
              <p class="row-meta">PawBites PH | Order #PW-00328 | May 14</p>
            </div>
            <div class="order-right">
              <p class="order-price">₱189</p>
              <span class="pill pill-green">Delivered</span>
            </div>
          </div>

          <div class="order-row">
            <div class="thumb-sq"></div>
            <div class="row-info">
              <p class="row-name">Interactive Dog Toy Bundle</p>
              <p class="row-meta">NaturePet | Order #PW-00328 | May 16</p>
            </div>
            <div class="order-right">
              <p class="order-price">₱256</p>
              <span class="pill pill-blue">In Transit</span>
            </div>
          </div>

          <div class="order-row">
            <div class="thumb-sq"></div>
            <div class="row-info">
              <p class="row-name">Hypoallergenic Pet Shampoo</p>
              <p class="row-meta">PlayPaws | Order #PW-00115 | May 13</p>
            </div>
            <div class="order-right">
              <p class="order-price">₱499</p>
              <span class="pill pill-green">Delivered</span>
            </div>
          </div>
        </div>

        <!-- Upcoming Appointments -->
        <div class="info-card">
          <div class="icard-head">
            <span class="icard-title">Upcoming Appointments</span>
            <a href="#" class="view-all-lnk">View All →</a>
          </div>

          <div class="appt-row">
            <div class="thumb-sq"></div>
            <div class="row-info">
              <p class="row-name">Grooming | Mochi</p>
              <p class="row-meta">May 20, 2026 | 1:00 PM | Calamba Hub</p>
            </div>
            <span class="pill pill-blue">Confirmed</span>
          </div>

          <div class="appt-row">
            <div class="thumb-sq"></div>
            <div class="row-info">
              <p class="row-name">Vet Check Up | Mochi</p>
              <p class="row-meta">June 3, 2026 | 10:00 AM | Pawster Vet</p>
            </div>
            <span class="pill pill-orange">Pending</span>
          </div>

          <div class="appt-row">
            <div class="thumb-sq"></div>
            <div class="row-info">
              <p class="row-name">Meet &amp; Greet | Mochi</p>
              <p class="row-meta">June 7, 2026 | 12:00 PM | Tanauan Shelter</p>
            </div>
            <span class="pill pill-tan">Scheduled</span>
          </div>
        </div>

      </div><!-- /two-col -->

      <!-- Account Details -->
      <div class="info-card mt-row">
        <div class="icard-head" style="margin-bottom:.7rem;">
          <span class="icard-title">Account Details</span>
        </div>
        <div class="acct-row">
          <div class="acct-key"><i class="bi bi-person"></i> Full Name</div>
          <div class="acct-val">Juan Dela Cruz</div>
        </div>
        <div class="acct-row">
          <div class="acct-key"><i class="bi bi-envelope"></i> Email</div>
          <div class="acct-val">JuanDelaCruz@gmail.com</div>
        </div>
        <div class="acct-row">
          <div class="acct-key"><i class="bi bi-telephone"></i> Phone</div>
          <div class="acct-val">+63 9917737162</div>
        </div>
        <div class="acct-row acct-last">
          <div class="acct-key"><i class="bi bi-geo-alt"></i> Address</div>
          <div class="acct-val">Calamba, Laguna</div>
        </div>
      </div>

    </div><!-- /orders -->

    <!-- ── APPOINTMENTS TAB ── -->
    <div class="tab-pane" id="tab-appointments">
      <div class="info-card">
        <div class="icard-head"><span class="icard-title">All Appointments</span></div>
        <div class="appt-row">
          <div class="thumb-sq"></div>
          <div class="row-info"><p class="row-name">Grooming | Mochi</p><p class="row-meta">May 20, 2026 | 1:00 PM | Calamba Hub</p></div>
          <span class="pill pill-blue">Confirmed</span>
        </div>
        <div class="appt-row">
          <div class="thumb-sq"></div>
          <div class="row-info"><p class="row-name">Vet Check Up | Mochi</p><p class="row-meta">June 3, 2026 | 10:00 AM | Pawster Vet</p></div>
          <span class="pill pill-orange">Pending</span>
        </div>
        <div class="appt-row">
          <div class="thumb-sq"></div>
          <div class="row-info"><p class="row-name">Meet &amp; Greet | Mochi</p><p class="row-meta">June 7, 2026 | 12:00 PM | Tanauan Shelter</p></div>
          <span class="pill pill-tan">Scheduled</span>
        </div>
      </div>
    </div>

    <!-- ── ADOPTIONS TAB ── -->
    <div class="tab-pane" id="tab-adoptions">
      <div class="info-card">
        <div class="icard-head"><span class="icard-title">Adoption History</span></div>
        <div class="order-row">
          <div class="thumb-sq" style="background:#C4A882;"></div>
          <div class="row-info"><p class="row-name">Mochi – Shih Tzu</p><p class="row-meta">Applied May 28, 2026</p></div>
          <div class="order-right"><span class="pill pill-blue">Under Review</span></div>
        </div>
      </div>
    </div>

    <!-- ── ACCOUNT INFO TAB ── -->
    <div class="tab-pane" id="tab-account">
      <div class="info-card">
        <div class="icard-head" style="margin-bottom:.7rem;"><span class="icard-title">Account Details</span></div>
        <div class="acct-row"><div class="acct-key"><i class="bi bi-person"></i> Full Name</div><div class="acct-val">Juan Dela Cruz</div></div>
        <div class="acct-row"><div class="acct-key"><i class="bi bi-envelope"></i> Email</div><div class="acct-val">JuanDelaCruz@gmail.com</div></div>
        <div class="acct-row"><div class="acct-key"><i class="bi bi-telephone"></i> Phone</div><div class="acct-val">+63 9917737162</div></div>
        <div class="acct-row acct-last"><div class="acct-key"><i class="bi bi-geo-alt"></i> Address</div><div class="acct-val">Calamba, Laguna</div></div>
      </div>
    </div>

  </div><!-- /tab-area -->
</div><!-- /profile-page -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
  });
});
</script>
</body>
</html>
