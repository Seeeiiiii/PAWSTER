<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/config/app.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit();
}

// Must be logged in
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    echo json_encode(['status' => 'auth', 'message' => 'Please log in first.']);
    exit();
}

$userid = intval($_SESSION['auth_user']['userid'] ?? 0);
$petid  = intval($_POST['petid'] ?? 0);

if (!$userid || !$petid) {
    echo json_encode(['status' => 'error', 'message' => 'Missing data.']);
    exit();
}

// Check for duplicate request
$check = $db->conn->prepare("SELECT requestid FROM tbladoptionrequest WHERE userid = ? AND petid = ?");
$check->bind_param('ii', $userid, $petid);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['status' => 'duplicate', 'message' => 'You already sent a request for this pet.']);
    $check->close();
    exit();
}
$check->close();

// Insert request
$stmt = $db->conn->prepare("INSERT INTO tbladoptionrequest (userid, petid, status) VALUES (?, ?, 'Pending')");
$stmt->bind_param('ii', $userid, $petid);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Request sent! We\'ll get back to you soon.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Could not submit request. Try again.']);
}
$stmt->close();