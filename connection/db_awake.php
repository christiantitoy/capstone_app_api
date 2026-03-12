<?php
// awake.php - Dedicated health check endpoint
header("Content-Type: application/json");

// Optional: Log the ping (remove in production)
$logFile = '../logs/cron_pings.log';
$timestamp = date('Y-m-d H:i:s');
file_put_contents($logFile, "[$timestamp] Ping received\n", FILE_APPEND);

// Quick database check (optional but safe)
try {
    require_once 'db_connection.php'; // Your existing connection
    $stmt = $conn->query("SELECT 1");
    $dbStatus = $stmt ? "ok" : "error";
} catch (Exception $e) {
    $dbStatus = "error: " . $e->getMessage();
}

echo json_encode([
    "status" => "ok",
    "message" => "Server is awake",
    "timestamp" => $timestamp,
    "database" => $dbStatus,
    "note" => "This endpoint is pinged every 24 hours for database verification"
]);
?>