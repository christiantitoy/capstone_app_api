<?php
header('Content-Type: application/json');
require 'db_connection.php';

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['username'], $data['email'], $data['password'])) {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
    exit;
}

$username = $data['username'];
$email = $data['email'];
$password = $data['password'];

// Check if username/email exists
$stmt = $conn->prepare("SELECT username, email FROM riders WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if ($row['username'] === $username) echo json_encode(["status" => "username_exists"]);
    else echo json_encode(["status" => "email_exists"]);
    exit;
}

// Insert new rider
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
$stmt = $conn->prepare("INSERT INTO riders (username, email, password_hash) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $hashedPassword);

if ($stmt->execute()) echo json_encode(["status" => "success"]);
else echo json_encode(["status" => "error", "message" => $stmt->error]);

$stmt->close();
$conn->close();
?>
