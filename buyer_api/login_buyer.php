<?php
header("Content-Type: application/json");

// Database connection
require_once 'db_connection.php';

// Read and trim POST values
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if (empty($email) || empty($password)) {
    echo json_encode([
        "status" => "error",
        "message" => "All fields are required"
    ]);
    exit;
}

// Check if user exists - FIXED: Added avatar_url to SELECT
$stmt = $conn->prepare("SELECT id, username, email, password, avatar_url FROM buyers WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $hashedPassword = $row['password'];

    if (password_verify($password, $hashedPassword)) {
        echo json_encode([
            "status" => "success",
            "message" => "Login successful",
            "id" => $row['id'],
            "username" => $row['username'],
            "email" => $row['email'],
            "avatar_url" => $row['avatar_url'] // ADD THIS LINE
        ]);
    } else {
        echo json_encode([
            "status" => "error", 
            "message" => "Password is incorrect"
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Email not found"
    ]);
}

$conn->close();
?>