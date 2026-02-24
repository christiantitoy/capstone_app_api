<?php
header("Content-Type: application/json");

// Database connection
require_once '/var/www/html/db_connection.php';

// Read POST values (can be JSON or form data)
$input = json_decode(file_get_contents('php://input'), true);

// Check if input is JSON, otherwise use POST
if ($input) {
    $email = isset($input['email']) ? trim($input['email']) : '';
    $password = isset($input['password']) ? trim($input['password']) : '';
} else {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
}

if (empty($email) || empty($password)) {
    echo json_encode([
        "status" => "error",
        "message" => "All fields are required"
    ]);
    exit;
}

try {
    // Check if user exists
    $stmt = $conn->prepare("SELECT id, username, email, password, avatar_url FROM buyers WHERE email = :email");
    $stmt->execute([':email' => $email]);
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $hashedPassword = $row['password'];

        if (password_verify($password, $hashedPassword)) {
            echo json_encode([
                "status" => "success",
                "message" => "Login successful",
                "id" => $row['id'],
                "username" => $row['username'],
                "email" => $row['email'],
                "avatar_url" => $row['avatar_url']
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
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
}

// Close connection (PDO automatically closes at end of script, but you can explicitly null it)
$conn = null;
?>