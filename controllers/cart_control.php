<?php
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


$shipping = 60; 

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

    $addressid = (int)($_SESSION['selected_address'] ?? 0);
    if ($addressid === 0) {
        $_SESSION['order_error'] = 'Please select a delivery address before checking out.';
        header('Location: cart.php');
        exit();
    }

    $conn->begin_transaction();
    try {

        $addressid = (int)$_SESSION['selected_address'];

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

        $delivery_date = date('Y-m-d', strtotime('+4 days'));

        $order_stmt = $conn->prepare(
            "INSERT INTO tblorder (userid, addressid, order_status, total_price, shipping_fee, delivery_date)
             VALUES (?, ?, 'Pending', ?, 60.00, ?)"
        );
        $order_stmt->bind_param("iids", $userid, $addressid, $grand_total, $delivery_date);
        $order_stmt->execute();
        $new_orderid = $order_stmt->insert_id;
        $order_stmt->close();


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
$receipt_shipping = 60;

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