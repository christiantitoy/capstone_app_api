<?php
require_once '/var/www/html/connection/db_connection.php';
require_once __DIR__ . '/../session/auth.php';

header('Content-Type: application/json');

try {
    $seller_id = $_SESSION['seller_id'] ?? null;
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if (!$seller_id || !$product_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }
    
    // Get product details and verify it belongs to seller
    $stmt = $conn->prepare("
        SELECT 
            id, product_name, product_description, category, price, stock, 
            main_image_url, image_urls, status, has_variations, created_at
        FROM items
        WHERE id = ? AND seller_id = ?
    ");
    $stmt->execute([$product_id, $seller_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    // Get variations if product has variations
    $variations = [];
    if ($product['has_variations'] == 1) {
        $stmt = $conn->prepare("
            SELECT id, options_json, options_json_value, price, stock, sku, image_urls, created_at
            FROM item_variants
            WHERE item_id = ?
            ORDER BY id ASC
        ");
        $stmt->execute([$product_id]);
        $variations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'product' => $product,
        'variations' => $variations
    ]);
    exit;
    
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}
?>