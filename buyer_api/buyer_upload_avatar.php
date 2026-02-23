<?php
// upload_avatar.php
header("Content-Type: application/json");

// Database connection
    require_once 'db_connection.php';

// Check inputs
if (!isset($_FILES['avatar']) || !isset($_POST['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Missing parameters"]);
    exit;
}

$userId = intval($_POST['user_id']);
$file = $_FILES['avatar'];

// Validate type + size
$allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExts)) {
    echo json_encode(["status" => "error", "message" => "Invalid file extension: " . $ext]);
    exit;
}

if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(["status" => "error", "message" => "File too large"]);
    exit;
}

// Prepare folder
$uploadDir = __DIR__ . "/uploads/avatars/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Unique filename
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$newFileName = "user_" . $userId . "_" . time() . "." . $ext;
$targetFile = $uploadDir . $newFileName;

// Save file
if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
    echo json_encode(["status" => "error", "message" => "Upload failed"]);
    exit;
}

// URL for DB
$avatarUrl = "http://" . $_SERVER['HTTP_HOST'] . "/capstone_app_api/uploads/avatars/" . $newFileName;

// Update DB
$sql = "UPDATE buyers SET avatar_url = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $avatarUrl, $userId);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "avatar_url" => $avatarUrl]);
} else {
    echo json_encode(["status" => "error", "message" => "DB update failed"]);
}
?>
