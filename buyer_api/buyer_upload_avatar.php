<?php
// upload_avatar.php
header("Content-Type: application/json");


require_once '/var/www/html/db_connection.php';

// Check inputs
if (!isset($_FILES['avatar']) || !isset($_POST['user_id'])) {
    die(json_encode(["status" => "error", "message" => "Missing parameters"]));
}

$userId = intval($_POST['user_id']);
$file = $_FILES['avatar'];

// Basic validation
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
    die(json_encode(["status" => "error", "message" => "Only JPG/PNG allowed"]));
}

if ($file['size'] > 5 * 1024 * 1024) {
    die(json_encode(["status" => "error", "message" => "File too large"]));
}

// Save file
$uploadDir = __DIR__ . "/uploads/avatars/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$newFileName = "user_{$userId}_" . time() . ".$ext";
$targetFile = $uploadDir . $newFileName;

if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
    die(json_encode(["status" => "error", "message" => "Save failed"]));
}

// Update DB
$avatarUrl = "https://" . $_SERVER['HTTP_HOST'] . "/uploads/avatars/" . $newFileName;

try {
    $stmt = $conn->prepare("UPDATE buyers SET avatar_url = ? WHERE id = ?");
    $stmt->execute([$avatarUrl, $userId]);
    
    echo json_encode(["status" => "success", "avatar_url" => $avatarUrl]);
} catch (PDOException $e) {
    unlink($targetFile);
    echo json_encode(["status" => "error", "message" => "DB update failed"]);
}
?>