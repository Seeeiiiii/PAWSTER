<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/config/app.php'; ?>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/controllers/navbar_mode_handler.php'; 

/**
 *
 * @var array $is_logged_in
 * @var array $order_success

 */
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Adoption</title>
<link rel="icon"  href="/PAWSTER/resources/images/logo white.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Caprasimo&family=Convergence&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="resources/css/adoption.css">
</head>
<body>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/navbar.php'; ?>

<?php
$pets = [];
$result = $db->conn->query("SELECT * FROM tblpets WHERE status = 'Available' ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pets[] = $row;
    }
}

// Check if user already has a pending request per pet
$userRequests = [];
if ($is_logged_in) {
    $uid = $_SESSION['auth_user']['userid'];
    $rRes = $db->conn->query("SELECT petid FROM tbladoptionrequest WHERE userid = $uid");
    if ($rRes) {
        while ($r = $rRes->fetch_assoc()) {
            $userRequests[] = $r['petid'];
        }
    }
}

$firstPet = $pets[0] ?? null;

// Count per category
$catCounts = ['Dogs'=>0,'Cats'=>0,'Rabbits'=>0,'Birds'=>0,'Others'=>0];
foreach ($pets as $p) {
    $cat = $p['category'] ?? 'Others';
    if (isset($catCounts[$cat])) $catCounts[$cat]++; else $catCounts['Others']++;
}
?>

<!-- SPECIES BAR -->
<div class="species-bar">
  <label class="sp-lbl active" onclick="filterCat('All',this)">
    <span class="sp-dot"></span> All (<?= count($pets) ?>)
  </label>
  <?php foreach ($catCounts as $cat => $cnt): ?>
  <label class="sp-lbl" onclick="filterCat('<?= $cat ?>',this)">
    <span class="sp-dot"></span> <?= $cat ?> (<?= $cnt ?>)
  </label>
  <?php endforeach; ?>
</div>


<!-- BODY -->
<div class="adopt-body">

  <!-- PET GRID -->
  <div class="pet-grid">
    <?php if (empty($pets)): ?>
      <div class="no-pets-msg">No pets available for adoption right now. Check back soon!</div>
    <?php else: ?>
      <?php foreach ($pets as $i => $pet):
        $docsStr = htmlspecialchars($pet['docs'] ?? '');
        $docsArr = $pet['docs'] ? explode(',', $pet['docs']) : [];
        $tags    = [];
        if (in_array('vacc',   $docsArr)) $tags[] = 'Vaccinated';
        if (in_array('deworm', $docsArr)) $tags[] = 'Dewormed';
        if (in_array('neuter', $docsArr)) $tags[] = 'Neutered';
        $imgPath = $pet['image']
            ? 'uploads/pets/' . htmlspecialchars($pet['image'])
            : '';
        $isFirst = ($i === 0);
        $alreadyRequested = in_array($pet['petid'], $userRequests);
      ?>
      <div class="pet-card <?= $isFirst ? 'selected' : '' ?>" onclick="pickPet(this)"
        data-petid="<?= $pet['petid'] ?>"
        data-category="<?= htmlspecialchars($pet['category'] ?? 'Others') ?>"
        data-name="<?= htmlspecialchars($pet['name']) ?>"
        data-sub="<?= htmlspecialchars($pet['breed']) ?> | <?= htmlspecialchars($pet['age']) ?> | <?= htmlspecialchars($pet['sex']) ?>"
        data-color="<?= htmlspecialchars($pet['color']) ?>"
        data-weight="<?= htmlspecialchars($pet['weight']) ?>"
        data-temp="<?= htmlspecialchars($pet['temperament']) ?>"
        data-kids="<?= htmlspecialchars($pet['good_with_kids']) ?>"
        data-loc="<?= htmlspecialchars($pet['location']) ?>"
        data-docs="<?= $docsStr ?>"
        data-img="<?= $imgPath ?>"
        data-requested="<?= $alreadyRequested ? '1' : '0' ?>">
        <div class="pc-img <?= $imgPath ? '' : 'pc-img-placeholder' ?>"
             <?= $imgPath ? 'style="background-image:url(' . $imgPath . ');background-size:cover;background-position:center;"' : 'style="background:#C4A882;"' ?>>
          <span class="avail-tag">Available</span>
        </div>
        <div class="pc-body">
          <p class="pc-name"><?= htmlspecialchars($pet['name']) ?></p>
          <p class="pc-breed"><?= htmlspecialchars($pet['breed']) ?> | <?= htmlspecialchars($pet['age']) ?> | <?= htmlspecialchars($pet['sex']) ?></p>
          <div class="htags">
            <?php foreach ($tags as $tag): ?>
              <span class="htag"><?= $tag ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- DETAIL PANEL -->
  <?php if ($firstPet): ?>
  <aside class="detail-panel">
    <div class="dp-img" id="dpImg"
         <?= $firstPet['image'] ? 'style="background-image:url(uploads/pets/' . htmlspecialchars($firstPet['image']) . ');background-size:cover;background-position:center;"' : 'style="background:#C4A882;"' ?>></div>
    <h4 class="dp-name" id="dpName"><?= htmlspecialchars($firstPet['name']) ?></h4>
    <p class="dp-sub" id="dpSub"><?= htmlspecialchars($firstPet['breed']) ?> | <?= htmlspecialchars($firstPet['age']) ?> | <?= htmlspecialchars($firstPet['sex']) ?></p>

    <table class="dp-table">
      <tr><td class="dp-k">Color</td><td class="dp-v" id="dpColor"><?= htmlspecialchars($firstPet['color']) ?></td></tr>
      <tr><td class="dp-k">Weight</td><td class="dp-v" id="dpWeight"><?= htmlspecialchars($firstPet['weight']) ?></td></tr>
      <tr><td class="dp-k">Temperament</td><td class="dp-v" id="dpTemp"><?= htmlspecialchars($firstPet['temperament']) ?></td></tr>
      <tr><td class="dp-k">Good With Kids</td><td class="dp-v" id="dpKids"><?= htmlspecialchars($firstPet['good_with_kids']) ?></td></tr>
      <tr><td class="dp-k">Location</td><td class="dp-v" id="dpLoc"><?= htmlspecialchars($firstPet['location']) ?></td></tr>
    </table>

    <p class="dp-docs-title">Legal Documents &amp; Health Records</p>
    <div class="dp-docs" id="dpDocs"></div>

    <button class="btn-apply" id="btnApply"
      data-petid="<?= $firstPet['petid'] ?>"
      data-requested="<?= in_array($firstPet['petid'], $userRequests) ? '1' : '0' ?>">
      <?= in_array($firstPet['petid'], $userRequests) ? 'Request Sent ✓' : 'Apply to Adopt ' . htmlspecialchars($firstPet['name']) ?>
    </button>

    <!-- Toast notification -->
    <div id="adoptToast" class="adopt-toast" style="display:none;"></div>
  </aside>
  <?php else: ?>
  <aside class="detail-panel" style="display:flex;align-items:center;justify-content:center;">
    <p style="color:#9B7050;text-align:center;">Select a pet to view details.</p>
  </aside>
  <?php endif; ?>

</div>

<!-- Mobile modal -->
<div class="modal fade" id="mobModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content mob-modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title mob-modal-title" id="mobName"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="mobBody"></div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
const IS_LOGGED_IN = <?= ($is_logged_in) ? 'true' : 'false' ?>;
const LOGIN_URL    = '/PAWSTER/login.php';

const DOCS = [
  {k:'vacc',   l:'Vaccination Certificate'},
  {k:'deworm', l:'Deworming Record'},
  {k:'neuter', l:'Neutering Certificate'},
  {k:'vet',    l:'Vet Health Certificate'},
  {k:'agree',  l:'Adoption Agreement Form'},
];

function renderDocs(str, el) {
  const active = str ? str.split(',') : [];
  el.innerHTML = DOCS.map(d => {
    const has = active.includes(d.k);
    return `<div class="doc-row"><i class="bi ${has?'bi-check-circle-fill doc-yes':'bi-circle doc-no'}"></i><span>${d.l}</span></div>`;
  }).join('');
}

function showToast(msg, success = true) {
  const t = document.getElementById('adoptToast');
  if (!t) return;
  t.textContent = msg;
  t.style.background = success ? '#5C8A5C' : '#C0735A';
  t.style.display = 'block';
  setTimeout(() => t.style.display = 'none', 3500);
}

function submitAdoptRequest(petid, petName, btn) {
  if (!IS_LOGGED_IN) {
    window.location.href = LOGIN_URL;
    return;
  }
  btn.disabled = true;
  btn.textContent = 'Sending…';

  const fd = new FormData();
  fd.append('petid', petid);

  fetch('/PAWSTER/controllers/adopt_request_controller.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      if (data.status === 'success') {
        btn.textContent = 'Request Sent ✓';
        btn.dataset.requested = '1';
        showToast(data.message, true);
      } else if (data.status === 'auth') {
        window.location.href = LOGIN_URL;
      } else if (data.status === 'duplicate') {
        btn.textContent = 'Request Sent ✓';
        showToast(data.message, false);
      } else {
        btn.disabled = false;
        btn.textContent = 'Apply to Adopt ' + petName;
        showToast(data.message, false);
      }
    })
    .catch(() => {
      btn.disabled = false;
      btn.textContent = 'Apply to Adopt ' + petName;
      showToast('Something went wrong. Try again.', false);
    });
}

// Main apply button
const mainBtn = document.getElementById('btnApply');
if (mainBtn) {
  mainBtn.addEventListener('click', () => {
    if (mainBtn.dataset.requested === '1') return;
    const petid = mainBtn.dataset.petid;
    const name  = document.getElementById('dpName').textContent;
    submitAdoptRequest(petid, name, mainBtn);
  });
}

function pickPet(card) {
  document.querySelectorAll('.pet-card').forEach(c => c.classList.remove('selected'));
  card.classList.add('selected');
  const d = card.dataset;

  if (window.innerWidth < 900) {
    document.getElementById('mobName').textContent = d.name;
    const body = document.getElementById('mobBody');
    const alreadyReq = d.requested === '1';
    body.innerHTML = `
      <p style="color:#9B7050;font-size:.83rem;margin-bottom:.8rem">${d.sub}</p>
      <table style="width:100%;font-size:.82rem;border-collapse:collapse;margin-bottom:1rem">
        ${[['Color',d.color],['Weight',d.weight],['Temperament',d.temp],['Good With Kids',d.kids],['Location',d.loc]].map(([k,v])=>`
        <tr><td style="color:#9B7050;padding:.32rem 0;border-bottom:1px solid #E3D0BC;font-weight:600;width:48%">${k}</td>
        <td style="text-align:right;padding:.32rem 0;border-bottom:1px solid #E3D0BC;color:#3D1F08">${v}</td></tr>`).join('')}
      </table>
      <p style="font-size:.7rem;font-weight:700;color:#5C3D1E;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.5rem">Legal Documents &amp; Health Records</p>
      <div id="mobDocs" style="background:#F0E2D0;border-radius:.6rem;padding:.65rem .8rem;margin-bottom:1rem"></div>
      <button id="mobApplyBtn" data-petid="${d.petid}" data-requested="${d.requested}"
        style="width:100%;background:${alreadyReq?'#888':'#AB8154'};color:#FAF0E8;border:none;border-radius:.75rem;padding:.65rem;font-family:'Convergence',sans-serif;font-weight:700;font-size:.87rem;cursor:pointer;margin-bottom:.45rem">
        ${alreadyReq ? 'Request Sent ✓' : 'Apply to Adopt ' + d.name}
      </button>
      <div id="mobToast" style="display:none;margin-top:.5rem;padding:.5rem .8rem;border-radius:.5rem;font-size:.8rem;color:#fff;text-align:center;"></div>
    `;
    renderDocs(d.docs, document.getElementById('mobDocs'));

    const mobBtn = document.getElementById('mobApplyBtn');
    mobBtn.addEventListener('click', () => {
      if (mobBtn.dataset.requested === '1') return;
      if (!IS_LOGGED_IN) { window.location.href = LOGIN_URL; return; }
      mobBtn.disabled = true;
      mobBtn.textContent = 'Sending…';
      const fd = new FormData();
      fd.append('petid', mobBtn.dataset.petid);
      fetch('/PAWSTER/controllers/adopt_request_controller.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
          const toast = document.getElementById('mobToast');
          if (res.status === 'success' || res.status === 'duplicate') {
            mobBtn.textContent = 'Request Sent ✓';
            mobBtn.dataset.requested = '1';
            if (toast) { toast.style.background='#5C8A5C'; toast.textContent=res.message; toast.style.display='block'; }
          } else {
            mobBtn.disabled = false;
            mobBtn.textContent = 'Apply to Adopt ' + d.name;
            if (toast) { toast.style.background='#C0735A'; toast.textContent=res.message; toast.style.display='block'; }
          }
        });
    });

    new bootstrap.Modal(document.getElementById('mobModal')).show();
    return;
  }

  // Desktop panel update
  document.getElementById('dpName').textContent    = d.name;
  document.getElementById('dpSub').textContent     = d.sub;
  document.getElementById('dpColor').textContent   = d.color;
  document.getElementById('dpWeight').textContent  = d.weight;
  document.getElementById('dpTemp').textContent    = d.temp;
  document.getElementById('dpKids').textContent    = d.kids;
  document.getElementById('dpLoc').textContent     = d.loc;

  const dpImg = document.getElementById('dpImg');
  if (d.img) {
    dpImg.style.backgroundImage = 'url(' + d.img + ')';
    dpImg.style.backgroundSize  = 'cover';
    dpImg.style.backgroundPosition = 'center';
  } else {
    dpImg.style.backgroundImage = 'none';
    dpImg.style.background = '#C4A882';
  }

  renderDocs(d.docs, document.getElementById('dpDocs'));

  const btn = document.getElementById('btnApply');
  btn.dataset.petid     = d.petid;
  btn.dataset.requested = d.requested;
  if (d.requested === '1') {
    btn.textContent = 'Request Sent ✓';
    btn.disabled = true;
  } else {
    btn.textContent = 'Apply to Adopt ' + d.name;
    btn.disabled = false;
  }
}

function filterCat(cat, el) {
  document.querySelectorAll('.sp-lbl').forEach(l => l.classList.remove('active'));
  el.classList.add('active');
  document.querySelectorAll('.pet-card').forEach(card => {
    const show = cat === 'All' || card.dataset.category === cat;
    card.style.display = show ? '' : 'none';
  });
}

// Init first pet docs
<?php if ($firstPet): ?>
renderDocs('<?= htmlspecialchars($firstPet['docs']) ?>', document.getElementById('dpDocs'));
<?php endif; ?>
</script>
</body>
</html>