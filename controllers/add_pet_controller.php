<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/config/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit();
}

$name         = trim($_POST['name'] ?? '');
$category     = trim($_POST['category'] ?? 'Others');
$breed        = trim($_POST['breed'] ?? '');
$age          = trim($_POST['age'] ?? '');
$sex          = trim($_POST['sex'] ?? '');
$color        = trim($_POST['color'] ?? '');
$weight       = trim($_POST['weight'] ?? '');
$temperament  = trim($_POST['temperament'] ?? '');
$good_kids    = trim($_POST['good_with_kids'] ?? 'Yes');
$location     = trim($_POST['location'] ?? '');
$docs         = trim($_POST['docs'] ?? '');

if (!$name || !$breed || !$age || !$sex) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
    exit();
}

// Handle image upload
$image = '';
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = 'pet_' . uniqid() . '.' . $ext;
    $dest     = $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/uploads/pets/' . $filename;

    if (!is_dir(dirname($dest))) {
        mkdir(dirname($dest), 0775, true);
    }

    if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
        $image = $filename;
    }
}

$stmt = $db->conn->prepare(
    "INSERT INTO tblpets (name, category, breed, age, sex, color, weight, temperament, good_with_kids, location, docs, image)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param('ssssssssssss', $name, $category, $breed, $age, $sex, $color, $weight, $temperament, $good_kids, $location, $docs, $image);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Pet added successfully!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $db->conn->error]);
}
$stmt->close();