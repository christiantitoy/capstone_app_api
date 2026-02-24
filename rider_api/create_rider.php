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

    $username = $data['username'];
    $email = $data['email'];
    $password = $data['password'];

    // Check if username/email exists
    $stmt = $conn->prepare("SELECT username, email FROM riders WHERE username = :username OR email = :email");
    $stmt->execute([
        ':username' => $username,
        ':email' => $email
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        if ($row['username'] === $username) {
            echo json_encode(["status" => "username_exists"]);
        } else {
            echo json_encode(["status" => "email_exists"]);
        }
        exit;
    }

    // Insert new rider
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO riders (username, email, password_hash) VALUES (:username, :email, :password_hash)");
    $result = $stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':password_hash' => $hashedPassword
    ]);

    if ($result) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to insert rider"]);
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