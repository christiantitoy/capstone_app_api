<?php
// /seller/backend/products_backend/delete_product.php
require_once '/var/www/html/connection/db_connection.php';
require_once __DIR__ . '/../session/auth.php';

header('Content-Type: application/json');

try {
    $seller_id = $_SESSION['seller_id'] ?? null;
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$seller_id) {
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit;
    }
    
    $product_id = $input['product_id'] ?? 0;
    
    if (!$product_id) {
        echo json_encode(['success' => false, 'message' => 'Product ID required']);
        exit;
    }
    
    // Verify product belongs to seller
    $stmt = $conn->prepare("SELECT id, has_variations FROM items WHERE id = ? AND seller_id = ?");
    $stmt->execute([$product_id, $seller_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    $conn->beginTransaction();
    
    // Delete variants first if product has variations
    if ($product['has_variations'] == 1) {
        $stmt = $conn->prepare("DELETE FROM item_variants WHERE item_id = ?");
        $stmt->execute([$product_id]);
    }
    
    // Delete the main product
    $stmt = $conn->prepare("DELETE FROM items WHERE id = ? AND seller_id = ?");
    $stmt->execute([$product_id, $seller_id]);
    
    if ($stmt->rowCount() > 0) {
        $conn->commit();
        echo json_encode([
            'success' => true, 
            'message' => 'Product deleted permanently'
        ]);
    } else {
        $conn->rollBack();
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to delete product'
        ]);
    }
    exit;
    
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    exit;
}
?>