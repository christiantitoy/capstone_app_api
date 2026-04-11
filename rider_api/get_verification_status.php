<?php
// get_verification_status.php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

require_once '/var/www/html/connection/db_connection.php';

try {
    // Get rider_id from GET or POST
    $rider_id = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        $rider_id = isset($data['rider_id']) ? intval($data['rider_id']) : null;
    } else {
        $rider_id = isset($_GET['rider_id']) ? intval($_GET['rider_id']) : null;
    }

    // Validate rider_id
    if (!$rider_id || $rider_id <= 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Valid rider_id is required"
        ]);
        exit;
    }

    // Fetch rider verification status
    $stmt = $conn->prepare("
        SELECT id, verification_status, 
               CASE 
                   WHEN verification_status = 'complete' THEN true 
                   ELSE false 
               END as is_verified
        FROM riders 
        WHERE id = :rider_id
    ");
    
    $stmt->execute([':rider_id' => $rider_id]);
    $rider = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rider) {
        echo json_encode([
            "status" => "error",
            "message" => "Rider not found"
        ]);
        exit;
    }

    // Also check if verification documents exist
    $stmt = $conn->prepare("
        SELECT status, submitted_at, reviewed_at 
        FROM rider_verifications 
        WHERE rider_id = :rider_id
    ");
    $stmt->execute([':rider_id' => $rider_id]);
    $verification = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "rider_id" => (int)$rider['id'],
        "verification_status" => $rider['verification_status'],
        "is_verified" => (bool)$rider['is_verified'],
        "verification_details" => $verification ? [
            "status" => $verification['status'],
            "submitted_at" => $verification['submitted_at'],
            "reviewed_at" => $verification['reviewed_at']
        ] : null
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}

$conn = null;
?>