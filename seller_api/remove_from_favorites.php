<?php
header('Content-Type: application/json');

require_once '/var/www/html/connection/db_connection.php';

// Check if connection exists
if (!isset($conn) || $conn === null) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

try {
    // Get query parameters
    $buyerId = isset($_GET['buyerId']) ? intval($_GET['buyerId']) : null;
    $productId = isset($_GET['productId']) ? intval($_GET['productId']) : null;
    
    // Validate parameters
    if ($buyerId === null || $productId === null) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
        exit;
    }
    
    // Delete from favorites table
    $deleteSql = "DELETE FROM favorites WHERE buyer_id = :buyerId AND product_id = :productId";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->execute([
        ':buyerId' => $buyerId,
        ':productId' => $productId
    ]);
    
    // Check if deletion was successful
    if ($deleteStmt->rowCount() > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Removed from favorites successfully',
            'is_favorited' => false
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Product not found in favorites'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn = null;
?>