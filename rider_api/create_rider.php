<?php
header('Content-Type: application/json');

require_once '/var/www/html/connection/db_connection.php';

try {
    // Get JSON input
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || !isset($data['username'], $data['email'], $data['password'])) {
        echo json_encode(["status" => "error", "message" => "Invalid input"]);
        exit;
    }

    $username = trim($data['username']);
    $email = trim($data['email']);
    $password = $data['password'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format"]);
        exit;
    }

    // Validate password length
    if (strlen($password) < 8) {
        echo json_encode(["status" => "error", "message" => "Password must be at least 8 characters"]);
        exit;
    }

    // Check if username/email exists
    $stmt = $conn->prepare("SELECT username, email FROM riders WHERE username = :username OR email = :email");
    $stmt->execute([
        ':username' => $username,
        ':email' => $email
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        if ($row['username'] === $username) {
            echo json_encode(["status" => "username_exists", "message" => "Username already in use"]);
        } else {
            echo json_encode(["status" => "email_exists", "message" => "Email already in use"]);
        }
        exit;
    }

    // Insert new rider
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    // Default values
    $status = 'offline';
    $verification_status = 'none';

    $stmt = $conn->prepare("
        INSERT INTO riders (username, email, password_hash, status, verification_status, created_at) 
        VALUES (:username, :email, :password_hash, :status, :verification_status, NOW())
    ");
    
    $result = $stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':password_hash' => $hashedPassword,
        ':status' => 'offline',
        ':verification_status' => 'none'
    ]);

    if ($result) {
        $riderId = $conn->lastInsertId();
        echo json_encode([
            "status" => "success", 
            "message" => "Account created successfully",
            "rider_id" => $riderId,
            "verification_status" => "none"
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to create account"]);
    }

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}

$conn = null; // Close PDO connection
?>