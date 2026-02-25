<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once '/var/www/html/connection/db_connection.php';

// Get POST parameters - handling both form-data and JSON input
$rider_id = 0;
$status = '';

// Check if it's JSON content type
if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
    $data = json_decode(file_get_contents("php://input"), true);
    $rider_id = isset($data['rider_id']) ? intval($data['rider_id']) : 0;
    $status = isset($data['status']) ? $data['status'] : '';
} else {
    // Regular form POST
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
    // Prepare and execute update query using PDO
    $stmt = $conn->prepare("UPDATE riders SET status = ? WHERE id = ?");
    $success = $stmt->execute([$status, $rider_id]);

    if ($success) {
        // Check if any row was actually affected
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                "status" => "success",
                "message" => "Rider status updated successfully",
                "new_status" => $status
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Rider not found or status unchanged"
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to update status"
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
}

// No need to explicitly close the connection with PDO
?>