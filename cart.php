<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/config/app.php';

$db   = new DatabaseConnection();
$conn = $db->conn;

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = []; 

$userid = (int)($_SESSION['auth_user']['userid'] ?? 0);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_to_cart') {
    header('Content-Type: application/json');
    $productid = (int)($_POST['productid'] ?? 0);
    if ($productid > 0) {
        $_SESSION['cart'][$productid] = ($_SESSION['cart'][$productid] ?? 0) + 1;
        echo json_encode(['success' => true, 'cart_count' => array_sum($_SESSION['cart'])]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'cart_count') {
    header('Content-Type: application/json');
    echo json_encode(['count' => array_sum($_SESSION['cart'])]);
    exit();
}


// ------------------------------------------------------------------
// Delivery Address API
// ------------------------------------------------------------------

// GET: list all saved delivery addresses for the logged-in user
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'list_addresses') {
    header('Content-Type: application/json');
    if (!$userid) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit();
    }
    $addresses = [];
    $stmt = $conn->prepare(
        "SELECT addressid, secondary_address, region, province, city, barangay, created_at
         FROM tbldelivery_address
         WHERE accountid = ?
         ORDER BY addressid DESC"
    );
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $addresses[] = $row;
    }
    $stmt->close();

    echo json_encode([
        'success'   => true,
        'addresses' => $addresses,
        'selected'  => $_SESSION['selected_address'] ?? null,
    ]);
    exit();
}

// POST: add a new delivery address for the logged-in user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_address') {
    header('Content-Type: application/json');
    if (!$userid) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit();
    }

    $secondary_address = trim($_POST['secondary_address'] ?? '');
    $region            = trim($_POST['region'] ?? '');
    $province          = trim($_POST['province'] ?? '');
    $city              = trim($_POST['city'] ?? '');
    $barangay          = trim($_POST['barangay'] ?? '');

    if ($secondary_address === '' || $region === '' || $city === '' || $barangay === '') {
        echo json_encode(['success' => false, 'message' => 'House/unit/street, region, city, and barangay are required.']);
        exit();
    }

    $stmt = $conn->prepare(
        "INSERT INTO tbldelivery_address (accountid, secondary_address, region, province, city, barangay)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("isssss", $userid, $secondary_address, $region, $province, $city, $barangay);
    $stmt->execute();
    $newid = $stmt->insert_id;
    $stmt->close();

    // Automatically select the newly added address for this checkout
    $_SESSION['selected_address'] = $newid;

    echo json_encode([
        'success' => true,
        'address' => [
            'addressid'         => $newid,
            'secondary_address' => $secondary_address,
            'region'            => $region,
            'province'          => $province,
            'city'              => $city,
            'barangay'          => $barangay,
        ],
    ]);
    exit();
}

// POST: select an existing delivery address to use for checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'select_address') {
    header('Content-Type: application/json');
    if (!$userid) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit();
    }

    $addressid = (int)($_POST['addressid'] ?? 0);

    // Verify the address belongs to this user
    $stmt = $conn->prepare("SELECT addressid FROM tbldelivery_address WHERE addressid = ? AND accountid = ? LIMIT 1");
    $stmt->bind_param("ii", $addressid, $userid);
    $stmt->execute();
    $stmt->store_result();
    $found = $stmt->num_rows > 0;
    $stmt->close();

    if (!$found) {
        echo json_encode(['success' => false, 'message' => 'Address not found.']);
        exit();
    }

    $_SESSION['selected_address'] = $addressid;
    echo json_encode(['success' => true, 'selected' => $addressid]);
    exit();
}

// POST: delete a saved delivery address
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_address') {
    header('Content-Type: application/json');
    if (!$userid) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit();
    }

    $addressid = (int)($_POST['addressid'] ?? 0);

    $stmt = $conn->prepare("DELETE FROM tbldelivery_address WHERE addressid = ? AND accountid = ?");
    $stmt->bind_param("ii", $addressid, $userid);
    $stmt->execute();
    $deleted = $stmt->affected_rows > 0;
    $stmt->close();

    if ($deleted && (int)($_SESSION['selected_address'] ?? 0) === $addressid) {
        unset($_SESSION['selected_address']);
    }

    echo json_encode(['success' => $deleted]);
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'remove') {
    $pid = (int)($_POST['productid'] ?? 0);
    unset($_SESSION['cart'][$pid]);
    header('Location: cart.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'qty') {
    $pid  = (int)($_POST['productid'] ?? 0);
    $qty  = (int)($_POST['qty'] ?? 1);
    if ($pid > 0) {
        if ($qty <= 0) {
            unset($_SESSION['cart'][$pid]);
        } else {
            $_SESSION['cart'][$pid] = $qty;
        }
    }
    header('Location: cart.php');
    exit();
}


$shipping = 120; // delivery fee — defined here so checkout block can use it

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'checkout') {
    if (!$userid) {
        header('Location: /PAWSTER/login.php');
        exit();
    }
    if (empty($_SESSION['cart'])) {
        header('Location: cart.php');
        exit();
    }


    $card_errors = [];

    if (empty($_SESSION['selected_address'])) {
        $card_errors[] = 'Please select or add a delivery address.';
    }

    $card_number = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
    $card_expiry = trim($_POST['card_expiry'] ?? '');
    $card_cvv    = trim($_POST['card_cvv']    ?? '');
    $card_name   = trim($_POST['card_name']   ?? '');

    if (!preg_match('/^\d{13,19}$/', $card_number)) {
        $card_errors[] = 'Enter a valid card number (13–19 digits).';
    }

    if (preg_match('/^(\d{2})\s*\/\s*(\d{2})$/', $card_expiry, $m)) {
        $exp_month = (int)$m[1];
        $exp_year  = (int)('20' . $m[2]);
        $now_month = (int)date('n');
        $now_year  = (int)date('Y');
        if ($exp_month < 1 || $exp_month > 12) {
            $card_errors[] = 'Invalid expiry month.';
        } elseif ($exp_year < $now_year || ($exp_year === $now_year && $exp_month < $now_month)) {
            $card_errors[] = 'Your card has expired.';
        }
    } else {
        $card_errors[] = 'Enter expiry in MM / YY format.';
    }

    if (!preg_match('/^\d{3,4}$/', $card_cvv)) {
        $card_errors[] = 'CVV must be 3 or 4 digits.';
    }

    if (strlen($card_name) < 2) {
        $card_errors[] = 'Enter the name on your card.';
    }

    if (!empty($card_errors)) {
        $_SESSION['order_error'] = implode(' ', $card_errors);
        header('Location: cart.php');
        exit();
    }


    $conn->begin_transaction();
    try {
        $addressid = (int)$_SESSION['selected_address'];

        // 1. Calculate grand total and cache prices
        $grand_total = 0;
        $cart_prices  = [];
        $cart_sellers = [];
        foreach ($_SESSION['cart'] as $productid => $qty) {
            $p = $conn->prepare("SELECT sellerid, price FROM tblsellerproduct WHERE productid = ? LIMIT 1");
            $p->bind_param("i", $productid);
            $p->execute();
            $p->bind_result($sellerid, $price);
            $p->fetch();
            $p->close();
            $cart_prices[$productid]  = (float)$price;
            $cart_sellers[$productid] = (int)$sellerid;
            $grand_total += round((float)$price * $qty, 2);
        }
        $grand_total += $shipping;

        // 2. Insert ONE order header row
        $order_stmt = $conn->prepare(
            "INSERT INTO tblorder (userid, addressid, order_status, total_price)
             VALUES (?, ?, 'Pending', ?)"
        );
        $order_stmt->bind_param("iid", $userid, $addressid, $grand_total);
        $order_stmt->execute();
        $new_orderid = $order_stmt->insert_id;
        $order_stmt->close();

        // 3. Insert one row per product into tblorder_items
        $item_stmt = $conn->prepare(
            "INSERT INTO tblorder_items (orderid, productid, sellerid, quantity, price)
             VALUES (?, ?, ?, ?, ?)"
        );
        foreach ($_SESSION['cart'] as $productid => $qty) {
            $unit_price = $cart_prices[$productid];
            $sel_id     = $cart_sellers[$productid];
            $item_stmt->bind_param("iiiid", $new_orderid, $productid, $sel_id, $qty, $unit_price);
            $item_stmt->execute();
        }
        $item_stmt->close();

        $conn->commit();
        $_SESSION['cart']          = [];
        $_SESSION['last_orderid']  = $new_orderid;
        $_SESSION['order_success'] = true;
        header('Location: cart.php');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['order_error'] = 'Order failed: ' . $e->getMessage();
        header('Location: cart.php');
        exit();
    }
}

$cart_items = [];
$subtotal   = 0;

if (!empty($_SESSION['cart'])) {
    $ids        = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
    $result     = $conn->query(
        "SELECT p.productid, p.brand_name, p.product_desc, p.price, p.productimage, p.sellerid
         FROM tblsellerproduct p
         WHERE p.productid IN ($ids)"
    );
    while ($row = $result->fetch_assoc()) {
        $qty               = $_SESSION['cart'][$row['productid']] ?? 1;
        $row['qty']        = $qty;
        $row['line_total'] = (float)$row['price'] * $qty;
        $subtotal         += $row['line_total'];
        $cart_items[]      = $row;
    }
}

$total     = $subtotal + (empty($cart_items) ? 0 : $shipping);
$pawpoints = floor($total / 100);

$order_success = !empty($_SESSION['order_success']);
$order_error   = $_SESSION['order_error'] ?? '';
unset($_SESSION['order_success'], $_SESSION['order_error']);

$receipt_items    = [];
$receipt_subtotal = 0;
$receipt_shipping = 120;

if ($order_success && $userid) {
    $last_orderid = (int)($_SESSION['last_orderid'] ?? 0);
    unset($_SESSION['last_orderid']);

    if ($last_orderid) {
        $rq = $conn->prepare(
            "SELECT oi.quantity,
                    oi.price,
                    (oi.quantity * oi.price) AS total_price,
                    p.brand_name,
                    sp.businessname AS seller_name
             FROM tblorder_items oi
             JOIN tblsellerproduct p  ON p.productid = oi.productid
             JOIN tblsellerprofile sp ON sp.sellerid  = oi.sellerid
             WHERE oi.orderid = ?
             ORDER BY oi.order_item_id ASC"
        );
        $rq->bind_param("i", $last_orderid);
        $rq->execute();
        $res = $rq->get_result();
        while ($row = $res->fetch_assoc()) {
            $receipt_items[]   = $row;
            $receipt_subtotal += (float)$row['total_price'];
        }
        $rq->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Your Cart — Pawster</title>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/headlinks.php'); ?>
    <link rel="stylesheet" href="resources/css/cart.css" />
</head>
<body>

<header>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/navbar.php'); ?>
</header>

<main class="page-wrapper">

    <a href="/PAWSTER/shop.php" class="back-link">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        Back to shop
    </a>

    <h1 class="page-title">Your Cart</h1>
    <p class="page-subtitle">Review your items and complete your purchase</p>

    <?php if ($order_success): ?>
    <div class="order-success">
        <p class="order-success-title">✓ Order placed successfully!</p>
        <?php if (!empty($receipt_items)): ?>
        <table class="receipt-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Seller</th>
                    <th>Qty</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($receipt_items as $ri): ?>
                <tr>
                    <td><?= htmlspecialchars($ri['brand_name']) ?></td>
                    <td><?= htmlspecialchars($ri['seller_name']) ?></td>
                    <td><?= (int)$ri['quantity'] ?></td>
                    <td>₱<?= number_format((float)$ri['total_price'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p class="receipt-summary-row">Shipping: ₱<?= number_format($receipt_shipping, 2) ?></p>
        <p class="receipt-summary-row total">Total: ₱<?= number_format($receipt_subtotal + $receipt_shipping, 2) ?></p>
        <?php endif; ?>
        <a href="/PAWSTER/userprof.php?tab=orders" class="order-success-link">View my orders →</a>
    </div>
    <?php endif; ?>
    <?php if ($order_error): ?>
        <div class="order-error">
            <?= htmlspecialchars($order_error) ?>
        </div>
    <?php endif; ?>

    <div class="checkout-grid">

        <div class="checkout-left">

        <!-- LEFT: CART ITEMS -->
        <section class="card cart-card" aria-label="Cart items">
            <h2 class="card-heading">Cart items (<?= count($cart_items) ?>)</h2>

            <?php if (empty($cart_items)): ?>
                <p class="empty-state">
                    Your cart is empty. <a href="/PAWSTER/shop.php">Browse products →</a>
                </p>
            <?php else: ?>
            <ul class="cart-list">
                <?php foreach ($cart_items as $index => $item): ?>
                <li class="cart-item">
                    <div class="item-thumb" aria-label="<?= htmlspecialchars($item['brand_name']) ?>">
                        <?php if (!empty($item['productimage'])): ?>
                            <img src="/PAWSTER/resources/images/<?= htmlspecialchars($item['productimage']) ?>"
                                 alt="<?= htmlspecialchars($item['brand_name']) ?>"
                                 style="width:100%; height:100%; object-fit:cover; border-radius:inherit;">
                        <?php else: ?>
                            <svg width="36" height="36" viewBox="0 0 64 64" fill="none" aria-hidden="true">
                                <ellipse cx="32" cy="38" rx="14" ry="12" fill="#F5EFE6" opacity=".9"/>
                                <ellipse cx="17" cy="28" rx="6" ry="8" fill="#F5EFE6" opacity=".9"/>
                                <ellipse cx="47" cy="28" rx="6" ry="8" fill="#F5EFE6" opacity=".9"/>
                            </svg>
                        <?php endif; ?>
                    </div>

                    <div class="item-info">
                        <p class="item-name"><?= htmlspecialchars($item['brand_name']) ?></p>
                        <p class="item-base-price">₱<?= number_format((float)$item['price'], 2) ?></p>

                        <form method="post" action="cart.php" class="qty-form">
                            <input type="hidden" name="action" value="qty" />
                            <input type="hidden" name="productid" value="<?= (int)$item['productid'] ?>" />
                            <div class="qty-control">
                                <button type="submit" name="qty" value="<?= $item['qty'] - 1 ?>" class="qty-btn" aria-label="Decrease quantity">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                </button>
                                <span class="qty-value"><?= $item['qty'] ?></span>
                                <button type="submit" name="qty" value="<?= $item['qty'] + 1 ?>" class="qty-btn" aria-label="Increase quantity">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="item-right">
                        <form method="post" action="cart.php">
                            <input type="hidden" name="action" value="remove" />
                            <input type="hidden" name="productid" value="<?= (int)$item['productid'] ?>" />
                            <button type="submit" class="remove-btn" aria-label="Remove item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </form>
                        <p class="item-line-price">₱<?= number_format($item['line_total'], 2) ?></p>
                    </div>
                </li>
                <?php if ($index < count($cart_items) - 1): ?>
                <li class="cart-divider" role="separator"></li>
                <?php endif; ?>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </section>

        <section class="card address-card" aria-label="Delivery address">
            <h2 class="card-heading">Delivery address</h2>
            <p class="card-subheading">Choose where you'd like your order delivered</p>

            <?php if (!$userid): ?>
                <p class="address-login-notice">
                    <a href="/PAWSTER/login.php">Log in</a> to manage your delivery addresses.
                </p>
            <?php else: ?>
                <div id="address-list" class="address-list">
                    <p class="address-loading">Loading saved addresses…</p>
                </div>

                <button type="button" id="toggle-address-form" class="pay-btn" style="margin-top:12px; width:100%; display:flex; align-items:center; justify-content:center; gap:6px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add new address
                </button>

                <form id="address-form" class="address-form" style="display:none;">
                    <div class="field-group">
                        <label class="field-label" for="secondary_address">House / Unit / Street No.</label>
                        <input type="text" class="form-input" id="secondary_address" name="secondary_address"
                               placeholder="e.g. 123 Sampaguita St., Blk 4 Lot 2" maxlength="100" />
                    </div>
                    <div class="field-group">
                        <label class="field-label" for="region">Region</label>
                        <select class="form-input" id="region" name="region">
                            <option value="">-- Select Region --</option>
                        </select>
                    </div>
                    <div class="field-group">
                        <label class="field-label" for="province">Province</label>
                        <select class="form-input" id="province" name="province" disabled>
                            <option value="">-- Select Province --</option>
                        </select>
                    </div>
                    <div class="field-group">
                        <label class="field-label" for="city">City / Municipality</label>
                        <select class="form-input" id="city" name="city" disabled>
                            <option value="">-- Select City/Municipality --</option>
                        </select>
                    </div>
                    <div class="field-group">
                        <label class="field-label" for="barangay">Barangay</label>
                        <select class="form-input" id="barangay" name="barangay" disabled>
                            <option value="">-- Select Barangay --</option>
                        </select>
                    </div>
                    <p id="address-form-error" class="address-error" style="display:none;"></p>
                    <div class="address-form-actions" style="display:flex; gap:10px; margin-top:14px;">
                        <button type="submit" class="pay-btn" style="flex:1; display:flex; align-items:center; justify-content:center; gap:6px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Save address
                        </button>
                        <button type="button" id="cancel-address-form" class="pay-btn" style="flex:1; display:flex; align-items:center; justify-content:center; gap:6px; opacity:0.6;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Cancel
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </section>

        </div>

        <section class="card payment-card" aria-label="Payment details">
            <h2 class="card-heading">Payment</h2>
            <p class="card-subheading">Secure payment with your card</p>


            <div class="accepted-cards">
                <span class="card-badge visa">VISA</span>
                <span class="card-badge mastercard">MC</span>
                <span class="card-badge amex">AMEX</span>
                <span class="card-badge jcb">JCB</span>
            </div>

            <p class="field-group-label">Card details</p>

            <div class="payment-form">
                <div class="field-group">
                    <div class="input-icon-wrap">
                        <input type="text" class="form-input" id="card_number" name="card_number"
                               placeholder="Card number" maxlength="19" inputmode="numeric"
                               autocomplete="cc-number" form="checkout-form" />
                        <span class="input-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        </span>
                    </div>
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <input type="text" class="form-input" id="card_expiry" name="card_expiry"
                               placeholder="MM / YY" maxlength="7"
                               autocomplete="cc-exp" form="checkout-form" />
                    </div>
                    <div class="field-group">
                        <div class="input-icon-wrap">
                            <input type="password" class="form-input" id="card_cvv" name="card_cvv"
                                   placeholder="CVV" maxlength="4" inputmode="numeric"
                                   autocomplete="cc-csc" form="checkout-form" />
                            <span class="input-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="field-group">
                    <input type="text" class="form-input" id="card_name" name="card_name"
                           placeholder="Name on card"
                           autocomplete="cc-name" form="checkout-form" />
                </div>
                <p class="security-notice">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Your payment is secure and encrypted
                </p>
            </div>
        </section>

    </div>


    <section class="card summary-card" aria-label="Order summary">
        <h2 class="card-heading summary-heading">Order summary</h2>
        <div class="summary-body">
            <div class="summary-lines">
                <div class="summary-row">
                    <span>Subtotal (<?= count($cart_items) ?> items)</span>
                    <span>₱<?= number_format($subtotal, 2) ?></span>
                </div>
                <?php if (!empty($cart_items)): ?>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>₱<?= number_format($shipping, 2) ?></span>
                </div>
                <?php endif; ?>
                <div class="summary-row summary-total">
                    <span>Total</span>
                    <span>₱<?= number_format($total, 2) ?></span>
                </div>
            </div>
            <div class="summary-cta">
                <?php if (!empty($cart_items)): ?>
                    <p class="pawpoints-notice">🐾 Earn <?= $pawpoints ?> PawPoints on this order!</p>
                    <?php if ($userid): ?>
                        <form id="checkout-form" method="post" action="cart.php">
                            <input type="hidden" name="action" value="checkout" />
                            <button type="submit" class="pay-btn">
                                 Pay ₱<?= number_format($total, 2) ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="/PAWSTER/login.php" class="pay-btn pay-btn-link">
                            Log in to checkout
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="/PAWSTER/shop.php" class="pay-btn pay-btn-link">
                        Start Shopping
                    </a>
                <?php endif; ?>
                <p class="ssl-notice">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    SSL Secured Checkout
                </p>
            </div>
        </div>
    </section>

</main>

<footer class="site-footer">
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/footer.php'); ?>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
const cardNumInput = document.getElementById('card_number');
if (cardNumInput) {
    cardNumInput.addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').substring(0, 19);
        this.value = v.replace(/(.{4})/g, '$1 ').trim();
    });
}

const expiryInput = document.getElementById('card_expiry');
if (expiryInput) {
    expiryInput.addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').substring(0, 4);
        if (v.length >= 3) {
            this.value = v.substring(0, 2) + ' / ' + v.substring(2);
        } else {
            this.value = v;
        }
    });
}

// ------------------------------------------------------------------
// PH Region / Province / City / Barangay cascading selects
// Data source: isaacdarcilla/philippine-addresses (via jsDelivr)
// ------------------------------------------------------------------
const PH_DATA_BASE = 'https://cdn.jsdelivr.net/gh/isaacdarcilla/philippine-addresses@main/';
const phDataCache  = {};

function loadPhData(file) {
    if (phDataCache[file]) return phDataCache[file];
    phDataCache[file] = fetch(PH_DATA_BASE + file).then(res => res.json());
    return phDataCache[file];
}

const regionSelect   = document.getElementById('region');
const provinceSelect = document.getElementById('province');
const citySelect     = document.getElementById('city');
const barangaySelect = document.getElementById('barangay');

function resetSelect(select, placeholder, disable) {
    if (!select) return;
    select.innerHTML = `<option value="">${placeholder}</option>`;
    select.disabled = !!disable;
}

if (regionSelect) {
    loadPhData('region.json').then(regions => {
        regions
            .slice()
            .sort((a, b) => a.region_name.localeCompare(b.region_name))
            .forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.region_code;
                opt.textContent = r.region_name;
                regionSelect.appendChild(opt);
            });
    }).catch(() => {});

    regionSelect.addEventListener('change', function () {
        resetSelect(provinceSelect, '-- Select Province --', true);
        resetSelect(citySelect, '-- Select City/Municipality --', true);
        resetSelect(barangaySelect, '-- Select Barangay --', true);

        const regionCode = this.value;
        if (!regionCode) return;

        loadPhData('province.json').then(provinces => {
            const filtered = provinces
                .filter(p => p.region_code === regionCode)
                .sort((a, b) => a.province_name.localeCompare(b.province_name));

            if (filtered.length === 0) {
                // Some regions (e.g. NCR) have no provinces — skip straight to cities
                resetSelect(provinceSelect, '-- N/A --', true);
                loadCitiesForRegion(regionCode);
                return;
            }

            filtered.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.province_code;
                opt.textContent = p.province_name;
                provinceSelect.appendChild(opt);
            });
            provinceSelect.disabled = false;
        }).catch(() => {});
    });
}

function loadCitiesForRegion(regionCode) {
    loadPhData('city.json').then(cities => {
        const filtered = cities
            .filter(c => c.region_desc === regionCode || c.region_code === regionCode)
            .sort((a, b) => a.city_name.localeCompare(b.city_name));

        filtered.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.city_code;
            opt.textContent = c.city_name;
            citySelect.appendChild(opt);
        });
        citySelect.disabled = false;
    }).catch(() => {});
}

if (provinceSelect) {
    provinceSelect.addEventListener('change', function () {
        resetSelect(citySelect, '-- Select City/Municipality --', true);
        resetSelect(barangaySelect, '-- Select Barangay --', true);

        const provinceCode = this.value;
        if (!provinceCode) return;

        loadPhData('city.json').then(cities => {
            const filtered = cities
                .filter(c => c.province_code === provinceCode)
                .sort((a, b) => a.city_name.localeCompare(b.city_name));

            filtered.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.city_code;
                opt.textContent = c.city_name;
                citySelect.appendChild(opt);
            });
            citySelect.disabled = false;
        }).catch(() => {});
    });
}

if (citySelect) {
    citySelect.addEventListener('change', function () {
        resetSelect(barangaySelect, '-- Select Barangay --', true);

        const cityCode = this.value;
        if (!cityCode) return;

        loadPhData('barangay.json').then(barangays => {
            const filtered = barangays
                .filter(b => b.city_code === cityCode)
                .sort((a, b) => a.brgy_name.localeCompare(b.brgy_name));

            filtered.forEach(b => {
                const opt = document.createElement('option');
                opt.value = b.brgy_code;
                opt.textContent = b.brgy_name;
                barangaySelect.appendChild(opt);
            });
            barangaySelect.disabled = false;
        }).catch(() => {});
    });
}

function resetAddressSelects() {
    resetSelect(provinceSelect, '-- Select Province --', true);
    resetSelect(citySelect, '-- Select City/Municipality --', true);
    resetSelect(barangaySelect, '-- Select Barangay --', true);
}

// ------------------------------------------------------------------
// Delivery Address API (cart.php)
// ------------------------------------------------------------------
const addressList   = document.getElementById('address-list');
const addressForm   = document.getElementById('address-form');
const toggleBtn     = document.getElementById('toggle-address-form');
const cancelBtn     = document.getElementById('cancel-address-form');
const addressErrEl  = document.getElementById('address-form-error');

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}

function renderAddresses(data) {
    if (!addressList) return;

    if (!data.addresses || data.addresses.length === 0) {
        addressList.innerHTML = '<p class="address-empty">No saved addresses yet. Add one below.</p>';
        return;
    }

    addressList.innerHTML = data.addresses.map(addr => {
        const checked = (String(data.selected) === String(addr.addressid)) ? 'checked' : '';
        return `
            <label class="address-option">
                <input type="radio" name="selected_address" value="${addr.addressid}" ${checked} />
                <span class="address-text">
                    <strong>${escapeHtml(addr.secondary_address)}</strong><br>
                    Brgy. ${escapeHtml(addr.barangay)}, ${escapeHtml(addr.city)}, ${escapeHtml(addr.province)}, ${escapeHtml(addr.region)}
                </span>
                <button type="button" class="delete-address-btn" data-id="${addr.addressid}" aria-label="Delete address">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </label>`;
    }).join('');

    addressList.querySelectorAll('input[name="selected_address"]').forEach(input => {
        input.addEventListener('change', function () {
            fetch('cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=select_address&addressid=' + encodeURIComponent(this.value)
            })
            .then(res => res.json())
            .then(resp => {
                if (!resp.success) {
                    alert(resp.message || 'Could not select address.');
                }
            });
        });
    });

    addressList.querySelectorAll('.delete-address-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            if (!confirm('Remove this address?')) return;
            fetch('cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=delete_address&addressid=' + encodeURIComponent(id)
            })
            .then(res => res.json())
            .then(resp => {
                if (resp.success) {
                    loadAddresses();
                } else {
                    alert('Could not delete address.');
                }
            });
        });
    });
}

function loadAddresses() {
    if (!addressList) return;
    fetch('cart.php?action=list_addresses')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderAddresses(data);
            } else {
                addressList.innerHTML = '<p class="address-empty">' + escapeHtml(data.message || 'Unable to load addresses.') + '</p>';
            }
        })
        .catch(() => {
            addressList.innerHTML = '<p class="address-empty">Unable to load addresses.</p>';
        });
}

if (toggleBtn && addressForm) {
    toggleBtn.addEventListener('click', function () {
        addressForm.style.display = (addressForm.style.display === 'none') ? 'block' : 'none';
        if (addressErrEl) addressErrEl.style.display = 'none';
    });
}

if (cancelBtn && addressForm) {
    cancelBtn.addEventListener('click', function () {
        addressForm.reset();
        resetAddressSelects();
        addressForm.style.display = 'none';
        if (addressErrEl) addressErrEl.style.display = 'none';
    });
}

function selectedText(select) {
    if (!select || select.selectedIndex < 0) return '';
    return select.options[select.selectedIndex].text.trim();
}

if (addressForm) {
    addressForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const secondaryAddress = document.getElementById('secondary_address').value.trim();
        const region   = selectedText(regionSelect);
        const province = (provinceSelect.value === '' && provinceSelect.disabled) ? '' : selectedText(provinceSelect);
        const city     = selectedText(citySelect);
        const barangay = selectedText(barangaySelect);

        if (!secondaryAddress || !regionSelect.value || !citySelect.value || !barangaySelect.value) {
            if (addressErrEl) {
                addressErrEl.textContent = 'Please complete the address fields (Region, City/Municipality, and Barangay are required).';
                addressErrEl.style.display = 'block';
            }
            return;
        }

        const body = new URLSearchParams({
            action: 'add_address',
            secondary_address: secondaryAddress,
            region: region,
            province: province || 'N/A',
            city: city,
            barangay: barangay
        });

        fetch('cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.success) {
                addressForm.reset();
                resetAddressSelects();
                addressForm.style.display = 'none';
                if (addressErrEl) addressErrEl.style.display = 'none';
                loadAddresses();
            } else if (addressErrEl) {
                addressErrEl.textContent = resp.message || 'Could not save address.';
                addressErrEl.style.display = 'block';
            }
        });
    });
}

loadAddresses();

</script>

</body>
</html>