<?php
header('Content-Type: application/json');

require_once '/var/www/html/connection/db_connection.php';

// Check if connection exists
if (!isset($conn) || $conn === null) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

try {
    // Get POST parameters
    $buyerId = isset($_POST['buyerId']) ? intval($_POST['buyerId']) : null;
    $productId = isset($_POST['productId']) ? intval($_POST['productId']) : null;
    
    // Validate parameters
    if ($buyerId === null || $productId === null) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
        exit;
    }
    
    // Check if favorite already exists
    $checkSql = "SELECT id FROM favorites WHERE buyer_id = :buyerId AND product_id = :productId";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->execute([
        ':buyerId' => $buyerId,
        ':productId' => $productId
    ]);
    
    if ($checkStmt->rowCount() > 0) {
        // Already favorited
        echo json_encode([
            'status' => 'success', 
            'message' => 'Product already in favorites',
            'is_favorited' => true
        ]);
        exit;
    }
    
    // Insert into favorites table
    $insertSql = "INSERT INTO favorites (buyer_id, product_id, created_at) 
                  VALUES (:buyerId, :productId, CURRENT_TIMESTAMP)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->execute([
        ':buyerId' => $buyerId,
        ':productId' => $productId
    ]);
    
    // Check if insertion was successful
    if ($insertStmt->rowCount() > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Added to favorites successfully',
            'is_favorited' => true
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to add to favorites'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn = null;
?>