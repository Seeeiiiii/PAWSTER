<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pawster – Adoption</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Caprasimo&family=Convergence&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="resources/css/adoption.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="adopt-nav">
  <div class="nav-brand">
    <img src="resources/images/Logo.png" alt="PAWSTER" class="nav-logo">
    <span class="nav-brand-txt">PAWSTER</span>
  </div>
  <div class="nav-search-wrap">
    <i class="bi bi-search"></i>
    <input type="text" placeholder="Search pets, breeds…">
  </div>
  <div class="nav-user-pill">
    <i class="bi bi-person-circle"></i>
    <span>| User</span>
    <i class="bi bi-chevron-down"></i>
  </div>
</nav>

<!-- SPECIES TABS -->
<div class="species-bar">
  <label class="sp-lbl active"><input type="radio" name="sp" checked hidden><span class="sp-dot"></span>Dogs (64)</label>
  <label class="sp-lbl"><input type="radio" name="sp" hidden><span class="sp-dot"></span>Cats (59)</label>
  <label class="sp-lbl"><input type="radio" name="sp" hidden><span class="sp-dot"></span>Rabbits (12)</label>
  <label class="sp-lbl"><input type="radio" name="sp" hidden><span class="sp-dot"></span>Birds (21)</label>
  <label class="sp-lbl"><input type="radio" name="sp" hidden><span class="sp-dot"></span>Others (9)</label>
</div>

<!-- BODY -->
<div class="adopt-body">

  <!-- PET GRID -->
  <div class="pet-grid">
    <div class="pet-card selected" onclick="pickPet(this)"
      data-name="Mochi" data-sub="Shih Tzu | 2 years old | Female"
      data-color="White &amp; Brown" data-weight="4.2kg" data-temp="Gentle, Playful"
      data-kids="Yes" data-loc="Calamba, Laguna" data-docs="vacc,deworm,neuter,vet"
      data-bg="#C4A882">
      <div class="pc-img" style="background:#C4A882;"><span class="avail-tag">Available</span></div>
      <div class="pc-body">
        <p class="pc-name">Mochi</p>
        <p class="pc-breed">Shih Tzu | 2 yrs | Female</p>
        <div class="htags"><span class="htag">Vaccinated</span><span class="htag">Neutered</span></div>
      </div>
    </div>

    <div class="pet-card" onclick="pickPet(this)"
      data-name="Doga" data-sub="Pitbull | 8 years old | Male"
      data-color="Brown" data-weight="28kg" data-temp="Calm, Loyal"
      data-kids="Yes" data-loc="Sta. Rosa, Laguna" data-docs="vacc"
      data-bg="#5C4030">
      <div class="pc-img" style="background:#5C4030;"></div>
      <div class="pc-body">
        <p class="pc-name">Doga</p>
        <p class="pc-breed">Pitbull | 8 yrs | Male</p>
        <div class="htags"><span class="htag">Vaccinated</span></div>
      </div>
    </div>

    <div class="pet-card" onclick="pickPet(this)"
      data-name="Dora" data-sub="Chihuahua | 2 years old | Male"
      data-color="Cream" data-weight="2.1kg" data-temp="Playful, Alert"
      data-kids="No" data-loc="Biñan, Laguna" data-docs="neuter"
      data-bg="#B09878">
      <div class="pc-img" style="background:#B09878;"><span class="avail-tag">Available</span></div>
      <div class="pc-body">
        <p class="pc-name">Dora</p>
        <p class="pc-breed">Chihuahua | 2 yrs | Male</p>
        <div class="htags"><span class="htag">Neutered</span></div>
      </div>
    </div>

    <div class="pet-card" onclick="pickPet(this)"
      data-name="Miko" data-sub="Persian | 1 year old | Male"
      data-color="Gray" data-weight="3.5kg" data-temp="Gentle, Quiet"
      data-kids="Yes" data-loc="Los Baños, Laguna" data-docs="vacc,deworm"
      data-bg="#D6C9BA">
      <div class="pc-img" style="background:#D6C9BA;"></div>
      <div class="pc-body">
        <p class="pc-name">Miko</p>
        <p class="pc-breed">Persian | 1 yr | Male</p>
        <div class="htags"><span class="htag">Vaccinated</span><span class="htag">Dewormed</span></div>
      </div>
    </div>

    <div class="pet-card" onclick="pickPet(this)"
      data-name="Breanna" data-sub="Himalayan | 4 years old | Female"
      data-color="White &amp; Gray" data-weight="4.8kg" data-temp="Calm, Affectionate"
      data-kids="Yes" data-loc="San Pedro, Laguna" data-docs="deworm"
      data-bg="#987860">
      <div class="pc-img" style="background:#987860;"><span class="avail-tag">Available</span></div>
      <div class="pc-body">
        <p class="pc-name">Breanna</p>
        <p class="pc-breed">Himalayan | 4 yrs | Female</p>
        <div class="htags"><span class="htag">Dewormed</span></div>
      </div>
    </div>

    <div class="pet-card" onclick="pickPet(this)"
      data-name="May" data-sub="Shih Tzu | 2 years old | Female"
      data-color="Gold &amp; White" data-weight="3.9kg" data-temp="Playful, Social"
      data-kids="Yes" data-loc="Cabuyao, Laguna" data-docs="vacc,neuter"
      data-bg="#C8A870">
      <div class="pc-img" style="background:#C8A870;"><span class="avail-tag">Available</span></div>
      <div class="pc-body">
        <p class="pc-name">May</p>
        <p class="pc-breed">Shih Tzu | 2 yrs | Female</p>
        <div class="htags"><span class="htag">Vaccinated</span><span class="htag">Neutered</span></div>
      </div>
    </div>
  </div>

  <!-- DETAIL PANEL -->
  <aside class="detail-panel">
    <div class="dp-img" id="dpImg" style="background:#C4A882;"></div>
    <h4 class="dp-name" id="dpName">Mochi</h4>
    <p class="dp-sub" id="dpSub">Shih Tzu | 2 years old | Female</p>

    <table class="dp-table">
      <tr><td class="dp-k">Color</td><td class="dp-v" id="dpColor">White &amp; Brown</td></tr>
      <tr><td class="dp-k">Weight</td><td class="dp-v" id="dpWeight">4.2kg</td></tr>
      <tr><td class="dp-k">Temperament</td><td class="dp-v" id="dpTemp">Gentle, Playful</td></tr>
      <tr><td class="dp-k">Good With Kids</td><td class="dp-v" id="dpKids">Yes</td></tr>
      <tr><td class="dp-k">Location</td><td class="dp-v" id="dpLoc">Calamba, Laguna</td></tr>
    </table>

    <p class="dp-docs-title">Legal Documents &amp; Health Records</p>
    <div class="dp-docs" id="dpDocs"></div>

    <button class="btn-apply" id="btnApply">Apply to Adopt Mochi</button>
    <button class="btn-wish"><i class="bi bi-heart"></i> Save to Wishlist</button>
  </aside>

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

function pickPet(card) {
  document.querySelectorAll('.pet-card').forEach(c => c.classList.remove('selected'));
  card.classList.add('selected');
  const d = card.dataset;

  if (window.innerWidth < 900) {
    document.getElementById('mobName').textContent = d.name;
    const body = document.getElementById('mobBody');
    const docsDiv = document.createElement('div');
    body.innerHTML = `
      <p style="color:#9B7050;font-size:.83rem;margin-bottom:.8rem">${d.sub}</p>
      <table style="width:100%;font-size:.82rem;border-collapse:collapse;margin-bottom:1rem">
        ${[['Color',d.color],['Weight',d.weight],['Temperament',d.temp],['Good With Kids',d.kids],['Location',d.loc]].map(([k,v])=>`
        <tr><td style="color:#9B7050;padding:.32rem 0;border-bottom:1px solid #E3D0BC;font-weight:600;width:48%">${k}</td>
        <td style="text-align:right;padding:.32rem 0;border-bottom:1px solid #E3D0BC;color:#3D1F08">${v}</td></tr>`).join('')}
      </table>
      <p style="font-size:.7rem;font-weight:700;color:#5C3D1E;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.5rem">Legal Documents &amp; Health Records</p>
      <div id="mobDocs" style="background:#F0E2D0;border-radius:.6rem;padding:.65rem .8rem;margin-bottom:1rem"></div>
      <button onclick="bootstrap.Modal.getInstance(document.getElementById('mobModal')).hide()" style="width:100%;background:#AB8154;color:#FAF0E8;border:none;border-radius:.75rem;padding:.65rem;font-family:'Convergence',sans-serif;font-weight:700;font-size:.87rem;cursor:pointer;margin-bottom:.45rem">Apply to Adopt ${d.name}</button>
      <button style="width:100%;background:transparent;color:#AB8154;border:1.5px solid #D6C0A5;border-radius:.75rem;padding:.55rem;font-family:'Convergence',sans-serif;font-weight:600;font-size:.87rem;cursor:pointer"><i class="bi bi-heart"></i> Save to Wishlist</button>
    `;
    renderDocs(d.docs, document.getElementById('mobDocs'));
    new bootstrap.Modal(document.getElementById('mobModal')).show();
    return;
  }

  document.getElementById('dpName').textContent = d.name;
  document.getElementById('dpSub').textContent = d.sub;
  document.getElementById('dpColor').textContent = d.color;
  document.getElementById('dpWeight').textContent = d.weight;
  document.getElementById('dpTemp').textContent = d.temp;
  document.getElementById('dpKids').textContent = d.kids;
  document.getElementById('dpLoc').textContent = d.loc;
  document.getElementById('dpImg').style.background = d.bg;
  document.getElementById('btnApply').textContent = 'Apply to Adopt ' + d.name;
  renderDocs(d.docs, document.getElementById('dpDocs'));
}

document.querySelectorAll('.sp-lbl').forEach(l => {
  l.querySelector('input').addEventListener('change', () => {
    document.querySelectorAll('.sp-lbl').forEach(x => x.classList.remove('active'));
    l.classList.add('active');
  });
});

// Init docs panel
renderDocs('vacc,deworm,neuter,vet', document.getElementById('dpDocs'));
</script>
</body>
</html>
