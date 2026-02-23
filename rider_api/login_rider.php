<?php
header('Content-Type: application/json');
require 'db_connection.php';

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['email'], $data['password'])) {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
    exit;
}

$email = $data['email'];
$password = $data['password'];

// Fetch rider by email
$stmt = $conn->prepare("SELECT id, password_hash FROM riders WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "email_not_found"]);
    exit;
}

$rider = $result->fetch_assoc();

// Verify password
if (password_verify($password, $rider['password_hash'])) {

    // ✅ Update rider status to ONLINE
    $updateStmt = $conn->prepare("UPDATE riders SET status = 'online' WHERE id = ?");
    $updateStmt->bind_param("i", $rider['id']);
    $updateStmt->execute();
    $updateStmt->close();

    // ✅ Return rider_id
    echo json_encode([
        "status" => "success",
        "rider_id" => (int)$rider['id']
    ]);

} else {
    echo json_encode(["status" => "wrong_password"]);
}

$stmt->close();
$conn->close();
?>
