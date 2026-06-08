<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/config/app.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/controllers/app_form_controller.php';

$db   = new DatabaseConnection();
$conn = $db->conn;


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_listing') {

    $userid = (int)($_SESSION['auth_user']['userid'] ?? 0);
    if (!$userid) {
        redirect('Please log in.', 'login.php');
    }


    $r = $conn->prepare("SELECT formid FROM tblsellerstatus WHERE userid = ? ORDER BY created_at DESC LIMIT 1");
    $r->bind_param("i", $userid);
    $r->execute();
    $r->bind_result($sellerid);
    $r->fetch();
    $r->close();

    if (!$sellerid) {
        redirect('No seller application found.', 'sellerprofile.php');
    }

    $category = trim($_POST['primary_category'] ?? '');
    $brand    = trim($_POST['brand_name']       ?? '');
    $desc     = trim($_POST['product_desc']     ?? '');
    $price    = (float)($_POST['price']         ?? 0);
    $photo    = $_FILES['product_photo']        ?? [];

    if (!$category || !$brand || !$desc || $price <= 0) {
        redirect('Please fill in all listing fields including a valid price.', 'sellerprofile.php');
    }

    $controller = new ApplicationFormController();
    $success    = $controller->addListing($category, $brand, $desc, $price, $sellerid, $photo);

    if ($success) {
        redirect('Listing added successfully!', 'sellerprofile.php');
    } else {
        redirect('Failed to add listing. Check your photo (PNG, max 5MB) and try again.', 'sellerprofile.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_listing') {
    header('Content-Type: application/json');

    $userid    = (int)($_SESSION['auth_user']['userid'] ?? 0);
    $productid = (int)($_POST['productid']        ?? 0);
    $category  = trim($_POST['primary_category']  ?? '');
    $brand     = trim($_POST['brand_name']         ?? '');
    $desc      = trim($_POST['product_desc']       ?? '');
    $price     = (float)($_POST['price']           ?? 0);
    $photo     = $_FILES['product_photo']          ?? [];

    if (!$userid) {
        echo json_encode(['success' => false, 'error' => 'Not logged in.']);
        exit();
    }
    if (!$productid || !$category || !$brand || !$desc || $price <= 0) {
        echo json_encode(['success' => false, 'error' => 'All fields including price are required.']);
        exit();
    }

    $controller = new ApplicationFormController();
    $success    = $controller->updateListing($productid, $userid, $category, $brand, $desc, $price, $photo);

    echo json_encode(
        $success
            ? ['success' => true]
            : ['success' => false, 'error' => 'Update failed. Check ownership, photo format (PNG, max 5MB), or try again.']
    );
    exit();
}


$userid = isset($_GET['userid'])
    ? (int) $_GET['userid']
    : (int) ($_SESSION['auth_user']['userid'] ?? 0);


$is_verified  = false;
$db_status     = '';
$formid       = null;
$business_name = '';
$contact_num   = '';
$address       = '';
$member_name   = '';
$primary_category = '';
$listings      = [];

if ($userid > 0) {

    $stmt = $conn->prepare(
        "SELECT status, formid
         FROM tblsellerstatus
         WHERE userid = ?
         ORDER BY created_at DESC
         LIMIT 1"
    );
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $stmt->bind_result($db_status, $formid);
    $stmt->fetch();
    $stmt->close();

    $db_status   = (string)($db_status ?? '');
    $is_verified = (strtolower($db_status) === 'verified');


    if ($formid) {
        $stmt2 = $conn->prepare(
            "SELECT businessname, contactnum, address
             FROM tblsellerprofile
             WHERE sellerid = ?
             LIMIT 1"
        );
        $stmt2->bind_param("i", $formid);
        $stmt2->execute();
        $stmt2->bind_result($business_name, $contact_num, $address);
        $stmt2->fetch();
        $stmt2->close();


        $stmt3 = $conn->prepare(
            "SELECT productid, brand_name, product_desc, primary_category, price, productimage
     FROM tblsellerproduct
     WHERE sellerid = ?
     ORDER BY productid ASC"
        );
        $stmt3->bind_param("i", $formid);
        $stmt3->execute();
        $res = $stmt3->get_result();
        while ($row = $res->fetch_assoc()) {
            $listings[] = $row;
        }
        $stmt3->close();

     
        $primary_category = !empty($listings) ? $listings[0]['primary_category'] : '';
    }

    $stmt4 = $conn->prepare(
        "SELECT first_name, last_name FROM users WHERE userid = ? LIMIT 1"
    );
    $stmt4->bind_param("i", $userid);
    $stmt4->execute();
    $stmt4->bind_result($first_name, $last_name);
    $stmt4->fetch();
    $stmt4->close();
    $member_name = trim("$first_name $last_name");
}

$listing_count = count($listings);

function renderStars(int $count): string
{
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
    <style>
        .badge--pending {
            background: #b07d3a;
            color: #fff;
        }
    </style>
</head>

<body>
    <header style="position: relative; z-index: 100;">
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/navbar.php'); ?>
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
                            <input class="field-input" id="editContact" type="text"
                                value="<?= htmlspecialchars($contact_num) ?>"
                                placeholder="Contact number" />
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
                            <?php if (!empty($_SESSION['message'])): ?>
                                <p style="color:#c0392b; font-family:'Convergence',sans-serif; font-size:.85rem; margin-bottom:.5rem;">
                                    <?= htmlspecialchars($_SESSION['message']) ?>
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

                    <p style="font-family:'Convergence',sans-serif; color:#9B7050; font-weight:600; margin:.75rem 0;">
                        Current Listings
                    </p>
                    <div id="existingListings">
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
                    <?php if ($address): ?>
                        &nbsp;·&nbsp;
                        <span id="bannerAddress"><?= htmlspecialchars($address) ?></span>
                    <?php endif; ?>
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
                <span class="metric-card__value"><?= $listing_count ?></span>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {

  
            var editModal = document.getElementById('editModal');
            var saveEditBtn = document.getElementById('saveEdit');
            var bannerName = document.getElementById('bannerName');
            var bannerContact = document.getElementById('bannerContact');
            var bannerAddress = document.getElementById('bannerAddress');
            var editName = document.getElementById('editName');
            var editContact = document.getElementById('editContact');
            var editAddress = document.getElementById('editAddress');

            editModal.addEventListener('show.bs.modal', function() {
                editName.value = bannerName.textContent.trim();
                editContact.value = bannerContact ? bannerContact.textContent.trim() : '';
                editAddress.value = bannerAddress ? bannerAddress.textContent.trim() : '';
            });
            editModal.addEventListener('shown.bs.modal', function() {
                editName.focus();
            });

            saveEditBtn.addEventListener('click', function() {
                var name = editName.value.trim();
                var contact = editContact.value.trim();
                var addr = editAddress.value.trim();
                if (name) bannerName.textContent = name;
                if (contact && bannerContact) bannerContact.textContent = contact;
                if (addr && bannerAddress) bannerAddress.textContent = addr;
                saveEditBtn.textContent = 'Saved!';
                saveEditBtn.style.background = '#2D8C4E';
                setTimeout(function() {
                    saveEditBtn.textContent = 'Save changes';
                    saveEditBtn.style.background = '';
                    bootstrap.Modal.getInstance(editModal).hide();
                }, 900);
            });

            var newPhotoBox = document.getElementById('new_photo_box');
            var newPhotoInput = document.getElementById('new_photo');
            var newPhotoName = document.getElementById('new_photo_name');

            if (newPhotoBox) {
                newPhotoBox.addEventListener('click', function() {
                    newPhotoInput.click();
                });
                newPhotoInput.addEventListener('change', function() {
                    var file = newPhotoInput.files[0];
                    if (file) {
                        if (file.type !== 'image/png') {
                            newPhotoName.style.color = '#c0392b';
                            newPhotoName.textContent = 'Only PNG files are allowed.';
                            newPhotoInput.value = '';
                            return;
                        }
                        if (file.size > 5 * 1024 * 1024) {
                            newPhotoName.style.color = '#c0392b';
                            newPhotoName.textContent = 'File must be under 5MB.';
                            newPhotoInput.value = '';
                            return;
                        }
                        newPhotoName.style.color = '#2D8C4E';
                        newPhotoName.textContent = '✓ ' + file.name;
                    }
                });
            }

            
            document.querySelectorAll('.edit-photo-box').forEach(function(box) {
                var input = box.querySelector('.edit-photo-input');
                var nameDiv = box.querySelector('.edit-photo-name');
                box.addEventListener('click', function() {
                    input.click();
                });
                input.addEventListener('change', function() {
                    var file = input.files[0];
                    if (!file) return;
                    if (file.type !== 'image/png') {
                        nameDiv.style.color = '#c0392b';
                        nameDiv.textContent = 'Only PNG files are allowed.';
                        input.value = '';
                        return;
                    }
                    if (file.size > 5 * 1024 * 1024) {
                        nameDiv.style.color = '#c0392b';
                        nameDiv.textContent = 'File must be under 5MB.';
                        input.value = '';
                        return;
                    }
                    nameDiv.style.color = '#2D8C4E';
                    nameDiv.textContent = '✓ ' + file.name;
                });
            });

            document.querySelectorAll('.save-listing-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var row = btn.closest('.listing-edit-row');
                    var productid = row.dataset.productid;
                    var brand = row.querySelector('.edit-brand').value.trim();
                    var desc = row.querySelector('.edit-desc').value.trim();
                    var cat = row.querySelector('.edit-cat').value;
                    var price = row.querySelector('.edit-price').value.trim();
                    var photoInput = row.querySelector('.edit-photo-input');
                    var msg = row.querySelector('.save-listing-msg');

                    if (!brand || !desc || !price) {
                        msg.style.color = '#c0392b';
                        msg.textContent = 'Brand, description, and price are required.';
                        msg.style.display = 'inline';
                        return;
                    }

                    btn.disabled = true;
                    btn.textContent = 'Saving...';
                    msg.style.display = 'none';

                    var formData = new FormData();
                    formData.append('action', 'update_listing');
                    formData.append('productid', productid);
                    formData.append('primary_category', cat);
                    formData.append('brand_name', brand);
                    formData.append('product_desc', desc);
                    formData.append('price', price);
                    if (photoInput && photoInput.files[0]) {
                        formData.append('product_photo', photoInput.files[0]);
                    }

                    fetch(window.location.href, {
                            method: 'POST',
                            body: formData
                        })
                        .then(function(r) {
                            return r.json();
                        })
                        .then(function(data) {
                            if (data.success) {
                                btn.textContent = 'Saved!';
                                btn.style.background = '#2D8C4E';
                                msg.style.color = '#2D8C4E';
                                msg.textContent = 'Changes saved.';
                                msg.style.display = 'inline';
                                setTimeout(function() {
                                    btn.textContent = 'Save changes';
                                    btn.style.background = '';
                                    btn.disabled = false;
                                }, 1500);
                            } else {
                                msg.style.color = '#c0392b';
                                msg.textContent = data.error || 'Something went wrong.';
                                msg.style.display = 'inline';
                                btn.disabled = false;
                                btn.textContent = 'Save changes';
                            }
                        })
                        .catch(function() {
                            msg.style.color = '#c0392b';
                            msg.textContent = 'Request failed.';
                            msg.style.display = 'inline';
                            btn.disabled = false;
                            btn.textContent = 'Save changes';
                        });
                });
            });

        });
    </script>

    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/footer.php'); ?>
    </footer>
</body>

</html>