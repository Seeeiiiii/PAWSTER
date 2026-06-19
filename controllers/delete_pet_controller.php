<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/config/app.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit();
}

$petid = intval($_POST['petid'] ?? 0);
if (!$petid) {
    echo json_encode(['status' => 'error', 'message' => 'Missing pet ID.']);
    exit();
}

$stmt = $db->conn->prepare("DELETE FROM tblpets WHERE petid = ?");
$stmt->bind_param('i', $petid);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Pet removed.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Pet not found.']);
    }
} else {
    // MySQL errno 1451 = FK constraint violation (e.g. pet has adoption requests)
    if ($db->conn->errno === 1451) {
        echo json_encode(['status' => 'error', 'message' => 'Can\'t remove this pet — it already has adoption request(s) tied to it.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error.']);
    }
}
$stmt->close();
