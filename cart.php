<?php
// cart.php — Pawster Cart & Checkout Page

$cart_items = [
    [
        'id'       => 1,
        'name'     => 'Premium Dog Chew Treats (500g)',
        'price'    => 500,
        'qty'      => 1,
        'img_alt'  => 'Dog Chew Treats',
    ],
    [
        'id'       => 2,
        'name'     => 'Adjustable Nylon Dog Collar',
        'price'    => 550,
        'qty'      => 1,
        'img_alt'  => 'Dog Collar',
    ],
    [
        'id'       => 3,
        'name'     => 'Orthopedic Dog Bed – Medium',
        'price'    => 1000,
        'qty'      => 1,
        'img_alt'  => 'Dog Bed',
    ],
];

$subtotal  = array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $cart_items));
$shipping  = 120;
$total     = $subtotal + $shipping;
$pawpoints = floor($total / 100);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Your Cart — Pawster</title>
    <link rel="stylesheet" href="resources/css/cart.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
</head>
<body>

<!-- ═══════════════════════════════════════════
     HEADER
════════════════════════════════════════════ -->
<header class="site-header">
    <div class="header-inner">
        <!-- Logo -->
        <a href="#" class="logo">
            <span class="logo-icon" aria-hidden="true">
                <!-- Paw SVG -->
                <svg width="32" height="32" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="32" cy="38" rx="16" ry="14" fill="#F5EFE6"/>
                    <ellipse cx="15" cy="26" rx="7" ry="9" fill="#F5EFE6"/>
                    <ellipse cx="49" cy="26" rx="7" ry="9" fill="#F5EFE6"/>
                    <ellipse cx="22" cy="18" rx="5" ry="7" fill="#F5EFE6"/>
                    <ellipse cx="42" cy="18" rx="5" ry="7" fill="#F5EFE6"/>
                </svg>
            </span>
            <span class="logo-text">PAWSTER</span>
        </a>

        <!-- User Dropdown (CSS-only) -->
        <div class="user-menu">
            <button class="user-toggle" aria-haspopup="true" aria-expanded="false">
                <span class="user-icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                    </svg>
                </span>
                <span>User</span>
                <span class="chevron" aria-hidden="true">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </span>
            </button>
            <ul class="dropdown-menu" role="menu">
                <li><a href="#" role="menuitem">My Profile</a></li>
                <li><a href="#" role="menuitem">My Orders</a></li>
                <li><a href="#" role="menuitem">PawPoints</a></li>
                <li class="divider"></li>
                <li><a href="#" role="menuitem">Sign Out</a></li>
            </ul>
        </div>
    </div>
</header>

<!-- ═══════════════════════════════════════════
     MAIN CONTENT
════════════════════════════════════════════ -->
<main class="page-wrapper">

    <a href="#" class="back-link">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"/>
        </svg>
        Back to shop
    </a>

    <h1 class="page-title">Your Cart</h1>
    <p class="page-subtitle">Review your items and complete your purchase</p>

    <div class="checkout-grid">

        <!-- ══════════════════════════════
             LEFT: CART ITEMS
        ══════════════════════════════ -->
        <section class="card cart-card" aria-label="Cart items">
            <h2 class="card-heading">Cart items (<?= count($cart_items) ?>)</h2>

            <ul class="cart-list">
                <?php foreach ($cart_items as $index => $item): ?>
                <li class="cart-item">
                    <!-- Product image placeholder -->
                    <div class="item-thumb" aria-label="<?= htmlspecialchars($item['img_alt']) ?>">
                        <svg width="36" height="36" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <ellipse cx="32" cy="38" rx="14" ry="12" fill="#F5EFE6" opacity=".9"/>
                            <ellipse cx="17" cy="28" rx="6" ry="8" fill="#F5EFE6" opacity=".9"/>
                            <ellipse cx="47" cy="28" rx="6" ry="8" fill="#F5EFE6" opacity=".9"/>
                            <ellipse cx="23" cy="20" rx="5" ry="6" fill="#F5EFE6" opacity=".9"/>
                            <ellipse cx="41" cy="20" rx="5" ry="6" fill="#F5EFE6" opacity=".9"/>
                        </svg>
                    </div>

                    <!-- Product info -->
                    <div class="item-info">
                        <p class="item-name"><?= htmlspecialchars($item['name']) ?></p>
                        <p class="item-base-price">₱<?= number_format($item['price']) ?></p>

                        <!-- Quantity selector (no JS — uses form submit) -->
                        <form method="post" action="cart.php" class="qty-form">
                            <input type="hidden" name="item_id" value="<?= $item['id'] ?>" />
                            <div class="qty-control">
                                <button type="submit" name="action" value="decrease" class="qty-btn" aria-label="Decrease quantity">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                </button>
                                <span class="qty-value"><?= $item['qty'] ?></span>
                                <button type="submit" name="action" value="increase" class="qty-btn" aria-label="Increase quantity">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Line price + remove -->
                    <div class="item-right">
                        <form method="post" action="cart.php">
                            <input type="hidden" name="item_id" value="<?= $item['id'] ?>" />
                            <button type="submit" name="action" value="remove" class="remove-btn" aria-label="Remove item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </form>
                        <p class="item-line-price">₱<?= number_format($item['price'] * $item['qty']) ?></p>
                    </div>
                </li>
                <?php if ($index < count($cart_items) - 1): ?>
                <li class="cart-divider" role="separator"></li>
                <?php endif; ?>
                <?php endforeach; ?>
            </ul>

            <!-- Promo Code -->
            <form method="post" action="cart.php" class="promo-form">
                <label for="promo" class="sr-only">Promo code</label>
                <input
                    type="text"
                    id="promo"
                    name="promo"
                    class="promo-input"
                    placeholder="Promo code"
                    autocomplete="off"
                />
                <button type="submit" name="action" value="apply_promo" class="promo-btn">Apply</button>
            </form>
        </section>

        <!-- ══════════════════════════════
             RIGHT: PAYMENT
        ══════════════════════════════ -->
        <section class="card payment-card" aria-label="Payment details">
            <h2 class="card-heading">Payment</h2>
            <p class="card-subheading">Secure payment with your card</p>

            <!-- Accepted cards -->
            <div class="accepted-cards">
                <span class="card-badge visa" title="Visa">VISA</span>
                <span class="card-badge mastercard" title="Mastercard">MC</span>
                <span class="card-badge amex" title="American Express">AMEX</span>
                <span class="card-badge jcb" title="JCB">JCB</span>
            </div>

            <p class="field-group-label">Card details</p>

            <form method="post" action="cart.php" class="payment-form" novalidate>
                <!-- Card Number -->
                <div class="field-group">
                    <label for="card_number" class="sr-only">Card number</label>
                    <div class="input-icon-wrap">
                        <input
                            type="text"
                            id="card_number"
                            name="card_number"
                            class="form-input"
                            placeholder="Card number"
                            maxlength="19"
                            inputmode="numeric"
                            autocomplete="cc-number"
                        />
                        <span class="input-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="5" width="20" height="14" rx="2"/>
                                <line x1="2" y1="10" x2="22" y2="10"/>
                            </svg>
                        </span>
                    </div>
                </div>

                <!-- Expiry + CVV -->
                <div class="field-row">
                    <div class="field-group">
                        <label for="expiry" class="sr-only">Expiration date</label>
                        <input
                            type="text"
                            id="expiry"
                            name="expiry"
                            class="form-input"
                            placeholder="MM / YY"
                            maxlength="7"
                            inputmode="numeric"
                            autocomplete="cc-exp"
                        />
                    </div>
                    <div class="field-group">
                        <label for="cvv" class="sr-only">CVV</label>
                        <div class="input-icon-wrap">
                            <input
                                type="text"
                                id="cvv"
                                name="cvv"
                                class="form-input"
                                placeholder="CVV"
                                maxlength="4"
                                inputmode="numeric"
                                autocomplete="cc-csc"
                            />
                            <span class="input-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="12" y1="8" x2="12" y2="12"/>
                                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Cardholder Name -->
                <div class="field-group">
                    <label for="card_name" class="sr-only">Cardholder name</label>
                    <input
                        type="text"
                        id="card_name"
                        name="card_name"
                        class="form-input"
                        placeholder="Name on card"
                        autocomplete="cc-name"
                    />
                </div>

                <!-- Security notice -->
                <p class="security-notice">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    Your payment is secure and encrypted
                </p>
            </form>
        </section>

    </div><!-- /.checkout-grid -->

    <!-- ══════════════════════════════
         ORDER SUMMARY
    ══════════════════════════════ -->
    <section class="card summary-card" aria-label="Order summary">
        <h2 class="card-heading summary-heading">Order summary</h2>

        <div class="summary-body">
            <div class="summary-lines">
                <div class="summary-row">
                    <span>Subtotal (<?= count($cart_items) ?> items)</span>
                    <span>₱<?= number_format($subtotal) ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>₱<?= number_format($shipping) ?></span>
                </div>
                <div class="summary-row summary-total">
                    <span>Total</span>
                    <span>₱<?= number_format($total) ?></span>
                </div>
            </div>

            <div class="summary-cta">
                <p class="pawpoints-notice">
                    🐾 Earn <?= $pawpoints ?> PawPoints on this order!
                </p>
                <form method="post" action="cart.php">
                    <button type="submit" name="action" value="checkout" class="pay-btn">
                        🔒 Pay ₱<?= number_format($total) ?>
                    </button>
                </form>
                <p class="ssl-notice">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    SSL Secured Checkout
                </p>
            </div>
        </div>
    </section>

</main>

<footer class="site-footer">
    <p>© <?= date('Y') ?> Pawster. All rights reserved.</p>
</footer>

</body>
</html>