<?php
include_once __DIR__ . '/../config/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

$status_id  = isset($_POST['status_id'])  ? (int) $_POST['status_id']  : 0;
$new_status = isset($_POST['new_status']) ? trim($_POST['new_status'])  : '';

$allowed = ['verified', 'rejected'];
if ($status_id <= 0 || !in_array($new_status, $allowed, true)) {
    http_response_code(400);
    exit('Invalid request.');
}

$db   = new DatabaseConnection();
$conn = $db->conn;

$stmt = $conn->prepare(
    "UPDATE tblsellerstatus SET status = ? WHERE status_id = ?"
);
$stmt->bind_param("si", $new_status, $status_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {

    if ($new_status === 'verified') {


        $r = $conn->prepare(
            "SELECT userid, formid FROM tblsellerstatus WHERE status_id = ? LIMIT 1"
        );
        $r->bind_param("i", $status_id);
        $r->execute();
        $r->bind_result($userid, $formid);
        $r->fetch();
        $r->close();

        if ($formid) {

            $fetch = $conn->prepare(
                "SELECT business_name, contact_num, dti_reg, bir_reg, address, business_permit, valid_id
         FROM tblapplicationform
         WHERE formid = ?
         LIMIT 1"
            );
            $fetch->bind_param("i", $formid);
            $fetch->execute();
            $fetch->bind_result($business_name, $contact_num, $dti_reg, $bir_reg, $address, $business_permit, $valid_id);
            $fetch->fetch();
            $fetch->close();

            $ins = $conn->prepare(
                "INSERT INTO tblsellerprofile (userid, businessname, contactnum, dti_reg, bir_reg, address, business_permit, valid_id)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
             businessname     = VALUES(businessname),
             contactnum       = VALUES(contactnum),
             dti_reg          = VALUES(dti_reg),
             bir_reg          = VALUES(bir_reg),
             address          = VALUES(address),
             business_permit  = VALUES(business_permit),
             valid_id         = VALUES(valid_id)"
            );
            $ins->bind_param("issiiiss", $userid, $business_name, $contact_num, $dti_reg, $bir_reg, $address, $business_permit, $valid_id);
            $ins->execute();
            $ins->close();
        }
    }

    header('Location: /PAWSTER/admin.php#sellers');
    exit;
}

header('Location: /PAWSTER/admin.php');
exit;
