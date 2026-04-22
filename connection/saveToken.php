<?php
header('Content-Type: application/json');

require_once '/var/www/html/connection/db_connection.php';

try {

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['user_id'], $data['role'], $data['fcm_token'])) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid input"
        ]);
        exit;
    }

    $user_id = (int)$data['user_id'];
    $role = $data['role'];
    $fcm_token = $data['fcm_token'];

    // ✅ FIXED UPSERT (one token per user)
    $stmt = $conn->prepare("
        INSERT INTO user_tokens (user_id, role, fcm_token)
        VALUES (:user_id, :role, :fcm_token)
        ON CONFLICT (user_id)
        DO UPDATE SET
            role = EXCLUDED.role,
            fcm_token = EXCLUDED.fcm_token,
            updated_at = CURRENT_TIMESTAMP
    ");

    $stmt->execute([
        ':user_id' => $user_id,
        ':role' => $role,
        ':fcm_token' => $fcm_token
    ]);

    echo json_encode([
        "status" => "success",
        "message" => "Token saved/updated"
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}

$conn = null;
?>