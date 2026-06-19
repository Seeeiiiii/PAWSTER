<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/config/app.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/controllers/app_form_controller.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/controllers/sellerprofile_controller.php';

/**
 * @var int $listing_count
 * @var array $listings
 */

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/headlinks.php'); ?>
    <link rel="stylesheet" href="resources/css/sellerprofile.css" />
    <style>
        .badge--pending {
            background: #b07d3a;
            color: #fff;
        }
    </style>
</head>

<body>
    <header style="position: relative; z-index: 100;">
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/navbar.php');
        include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/controllers/navbar_mode_handler.php'; ?>
    </header>

    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content pawster-modal">

                <div class="modal-header pawster-modal__header">
                    <h5 class="modal-title pawster-modal__title" id="editModalLabel">Edit Profile</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body pawster-modal__body">
                    <div class="pawster-modal__fields">
                        <div class="field-group">
                            <label class="field-label" for="editName">Business name</label>
                            <input class="field-input" id="editName" type="text"
                                value="<?= htmlspecialchars($business_name) ?>"
                                placeholder="Business name" />
                        </div>
                        <div class="field-group">
                            <label class="field-label" for="editContact">Contact number</label>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span style="background: #f0f0f0; padding: 0.5rem 0.75rem; border-radius: 8px; font-family: monospace;">+63</span>
                                <input class="field-input" id="editContact" type="text"
                                    value="<?= preg_replace('/^\+63/', '', htmlspecialchars($contact_num)) ?>"
                                    placeholder="9123456789" maxlength="10"
                                    inputmode="numeric" pattern="\d{10}"
                                    title="Enter 10 digits" style="flex:1;" />
                            </div>
                            <small style="font-size: 0.75rem; color: #666;">Enter 10 digits (numbers only)</small>
                        </div>
                        <div class="field-group">
                            <label class="field-label" for="editAddress">Address</label>
                            <input class="field-input" id="editAddress" type="text"
                                value="<?= htmlspecialchars($address) ?>"
                                placeholder="Address" />
                        </div>
                    </div>
                </div>

                <div class="modal-footer pawster-modal__footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-save" id="saveEdit">Save changes</button>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="listingsModal" tabindex="-1" aria-labelledby="listingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content pawster-modal">

                <div class="modal-header pawster-modal__header">
                    <h5 class="modal-title pawster-modal__title" id="listingsModalLabel">Manage Listings</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body pawster-modal__body">


                    <form action="/PAWSTER/sellerprofile.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_listing">
                        <div class="pawster-modal__fields" style="margin-bottom:1.25rem;">
                            <p style="font-family:'Convergence',sans-serif; color:#9B7050; font-weight:600; margin-bottom:.75rem;">
                                Add New Listing
                            </p>
                            <?php
                            $allowed_messages = [
                                'Listing added successfully!',
                                'Failed to add listing. Check your photo (PNG, max 5MB) and try again.'
                            ];
                            if (!empty($_SESSION['message']) && in_array($_SESSION['message'], $allowed_messages)):
                                $msg = $_SESSION['message'];
                                // success message = green, error message = red
                                $color = ($msg === 'Listing added successfully!') ? '#2D8C4E' : '#c0392b';
                            ?>
                                <p style="color:<?= $color ?>; font-family:'Convergence',sans-serif; font-size:.85rem; margin-bottom:.5rem;">
                                    <?= htmlspecialchars($msg) ?>
                                </p>
                                <?php unset($_SESSION['message']); ?>
                            <?php endif; ?>
                            <div class="field-group">
                                <label class="field-label" for="new_category">Primary Category</label>
                                <select class="field-input" name="primary_category" id="new_category">
                                    <option value="Pet Food">Pet Food</option>
                                    <option value="Grooming Supplies">Grooming Supplies</option>
                                    <option value="Pet Accessories">Pet Accessories</option>
                                    <option value="Pet Clothes">Pet Clothes</option>
                                </select>
                            </div>
                            <div class="field-group">
                                <label class="field-label" for="new_brand">Brand Name</label>
                                <input class="field-input" name="brand_name" id="new_brand" type="text" placeholder="e.g. Pedigree" />
                            </div>
                            <div class="field-group">
                                <label class="field-label" for="new_desc">Product Description</label>
                                <input class="field-input" name="product_desc" id="new_desc" type="text" placeholder="e.g. Good for all pets" />
                            </div>
                            <div class="field-group">
                                <label class="field-label" for="new_price">Price (₱)</label>
                                <input class="field-input" name="price" id="new_price" type="number" min="0.01" step="0.01" placeholder="e.g. 250.00" />
                            </div>
                            <div class="field-group">
                                <label class="field-label" for="new_photo">Product Photo <span style="font-weight:400; font-size:.8rem;">(PNG only, max 5MB — optional)</span></label>
                                <div class="upload-box" id="new_photo_box" style="border:2px dashed #9B7050; border-radius:12px; padding:1rem; text-align:center; cursor:pointer; font-family:'Convergence',sans-serif; color:#9B7050;">
                                    <i class="bi bi-file-earmark-image" style="font-size:1.5rem;"></i>
                                    <div style="margin-top:.4rem; font-size:.85rem;">Click to upload product photo</div>
                                    <input type="file" name="product_photo" id="new_photo" accept="image/png" style="display:none;" />
                                    <div id="new_photo_name" style="margin-top:.3rem; font-size:.8rem; color:#555;"></div>
                                </div>
                            </div>
                            <button type="submit" class="btn-save" style="margin-top:.5rem;">
                                + Add Listing
                            </button>
                        </div>
                    </form>

                    <hr class="listing-divider">

                    <button type="button" id="toggleCurrentListings"
                        style="background:none; border:none; cursor:pointer; font-family:'Convergence',sans-serif; color:#9B7050; font-weight:600; font-size:1rem; padding:0; margin:.75rem 0; display:flex; align-items:center; gap:.4rem; width:100%;">
                        Current Listings
                        <svg id="currentListingsChevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="transition:transform .2s;">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </button>
                    <div id="existingListings" style="display:none;">
                        <?php if (empty($listings)): ?>
                            <p style="text-align:center; color:#9B7050; font-family:'Convergence',sans-serif;">
                                No listings found for this account.
                            </p>
                        <?php else: ?>
                            <div class="pawster-modal__fields">
                                <?php foreach ($listings as $i => $item): ?>
                                    <div class="listing-edit-row" data-productid="<?= (int)$item['productid'] ?>">
                                        <div class="listing-edit-row__num"><?= $i + 1 ?></div>
                                        <div class="listing-edit-row__fields">
                                            <div class="field-group">
                                                <label class="field-label">Brand name</label>
                                                <input class="field-input edit-brand" type="text"
                                                    value="<?= htmlspecialchars($item['brand_name']) ?>"
                                                    placeholder="Brand name" />
                                            </div>
                                            <div class="field-group">
                                                <label class="field-label">Description</label>
                                                <input class="field-input edit-desc" type="text"
                                                    value="<?= htmlspecialchars($item['product_desc']) ?>"
                                                    placeholder="Product description" />
                                            </div>
                                            <div class="field-group">
                                                <label class="field-label">Price (₱)</label>
                                                <input class="field-input edit-price" type="number" min="0.01" step="0.01"
                                                    value="<?= number_format((float)$item['price'], 2, '.', '') ?>"
                                                    placeholder="e.g. 250.00" />
                                            </div>
                                            <div class="field-group">
                                                <label class="field-label">Category</label>
                                                <select class="field-input edit-cat">
                                                    <?php foreach (['Pet Food', 'Pet Accessories', 'Pet Clothes', 'Grooming Supplies'] as $cat): ?>
                                                        <option value="<?= htmlspecialchars($cat) ?>"
                                                            <?= $cat === $item['primary_category'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($cat) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="field-group">
                                                <label class="field-label">Product Photo <span style="font-weight:400; font-size:.8rem;">(PNG only, max 5MB)</span></label>
                                                <div class="upload-box edit-photo-box" style="border:2px dashed #9B7050; border-radius:12px; padding:.75rem; text-align:center; cursor:pointer; font-family:'Convergence',sans-serif; color:#9B7050;">
                                                    <i class="bi bi-file-earmark-image" style="font-size:1.25rem;"></i>
                                                    <div style="margin-top:.3rem; font-size:.8rem;">Click to replace photo (optional)</div>
                                                    <input type="file" class="edit-photo-input" accept="image/png" style="display:none;" />
                                                    <div class="edit-photo-name" style="margin-top:.3rem; font-size:.8rem; color:#555;"></div>
                                                </div>
                                            </div>
                                            <div style="display:flex; align-items:center; gap:.75rem; margin-top:.5rem;">
                                                <button type="button" class="btn-save save-listing-btn" style="flex:1;">
                                                    Save changes
                                                </button>
                                                <button type="button" class="delete-listing-btn" style="background:#c0392b; color:#fff; border:none; border-radius:8px; padding:.45rem .9rem; cursor:pointer; font-family:'Convergence',sans-serif; font-size:.85rem;">
                                                    Delete
                                                </button>
                                                <span class="save-listing-msg" style="font-family:'Convergence',sans-serif; font-size:.82rem; display:none;"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($i < count($listings) - 1): ?>
                                        <hr class="listing-divider">
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="modal-footer pawster-modal__footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>


    <main class="dashboard">

        <section class="profile-banner" aria-label="Seller profile">
            <div class="profile-banner__info">
                <h1 class="profile-banner__name" id="bannerName">
                    <?= htmlspecialchars($business_name ?: 'Unnamed Business') ?>
                </h1>
                <p class="profile-banner__meta">
                    <span id="bannerContact"><?= htmlspecialchars($contact_num) ?></span>
                    &nbsp;·&nbsp;
                    <span id="bannerAddress"><?= htmlspecialchars($address) ?></span>
                </p>
                <div class="profile-banner__badges" id="bannerBadges">

                    <?php if ($db_status !== ''): ?>
                        <span class="badge <?= $is_verified ? 'badge--verified' : 'badge--pending' ?>">
                            <?php if ($is_verified): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="13" height="13" fill="currentColor">
                                    <path d="M13.485 1.431a1.473 1.473 0 0 0-2.084 0l-6.8 6.915-2.04-2.078a1.473 1.473 0 0 0-2.084 2.084l3.084 3.12a1.473 1.473 0 0 0 2.084 0l7.84-7.956a1.473 1.473 0 0 0 0-2.085z" />
                                </svg>
                            <?php endif; ?>
                            <?= ucfirst(htmlspecialchars($db_status)) ?> seller
                        </span>
                    <?php endif; ?>

                    <?php if ($primary_category): ?>
                        <span class="badge badge--tag" id="bannerTag">
                            <?= htmlspecialchars($primary_category) ?>
                        </span>
                    <?php endif; ?>

                </div>
            </div>
            <div class="profile-banner__actions">
                <button class="btn-edit" data-bs-toggle="modal" data-bs-target="#editModal" aria-label="Edit profile">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="15" height="15" fill="currentColor">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zm17.71-10.21a1.003 1.003 0 0 0 0-1.42l-2.34-2.34a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.82z" />
                    </svg>
                    Edit Profile
                </button>
            </div>
        </section>

        <section class="metrics" aria-label="Key metrics">
            <div class="metric-card">
                <span class="metric-card__value"><?= $member_name ? htmlspecialchars($member_name) : '—' ?></span>
                <span class="metric-card__label">Account holder</span>
            </div>
            <div class="metric-card">
                <span class="metric-card__value" id="listingCount"><?= $listing_count ?></span>
                <span class="metric-card__label">Active listings</span>
            </div>
        </section>

        <div class="content-grid">


            <section class="card" aria-label="My listings">
                <div class="card__header">
                    <h2 class="card__title">My listings</h2>
                    <a href="#" class="card__link"
                        data-bs-toggle="modal" data-bs-target="#listingsModal">
                        Manage Listings &rarr;
                    </a>
                </div>
                <ul class="listing-list" role="list" id="listingDisplay">
                    <?php if (empty($listings)): ?>
                        <li class="review-list--empty">No listings yet.</li>
                    <?php endif; ?>
                    <?php foreach ($listings as $item): ?>
                        <li class="listing-item" data-productid="<?= (int)$item['productid'] ?>">
                            <div class="listing-item__thumb" aria-hidden="true">
                                <?php if (!empty($item['productimage'])): ?>
                                    <img src="/PAWSTER/resources/images/<?= htmlspecialchars($item['productimage']) ?>"
                                        alt="<?= htmlspecialchars($item['brand_name']) ?>"
                                        style="width:100%; height:100%; object-fit:cover; border-radius:inherit;">
                                <?php endif; ?>
                            </div>
                            <div class="listing-item__info">
                                <span class="listing-item__name">
                                    <?= htmlspecialchars($item['brand_name']) ?>
                                    — <?= htmlspecialchars($item['product_desc']) ?>
                                </span>
                                <span class="listing-item__price">
                                    ₱<?= number_format((float)$item['price'], 2) ?>
                                </span>
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
                    <li class="review-list--empty">No reviews yet.</li>
                </ul>
            </section>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/PAWSTER/resources/js/sellerprofile.js"></script>
    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/footer.php'); ?>
    </footer>
</body>

</html>