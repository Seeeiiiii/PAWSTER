<?php
/**
 * update_appointment_status.php
 * Place this file at: /PAWSTER/controllers/update_appointment_status.php
 *
 * Responds with JSON — called via fetch() from admin.php (no page reload).
 */

include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/config/app.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

$appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;
$new_status     = isset($_POST['new_status'])     ? trim($_POST['new_status'])       : '';

$allowed_statuses = ['Approved', 'Rejected'];
if ($appointment_id <= 0 || !in_array($new_status, $allowed_statuses)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit;
}

$db   = new DatabaseConnection();
$conn = $db->conn;

$stmt = $conn->prepare("UPDATE tblappointment SET status = ? WHERE appointmentID = ?");
$stmt->bind_param("si", $new_status, $appointment_id);

if ($stmt->execute()) {
    $stmt->close();
    echo json_encode(['status' => 'success', 'new_status' => $new_status]);
} else {
    echo json_encode(['status' => 'error', 'message' => $conn->error]);
}