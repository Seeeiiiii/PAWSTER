<?php

ob_start();
error_reporting(0);
ini_set('display_errors', 0);

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
    ob_clean();
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


    $check = $conn->prepare(
        "SELECT p.productid FROM tblsellerproduct p
     JOIN tblsellerstatus s ON s.formid = p.sellerid
     WHERE p.productid = ? AND s.userid = ? LIMIT 1"
    );
    $check->bind_param("ii", $productid, $userid);
    $check->execute();
    $check->store_result();
    if ($check->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Product not found or not yours.']);
        exit();
    }
    $check->close();

    $controller = new ApplicationFormController();
    $success    = $controller->updateListing($productid, $userid, $category, $brand, $desc, $price, $photo);

    echo json_encode(
        $success
            ? ['success' => true]
            : ['success' => false, 'error' => 'Update failed. Check ownership, photo format (PNG, max 5MB), or try again.']
    );
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_listing') {
    ob_clean();
    header('Content-Type: application/json');

    $userid    = (int)($_SESSION['auth_user']['userid'] ?? 0);
    $productid = (int)($_POST['productid'] ?? 0);

    if (!$userid) {
        echo json_encode(['success' => false, 'error' => 'Not logged in.']);
        exit();
    }
    if (!$productid) {
        echo json_encode(['success' => false, 'error' => 'Invalid product.']);
        exit();
    }

    $check = $conn->prepare(
        "SELECT p.productid FROM tblsellerproduct p
         JOIN tblsellerstatus s ON s.formid = p.sellerid
         WHERE p.productid = ? AND s.userid = ? LIMIT 1"
    );
    $check->bind_param("ii", $productid, $userid);
    $check->execute();
    $check->store_result();
    $owned = $check->num_rows > 0;
    $check->close();

    if (!$owned) {
        echo json_encode(['success' => false, 'error' => 'Product not found or not yours.']);
        exit();
    }

    $del = $conn->prepare("UPDATE tblsellerproduct SET listing_status = 'deleted' WHERE productid = ?");
    $del->bind_param("i", $productid);
    $del->execute();
    $affected = $del->affected_rows;
    $del->close();

    echo json_encode(
        $affected > 0
            ? ['success' => true]
            : ['success' => false, 'error' => 'Delete failed. Please try again.']
    );
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile') {
    ob_clean();
    header('Content-Type: application/json');

    $userid  = (int)($_SESSION['auth_user']['userid'] ?? 0);
    $bname   = trim($_POST['business_name'] ?? '');
    $contact = trim($_POST['contact_num']   ?? '');
    $addr    = trim($_POST['address']       ?? '');

    if (!$userid) {
        echo json_encode(['success' => false, 'error' => 'Not logged in.']);
        exit();
    }


    $r = $conn->prepare("SELECT formid FROM tblsellerstatus WHERE userid = ? ORDER BY created_at DESC LIMIT 1");
    $r->bind_param("i", $userid);
    $r->execute();
    $r->bind_result($formid);
    $r->fetch();
    $r->close();

    if (!$formid) {
        echo json_encode(['success' => false, 'error' => 'Seller application not found.']);
        exit();
    }

 
    $check = $conn->prepare("SELECT sellerid FROM tblsellerprofile WHERE sellerid = ? LIMIT 1");
    $check->bind_param("i", $formid);
    $check->execute();
    $check->store_result();
    $exists = $check->num_rows > 0;
    $check->close();

    if ($exists) {
   
        $upd = $conn->prepare("UPDATE tblsellerprofile SET businessname = ?, contactnum = ?, address = ? WHERE sellerid = ?");
        $upd->bind_param("sssi", $bname, $contact, $addr, $formid);
        $upd->execute();
        if ($upd->affected_rows === 0 && $upd->errno) {
            echo json_encode(['success' => false, 'error' => 'Update failed: ' . $upd->error]);
            exit();
        }
    } else {

        $ins = $conn->prepare("INSERT INTO tblsellerprofile (sellerid, businessname, contactnum, address) VALUES (?, ?, ?, ?)");
        $ins->bind_param("isss", $formid, $bname, $contact, $addr);
        if (!$ins->execute()) {
            echo json_encode(['success' => false, 'error' => 'Failed to create seller profile.']);
            exit();
        }
    }

    echo json_encode([
        'success'       => true,
        'business_name' => $bname,
        'contact_num'   => $contact,
        'address'       => $addr
    ]);
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
     WHERE sellerid = ? AND listing_status != 'deleted'
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