<?php
/**
 * update_seller_status.php
 * Handles Approve / Reject POST from the admin seller applications table.
 * Place this file at: /PAWSTER/authentication/update_seller_status.php
 */

include_once __DIR__ . '/../config/app.php';

/* ── Only accept POST ── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

$status_id  = isset($_POST['status_id'])  ? (int) $_POST['status_id']           : 0;
$new_status = isset($_POST['new_status']) ? trim($_POST['new_status'])           : '';

/* ── Whitelist the two allowed status values ── */
$allowed = ['verified', 'rejected'];
if ($status_id <= 0 || !in_array($new_status, $allowed, true)) {
    http_response_code(400);
    exit('Invalid request.');
}

$db   = new DatabaseConnection();
$conn = $db->conn;

/*
 * UPDATE tblsellerstatus
 * The created_at column uses ON UPDATE current_timestamp(), so it will
 * automatically record the time the decision was made.
 */
$stmt = $conn->prepare(
    "UPDATE tblsellerstatus SET status = ? WHERE status_id = ?"
);
$stmt->bind_param("si", $new_status, $status_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {


    if ($new_status === 'verified') {


        $r = $conn->prepare(
            "SELECT formid FROM tblsellerstatus WHERE status_id = ? LIMIT 1"
        );
        $r->bind_param("i", $status_id);
        $r->execute();
        $r->bind_result($formid);
        $r->fetch();
        $r->close();

        if ($formid) {

            $fetch = $conn->prepare(
                "SELECT business_name, contact_num, dti_reg, bir_reg, address
                 FROM tblapplicationform
                 WHERE formid = ?
                 LIMIT 1"
            );
            $fetch->bind_param("i", $formid);
            $fetch->execute();
            $fetch->bind_result($business_name, $contact_num, $dti_reg, $bir_reg, $address);
            $fetch->fetch();
            $fetch->close();

            $ins = $conn->prepare(
                "INSERT INTO tblsellerprofile (sellerid, businessname, contactnum, dti_reg, bir_reg, address)
                 VALUES (?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                     businessname = VALUES(businessname),
                     contactnum   = VALUES(contactnum),
                     dti_reg      = VALUES(dti_reg),
                     bir_reg      = VALUES(bir_reg),
                     address      = VALUES(address)"
            );
            $ins->bind_param("issiis", $formid, $business_name, $contact_num, $dti_reg, $bir_reg, $address);
            $ins->execute();
            $ins->close();
        }
    }

    header('Location: /PAWSTER/admin.php#sellers');
    exit;
}

header('Location: /PAWSTER/admin.php');
exit;