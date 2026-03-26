<?php
header('Content-Type: application/json');

require_once '/var/www/html/connection/db_connection.php';

// Check if connection exists
if (!isset($conn) || $conn === null) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

try {
    // Get GET parameter
    $buyerId = isset($_GET['buyerId']) ? intval($_GET['buyerId']) : null;
    
    // Validate parameter
    if ($buyerId === null) {
        echo json_encode(['status' => 'error', 'message' => 'Missing buyerId parameter']);
        exit;
    }
    
    // Query to fetch favorite products with seller info
    $sql = "
        SELECT 
            i.id,
            i.product_name as title,
            i.price,
            s.full_name as shop,
            i.seller_id,
            i.main_image_url as image_url,
            f.created_at as favorited_at
        FROM favorites f
        INNER JOIN items i ON f.product_id = i.id
        INNER JOIN sellers s ON i.seller_id = s.id
        WHERE f.buyer_id = :buyerId
        AND i.status = 'approved'
        ORDER BY f.created_at DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([':buyerId' => $buyerId]);
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the response
    if (count($products) > 0) {
        echo json_encode([
            'status' => 'success',
            'products' => $products
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'products' => [],
            'message' => 'No favorite products found'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn = null;
?>