<?php
// awake.php - Simple health check endpoint
header("Content-Type: application/json");

// Simple database check
try {
    require_once 'db_connection.php';
    $stmt = $conn->query("SELECT 1");
    $dbStatus = "ok";
} catch (Exception $e) {
    $dbStatus = "error";
}

echo json_encode([
    "status" => "ok",
    "message" => "Server is awake",
    "timestamp" => date('Y-m-d H:i:s'),
    "database" => $dbStatus
]);
?>