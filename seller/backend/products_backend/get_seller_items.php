<?php
// /seller/backend/products_backend/get_seller_items.php
require_once '/var/www/html/connection/db_connection.php';
require_once __DIR__ . '/../session/auth.php';

header('Content-Type: application/json');

try {
    $seller_id = $_SESSION['seller_id'] ?? null;
    
    if (!$seller_id) {
        echo json_encode(['success' => false, 'products' => []]);
        exit;
    }
    
    // Query to get ALL products including removed ones
    $stmt = $conn->prepare("
        SELECT 
            i.id,
            i.product_name,
            i.product_description,
            i.category,
            i.price,
            i.stock,
            i.main_image_url,
            i.status,
            i.created_at,
            i.has_variations,
            COUNT(iv.id) as variations_count
        FROM items i
        LEFT JOIN item_variants iv ON i.id = iv.item_id
        WHERE i.seller_id = ?
        GROUP BY i.id, i.product_name, i.product_description, i.category, i.price, i.stock, i.main_image_url, i.status, i.created_at, i.has_variations
        ORDER BY i.created_at DESC
    ");
    
    $stmt->execute([$seller_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data for display
    foreach ($products as &$product) {
        $product['price_formatted'] = '₱' . number_format($product['price'], 2);
        $product['stock_status'] = $product['stock'] <= 0 ? 'out_of_stock' : ($product['stock'] <= 10 ? 'low_stock' : 'in_stock');
        
        // Ensure variations_count is set (0 if no variations)
        $product['variations_count'] = (int)($product['variations_count'] ?? 0);
    }
    
    echo json_encode(['success' => true, 'products' => $products]);
    exit;
    
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'products' => []]);
    exit;
}
?>