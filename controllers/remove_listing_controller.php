<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/config/app.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit();
}

$productid = intval($_POST['productid'] ?? 0);
if (!$productid) {
    echo json_encode(['status' => 'error', 'message' => 'Missing product ID.']);
    exit();
}

$stmt = $db->conn->prepare("UPDATE tblsellerproduct SET listing_status = 'removed' WHERE productid = ?");
$stmt->bind_param('i', $productid);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Listing removed.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
$stmt->close();