<?php
header("Content-Type: application/json; charset=UTF-8");

// Database connection
require_once 'db_connection.php';

// Get POST fields
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

// Check if username exists
$stmt = $conn->prepare("SELECT id FROM buyers WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Username already used"]);
    exit;
}
$stmt->close();

// Check if email exists
$stmt = $conn->prepare("SELECT id FROM buyers WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Email already used"]);
    exit;
}
$stmt->close();

// Hash password for security
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert new user
$stmt = $conn->prepare("INSERT INTO buyers (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $hashedPassword);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id;

    // Fetch inserted user
    $fetch = $conn->prepare("SELECT id, username, email, avatar_url FROM buyers WHERE id = ?");
    $fetch->bind_param("i", $user_id);
    $fetch->execute();
    $res = $fetch->get_result();
    $user = $res->fetch_assoc();

    echo json_encode([
        "status" => "success",
        "message" => "Account created successfully",
        "id" => $user["id"],
        "username" => $user["username"],
        "email" => $user["email"],
        "avatar_url" => $user["avatar_url"]
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Account creation failed"]);
}

$stmt->close();
$conn->close();
?>
