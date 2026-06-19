<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/config/app.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/controllers/cart_control.php';

/**
 * $cart_items
 * @var int $order_error
 * @var array $order_success
 * @var array $cart_items
 * @var array $userid
 * @var int $total
 * @var int $pawpoints
 */
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
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/navbar.php'); 
        include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/controllers/navbar_mode_handler.php';?>
    </header>

    <main class="page-wrapper">

        <a href="/PAWSTER/shop.php" class="back-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6" />
            </svg>
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
                                                <ellipse cx="32" cy="38" rx="14" ry="12" fill="#F5EFE6" opacity=".9" />
                                                <ellipse cx="17" cy="28" rx="6" ry="8" fill="#F5EFE6" opacity=".9" />
                                                <ellipse cx="47" cy="28" rx="6" ry="8" fill="#F5EFE6" opacity=".9" />
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
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">
                                                        <line x1="5" y1="12" x2="19" y2="12" />
                                                    </svg>
                                                </button>
                                                <span class="qty-value"><?= $item['qty'] ?></span>
                                                <button type="submit" name="qty" value="<?= $item['qty'] + 1 ?>" class="qty-btn" aria-label="Increase quantity">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">
                                                        <line x1="12" y1="5" x2="12" y2="19" />
                                                        <line x1="5" y1="12" x2="19" y2="12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="item-right">
                                        <form method="post" action="cart.php">
                                            <input type="hidden" name="action" value="remove" />
                                            <input type="hidden" name="productid" value="<?= (int)$item['productid'] ?>" />
                                            <button type="submit" class="remove-btn" aria-label="Remove item">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                                    <line x1="18" y1="6" x2="6" y2="18" />
                                                    <line x1="6" y1="6" x2="18" y2="18" />
                                                </svg>
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
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19" />
                                <line x1="5" y1="12" x2="19" y2="12" />
                            </svg>
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
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                    Save address
                                </button>
                                <button type="button" id="cancel-address-form" class="pay-btn" style="flex:1; display:flex; align-items:center; justify-content:center; gap:6px; opacity:0.6;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                        <line x1="18" y1="6" x2="6" y2="18" />
                                        <line x1="6" y1="6" x2="18" y2="18" />
                                    </svg>
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
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="5" width="20" height="14" rx="2" />
                                    <line x1="2" y1="10" x2="22" y2="10" />
                                </svg>
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
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10" />
                                        <line x1="12" y1="8" x2="12" y2="12" />
                                        <line x1="12" y1="16" x2="12.01" y2="16" />
                                    </svg>
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
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        </svg>
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
                        <p class="shipping-notice">
                                ⚠️ A shipping fee of <strong>₱60.00</strong> is charged for every order.
                            </p>
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
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="11" width="18" height="11" rx="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
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
    <script src="resources/js/cart.js"></script>
</body>

</html>