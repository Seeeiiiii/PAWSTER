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
    /* Redirect back to the admin Sellers section */
    header('Location: /PAWSTER/admin.php#sellers');
    exit;
}

/* If nothing changed (already updated, or bad id) just go back */
header('Location: /PAWSTER/admin.php');
exit;