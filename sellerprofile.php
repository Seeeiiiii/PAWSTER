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
    ['value' => '312',     'label' => 'Orders fulfilled'],
    ['value' => '4.9★',    'label' => 'Average rating'],
    ['value' => '12',      'label' => 'Active listing'],
];

$listings = [
    ['name' => 'Premium Dog Chew Treats (500g)', 'price' => '₱500'],
    ['name' => 'Adjustable Nylon Dog Collar',    'price' => '₱550'],
    ['name' => 'Orthopedic Dog Bed – Medium',    'price' => '₱1,000'],
];

$reviews = [
    [
        'stars'     => 5,
        'text'      => 'My dog loves the treats! Great quality and fast delivery.',
        'author'    => 'Juan D.',
        'when'      => '1day ago',
    ],
    [
        'stars'     => 5,
        'text'      => 'Packaging was great, product exactly as described.',
        'author'    => 'Luna H.',
        'when'      => '2days ago',
    ],
    [
        'stars'     => 5,
        'text'      => 'Good treats, will definitely re-order for my dogs.',
        'author'    => 'Heneral L.',
        'when'      => '1week ago',
    ],
];

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
    <header>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/navbar.php'); ?>
    </header>



    <main class="dashboard">
        <section class="profile-banner" aria-label="Seller profile">
            <div class="profile-banner__avatar" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80" width="64" height="64" fill="#2d1a0e">
                    <circle cx="40" cy="28" r="18"/>
                    <ellipse cx="40" cy="70" rx="26" ry="18"/>
                </svg>
            </div>

            <div class="profile-banner__info">
                <h1 class="profile-banner__name"><?= htmlspecialchars($seller['name']) ?></h1>
                <p class="profile-banner__meta">
                    <?= htmlspecialchars($seller['handle']) ?>
                    &nbsp;•&nbsp;
                    Member since <?= htmlspecialchars($seller['member_since']) ?>
                </p>
                <div class="profile-banner__badges">
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
                <button class="btn-edit" aria-label="Edit profile">
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
                    <h2 class="card__title">My listing</h2>
                    <a href="#" class="card__link">View all &rarr;</a>
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
    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/footer.php'); ?>
    </footer>
</body>
</html>