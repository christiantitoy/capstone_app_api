<?php
header("Content-Type: application/json");


// Database connection
    require_once 'db_connection.php';

$userId = intval($_POST['user_id']);
$newUsername = $_POST['username'] ?? null;
$newEmail = $_POST['email'] ?? null;

if (!$userId) {
    echo json_encode(["status" => "error", "message" => "Missing user ID"]);
    exit;
}

// Prepare query dynamically based on inputs
$updates = [];
$params = [];
$types = "";

if ($newUsername) {
    $updates[] = "username = ?";
    $params[] = $newUsername;
    $types .= "s";
}

if ($newEmail) {
    $updates[] = "email = ?";
    $params[] = $newEmail;
    $types .= "s";
}

if (empty($updates)) {
    echo json_encode(["status" => "error", "message" => "Nothing to update"]);
    exit;
}

$params[] = $userId;
$types .= "i";

$sql = "UPDATE buyers SET " . implode(", ", $updates) . " WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Profile updated"]);
} else {
    echo json_encode(["status" => "error", "message" => "DB update failed"]);
}
?>
