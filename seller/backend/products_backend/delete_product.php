<?php
require_once '/var/www/html/connection/db_connection.php';
require_once __DIR__ . '/../session/auth.php';

header('Content-Type: application/json');

try {
    $seller_id = $_SESSION['seller_id'] ?? null;
    $input = json_decode(file_get_contents('php://input'), true);
    
    $product_id = $input['product_id'] ?? 0;
    
    if (!$seller_id || !$product_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }
    
    // Verify product belongs to seller and get current status
    $stmt = $conn->prepare("
        SELECT id, status, product_name 
        FROM items 
        WHERE id = ? AND seller_id = ?
    ");
    $stmt->execute([$product_id, $seller_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    // Check if product is already removed
    if ($product['status'] === 'removed') {
        echo json_encode(['success' => false, 'message' => 'Product is already removed']);
        exit;
    }
    
    // Update status to 'removed' instead of hard delete
    $stmt = $conn->prepare("
        UPDATE items 
        SET status = 'removed', updated_at = CURRENT_TIMESTAMP 
        WHERE id = ? AND seller_id = ?
    ");
    $stmt->execute([$product_id, $seller_id]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Product has been removed successfully',
        'product_name' => $product['product_name']
    ]);
    exit;
    
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    exit;
}
?>