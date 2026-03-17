<?php
// keep_alive.php - Place in your /var/www/html/ directory
header('Content-Type: application/json');

require_once '/var/www/html/connection/db_connection.php';

// Check connection
if (!isset($conn) || $conn === null) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Connection failed']);
    exit;
}

try {
    // Simple query that does minimal work
    $stmt = $conn->query("SELECT 1");
    $result = $stmt->fetch();
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Keep-alive ping successful',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Query failed']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn = null;
?>