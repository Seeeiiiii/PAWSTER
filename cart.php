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
        $stmt = $conn->prepare(
            "INSERT INTO tblorder (userid, sellerid, productid, quantity, total_price)
             VALUES (?, ?, ?, ?, ?)"
        );
        foreach ($_SESSION['cart'] as $productid => $qty) {
            $p = $conn->prepare("SELECT sellerid, price FROM tblsellerproduct WHERE productid = ? LIMIT 1");
            $p->bind_param("i", $productid);
            $p->execute();
            $p->bind_result($sellerid, $price);
            $p->fetch();
            $p->close();

            if (!$sellerid) continue;
            $line_total = round((float)$price * $qty, 2);
            $stmt->bind_param("iiiid", $userid, $sellerid, $productid, $qty, $line_total);
            $stmt->execute();
        }
        $stmt->close();
        $conn->commit();
        $_SESSION['cart'] = [];
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
$shipping   = 120;
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
    $rq = $conn->prepare(
        "SELECT o.quantity, o.total_price,
                p.brand_name, p.price AS unit_price,
                sp.businessname AS seller_name
         FROM tblorder o
         JOIN tblsellerproduct p  ON p.productid = o.productid
         JOIN tblsellerprofile sp ON sp.sellerid  = o.sellerid
         WHERE o.userid = ?
         ORDER BY o.orderid DESC
         LIMIT 50"
    );
    $rq->bind_param("i", $userid);
    $rq->execute();
    $res = $rq->get_result();
    while ($row = $res->fetch_assoc()) {
        $receipt_items[]   = $row;
        $receipt_subtotal += (float)$row['total_price'];
    }
    $rq->close();
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
    <div style="border:1px solid #ccc; border-radius:8px; padding:1.25rem 1.5rem; margin-bottom:1.5rem; font-family:'Convergence',sans-serif; font-size:.9rem; color:#3e2723;">
        <p style="margin:0 0 .75rem; font-weight:700; font-size:1rem;">✓ Order placed successfully!</p>
        <?php if (!empty($receipt_items)): ?>
        <table style="width:100%; border-collapse:collapse; margin-bottom:.75rem;">
            <thead>
                <tr style="border-bottom:1px solid #ccc; color:#666;">
                    <th style="text-align:left; padding:.3rem .4rem; font-weight:600;">Item</th>
                    <th style="text-align:left; padding:.3rem .4rem; font-weight:600;">Seller</th>
                    <th style="text-align:right; padding:.3rem .4rem; font-weight:600;">Qty</th>
                    <th style="text-align:right; padding:.3rem .4rem; font-weight:600;">Price</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($receipt_items as $ri): ?>
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:.3rem .4rem;"><?= htmlspecialchars($ri['brand_name']) ?></td>
                    <td style="padding:.3rem .4rem; color:#666;"><?= htmlspecialchars($ri['seller_name']) ?></td>
                    <td style="padding:.3rem .4rem; text-align:right;"><?= (int)$ri['quantity'] ?></td>
                    <td style="padding:.3rem .4rem; text-align:right;">₱<?= number_format((float)$ri['total_price'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p style="margin:.3rem 0; text-align:right;">Shipping: ₱<?= number_format($receipt_shipping, 2) ?></p>
        <p style="margin:.3rem 0; text-align:right; font-weight:700;">Total: ₱<?= number_format($receipt_subtotal + $receipt_shipping, 2) ?></p>
        <?php endif; ?>
        <p style="margin:.75rem 0 0;"><a href="/PAWSTER/userprof.php?tab=orders" style="color:#9b7050;">View my orders →</a></p>
    </div>
    <?php endif; ?>
    <?php if ($order_error): ?>
        <div style="background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; border-radius:8px; padding:1rem 1.25rem; margin-bottom:1.5rem; font-family:'Convergence',sans-serif;">
            <?= htmlspecialchars($order_error) ?>
        </div>
    <?php endif; ?>

    <div class="checkout-grid">

        <!-- LEFT: CART ITEMS -->
        <section class="card cart-card" aria-label="Cart items">
            <h2 class="card-heading">Cart items (<?= count($cart_items) ?>)</h2>

            <?php if (empty($cart_items)): ?>
                <p style="text-align:center; color:#9B7050; padding:2rem 0; font-family:'Convergence',sans-serif;">
                    Your cart is empty. <a href="/PAWSTER/shop.php" style="color:#9B7050; font-weight:600;">Browse products →</a>
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
                        <a href="/PAWSTER/login.php" class="pay-btn" style="display:block; text-align:center; text-decoration:none;">
                            Log in to checkout
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="/PAWSTER/shop.php" class="pay-btn" style="display:block; text-align:center; text-decoration:none;">
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
</script>

</body>
</html>