<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once '/var/www/html/connection/db_connection.php';

// Get POST parameters
$rider_id = 0;
$status = '';

if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
    $data = json_decode(file_get_contents("php://input"), true);
    $rider_id = isset($data['rider_id']) ? intval($data['rider_id']) : 0;
    $status = isset($data['status']) ? $data['status'] : '';
} else {
    $rider_id = isset($_POST['rider_id']) ? intval($_POST['rider_id']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';
}

if ($rider_id <= 0 || !in_array($status, ['online', 'offline'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid rider_id or status"
    ]);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE riders SET status = ? WHERE id = ?");
    $success = $stmt->execute([$status, $rider_id]);

    if ($success && $stmt->rowCount() > 0) {
        echo json_encode([
            "status" => "success",
            "message" => "Rider status updated successfully"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Rider not found or status unchanged"
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>