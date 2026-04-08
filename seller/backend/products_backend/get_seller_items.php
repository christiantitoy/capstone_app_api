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
    
    // Simple query - just get all products for this seller
    $stmt = $conn->prepare("
        SELECT 
            id,
            product_name,
            product_description,
            category,
            price,
            stock,
            main_image_url,
            status,
            created_at
        FROM items
        WHERE seller_id = ?
        ORDER BY created_at DESC
    ");
    
    $stmt->execute([$seller_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data for display
    foreach ($products as &$product) {
        $product['price_formatted'] = '₱' . number_format($product['price'], 2);
        $product['stock_status'] = $product['stock'] <= 0 ? 'out_of_stock' : ($product['stock'] <= 10 ? 'low_stock' : 'in_stock');
    }
    
    echo json_encode(['success' => true, 'products' => $products]);
    exit;
    
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'products' => []]);
    exit;
}
?>