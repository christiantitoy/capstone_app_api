<?php
header('Content-Type: application/json');

require_once '/var/www/html/connection/db_connection.php';

try {
    // Get JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['email'], $data['password'])) {
        echo json_encode(["status" => "error", "message" => "Invalid input"]);
        exit;
    }

    $email = $data['email'];
    $password = $data['password'];

    // Fetch rider by email (includes is_confirmed)
    $stmt = $conn->prepare("SELECT id, password_hash, is_confirmed, status FROM riders WHERE email = :email");
    $stmt->execute([':email' => $email]);

    $rider = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rider) {
        echo json_encode(["status" => "email_not_found"]);
        exit;
    }

    // Verify password
    if (password_verify($password, $rider['password_hash'])) {

        // Only update status to online if account is confirmed
        if ($rider['is_confirmed']) {
            $updateStmt = $conn->prepare("UPDATE riders SET status = 'online' WHERE id = :id");
            $updateStmt->execute([':id' => $rider['id']]);
        }

        // Always return success with is_confirmed data
        echo json_encode([
            "status" => "success",
            "rider_id" => (int)$rider['id'],
            "is_confirmed" => (bool)$rider['is_confirmed']
        ]);

    } else {
        echo json_encode(["status" => "wrong_password"]);
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