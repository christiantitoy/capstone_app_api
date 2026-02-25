<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

// Include your database connection
require_once '/var/www/html/connection/db_connection.php';

// Get rider_id from GET or POST
$rider_id = 0;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rider_id = isset($_GET['rider_id']) ? intval($_GET['rider_id']) : 0;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $rider_id = isset($data['rider_id']) ? intval($data['rider_id']) : 0;
}

if ($rider_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid rider ID",
    ]);
    exit;
}

try {
    // Fetch rider data using PDO
    $stmt = $conn->prepare("SELECT id, username, email, status, created_at FROM riders WHERE id = ?");
    $stmt->execute([$rider_id]);
    $rider = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($rider) {
        echo json_encode([
            "status" => "success",
            "message" => "Rider data fetched successfully",
            "rider" => [
                "id" => intval($rider['id']),
                "riderName" => $rider['username'],
                "riderEmail" => $rider['email'],
                "status" => $rider['status'],
                "createdAt" => $rider['created_at']
            ]
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Rider not found"
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
}

// No need to explicitly close the connection with PDO
// The connection will be closed automatically when the script ends
?>