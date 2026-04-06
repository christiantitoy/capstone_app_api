<?php
// /admin/backend/get_buyers.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

try {
    // Query to get all buyers
    $sql = "SELECT id, username, email, avatar_url FROM buyers ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $buyers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $buyers
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} finally {
    $conn = null; // Close the database connection
}
?>