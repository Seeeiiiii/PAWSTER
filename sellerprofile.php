<?php
$seller = [
    'name'        => 'Pawbites',
    'handle'      => '@pawbites.ph',
    'member_since'=> 'May 2025',
    'verified'    => true,
    'tags'        => ['Pet food', 'Treats'],
];

$metrics = [
    ['value' => '₱48,200', 'label' => 'Total sales'],
    ['value' => '12',      'label' => 'Active listings'],
];

$listings = [
    ['name' => 'Premium Dog Chew Treats (500g)', 'price' => '₱500'],
    ['name' => 'Adjustable Nylon Dog Collar',    'price' => '₱550'],
    ['name' => 'Orthopedic Dog Bed – Medium',    'price' => '₱1,000'],
];

$reviews = [];

function renderStars(int $count): string {
    return str_repeat('<span class="star">★</span>', $count);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/headlinks.php'); ?>
    <link rel="stylesheet" href="resources/css/sellerprofile.css" />
</head>
<body>
    <header style="position: relative; z-index: 100;">
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/navbar.php'); ?>
    </header>



    <!-- Edit Profile Modal -->
    <div class="modal-overlay" id="editModal" role="dialog" aria-modal="true" aria-label="Edit profile">
        <div class="modal">
            <div class="modal__header">
                <h2 class="modal__title">Edit Profile</h2>
                <button class="modal__close" id="closeModal" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                        <path d="M18.3 5.71a1 1 0 0 0-1.41 0L12 10.59 7.11 5.7A1 1 0 0 0 5.7 7.11L10.59 12 5.7 16.89a1 1 0 1 0 1.41 1.41L12 13.41l4.89 4.89a1 1 0 0 0 1.41-1.41L13.41 12l4.89-4.89a1 1 0 0 0 0-1.4z"/>
                    </svg>
                </button>
            </div>
            <div class="modal__avatar-row">
                <div class="modal__avatar-preview">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80" width="48" height="48" fill="#2d1a0e">
                        <circle cx="40" cy="28" r="18"/>
                        <ellipse cx="40" cy="70" rx="26" ry="18"/>
                    </svg>
                </div>
                <button class="btn-avatar-change" type="button">Change photo</button>
            </div>
            <div class="modal__fields">
                <div class="field-group">
                    <label class="field-label" for="editName">Shop name</label>
                    <input class="field-input" id="editName" type="text" value="Pawbites" placeholder="Shop name" />
                </div>
                <div class="field-group">
                    <label class="field-label" for="editHandle">Handle</label>
                    <input class="field-input" id="editHandle" type="text" value="@pawbites.ph" placeholder="@handle" />
                </div>
                <div class="field-group">
                    <label class="field-label" for="editTags">Tags <span class="field-hint">(comma-separated)</span></label>
                    <input class="field-input" id="editTags" type="text" value="Pet food, Treats" placeholder="e.g. Pet food, Treats" />
                </div>
            </div>
            <div class="modal__footer">
                <button class="btn-cancel" id="cancelEdit" type="button">Cancel</button>
                <button class="btn-save" id="saveEdit" type="button">Save changes</button>
            </div>
        </div>
    </div>

    <main class="dashboard">
        <section class="profile-banner" aria-label="Seller profile">
            <div class="profile-banner__avatar" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80" width="64" height="64" fill="#2d1a0e">
                    <circle cx="40" cy="28" r="18"/>
                    <ellipse cx="40" cy="70" rx="26" ry="18"/>
                </svg>
            </div>
            <div class="profile-banner__info">
                <h1 class="profile-banner__name" id="bannerName"><?= htmlspecialchars($seller['name']) ?></h1>
                <p class="profile-banner__meta">
                    <span id="bannerHandle"><?= htmlspecialchars($seller['handle']) ?></span>
                    &nbsp;
                </p>
                <div class="profile-banner__badges" id="bannerBadges">
                    <?php if ($seller['verified']): ?>
                        <span class="badge badge--verified">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="13" height="13" fill="currentColor">
                                <path d="M13.485 1.431a1.473 1.473 0 0 0-2.084 0l-6.8 6.915-2.04-2.078a1.473 1.473 0 0 0-2.084 2.084l3.084 3.12a1.473 1.473 0 0 0 2.084 0l7.84-7.956a1.473 1.473 0 0 0 0-2.085z"/>
                            </svg>
                            Verified seller
                        </span>
                    <?php endif; ?>
                    <?php foreach ($seller['tags'] as $tag): ?>
                        <span class="badge badge--tag"><?= htmlspecialchars($tag) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="profile-banner__actions">
                <button class="btn-edit" id="openEditModal" onclick="if(window.pawsterOpenModal)window.pawsterOpenModal()" aria-label="Edit profile">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="15" height="15" fill="currentColor">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zm17.71-10.21a1.003 1.003 0 0 0 0-1.42l-2.34-2.34a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.82z"/>
                    </svg>
                    Edit Profile
                </button>
            </div>
        </section>


        <section class="metrics" aria-label="Key metrics">
            <?php foreach ($metrics as $m): ?>
                <div class="metric-card">
                    <span class="metric-card__value"><?= htmlspecialchars($m['value']) ?></span>
                    <span class="metric-card__label"><?= htmlspecialchars($m['label']) ?></span>
                </div>
            <?php endforeach; ?>
        </section>

        <div class="content-grid">

            <section class="card" aria-label="My listings">
                <div class="card__header">
                    <h2 class="card__title">My listings</h2>
                    <a href="#" class="card__link">Manage Listings &rarr;</a>
                </div>
                <ul class="listing-list" role="list">
                    <?php foreach ($listings as $item): ?>
                        <li class="listing-item">
                            <div class="listing-item__thumb" aria-hidden="true"></div>
                            <div class="listing-item__info">
                                <span class="listing-item__name"><?= htmlspecialchars($item['name']) ?></span>
                                <span class="listing-item__price"><?= htmlspecialchars($item['price']) ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>

            <section class="card" aria-label="Recent reviews">
                <div class="card__header">
                    <h2 class="card__title">Recent reviews</h2>
                    <a href="#" class="card__link">View all &rarr;</a>
                </div>
                <ul class="review-list" role="list">
                    <?php if (empty($reviews)): ?>
                        <li class="review-list--empty">No reviews yet.</li>
                    <?php endif; ?>
                    <?php foreach ($reviews as $r): ?>
                        <li class="review-item">
                            <div class="review-item__stars" aria-label="<?= $r['stars'] ?> stars">
                                <?= renderStars($r['stars']) ?>
                            </div>
                            <p class="review-item__text"><?= htmlspecialchars($r['text']) ?></p>
                            <footer class="review-item__footer">
                                <span class="review-item__author"><?= htmlspecialchars($r['author']) ?></span>
                                <span class="review-item__when">&ndash; <?= htmlspecialchars($r['when']) ?></span>
                            </footer>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>

        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        var modal      = document.getElementById('editModal');
        var openBtn    = document.getElementById('openEditModal');
        var closeBtn   = document.getElementById('closeModal');
        var cancelBtn  = document.getElementById('cancelEdit');
        var saveBtn    = document.getElementById('saveEdit');
        var bannerName   = document.getElementById('bannerName');
        var bannerHandle = document.getElementById('bannerHandle');
        var bannerBadges = document.getElementById('bannerBadges');
        var editName   = document.getElementById('editName');
        var editHandle = document.getElementById('editHandle');
        var editTags   = document.getElementById('editTags');

        /* Safety check — log if any element is missing */
        var els = { modal:modal, openBtn:openBtn, closeBtn:closeBtn, cancelBtn:cancelBtn,
                    saveBtn:saveBtn, bannerName:bannerName, bannerHandle:bannerHandle,
                    bannerBadges:bannerBadges, editName:editName, editHandle:editHandle, editTags:editTags };
        Object.keys(els).forEach(function(k){ if (!els[k]) console.error('PAWSTER modal: missing element →', k); });

        if (!modal || !openBtn) return; /* can't proceed without these two */

        window.pawsterOpenModal = function () {
            editName.value   = bannerName.textContent.trim();
            editHandle.value = bannerHandle.textContent.trim();
            var tagEls = bannerBadges.querySelectorAll('.badge--tag');
            editTags.value = Array.from(tagEls).map(function(el){ return el.textContent.trim(); }).join(', ');
            modal.classList.add('modal-overlay--open');
            document.body.style.overflow = 'hidden';
            editName.focus();
        };

        window.pawsterCloseModal = function () {
            modal.classList.remove('modal-overlay--open');
            document.body.style.overflow = '';
        };

        function saveChanges() {
            var name   = editName.value.trim() || 'Pawbites';
            var handle = editHandle.value.trim() || '@pawbites.ph';
            var tags   = editTags.value.split(',').map(function(t){ return t.trim(); }).filter(Boolean);

            bannerName.textContent   = name;
            bannerHandle.textContent = handle;

            bannerBadges.querySelectorAll('.badge--tag').forEach(function(el){ el.remove(); });
            tags.forEach(function(tag) {
                var span = document.createElement('span');
                span.className   = 'badge badge--tag';
                span.textContent = tag;
                bannerBadges.appendChild(span);
            });

            saveBtn.textContent       = 'Saved!';
            saveBtn.style.background  = '#2D8C4E';
            setTimeout(function() {
                saveBtn.textContent      = 'Save changes';
                saveBtn.style.background = '';
                window.pawsterCloseModal();
            }, 900);
        }

        openBtn.addEventListener('click',  window.pawsterOpenModal);
        closeBtn.addEventListener('click',  window.pawsterCloseModal);
        cancelBtn.addEventListener('click', window.pawsterCloseModal);
        saveBtn.addEventListener('click',   saveChanges);
        modal.addEventListener('click', function(e) { if (e.target === modal) window.pawsterCloseModal(); });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('modal-overlay--open')) window.pawsterCloseModal();
        });
    });
    </script>
    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/footer.php'); ?>
    </footer>
</body>
</html>