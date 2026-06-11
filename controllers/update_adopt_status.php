<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/config/app.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit();
}

$requestid  = intval($_POST['requestid'] ?? 0);
$new_status = trim($_POST['new_status'] ?? '');

if (!$requestid || !in_array($new_status, ['Approved', 'Rejected'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data.']);
    exit();
}

$stmt = $db->conn->prepare("UPDATE tbladoptionrequest SET status = ? WHERE requestid = ?");
$stmt->bind_param('si', $new_status, $requestid);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Status updated.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
$stmt->close();
