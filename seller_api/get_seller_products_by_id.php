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
    $sellerId = isset($_GET['seller_id']) ? intval($_GET['seller_id']) : null;
    
    // Validate parameter
    if ($sellerId === null) {
        echo json_encode(['status' => 'error', 'message' => 'Missing seller_id parameter']);
        exit;
    }
    
    // Query to fetch products with seller info
    $sql = "
        SELECT 
            i.id,
            i.product_name as title,
            i.price,
            i.sold,
            s.store_name as shop,
            i.seller_id,
            i.main_image_url as image_url
        FROM items i
        INNER JOIN stores s ON i.seller_id = s.seller_id
        WHERE i.seller_id = :sellerId
        AND i.status = 'approved'
        ORDER BY i.created_at DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([':sellerId' => $sellerId]);
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the response to match SellerProducts data class
    if (count($products) > 0) {
        // Format each product
        foreach ($products as &$product) {
            // Convert price to float
            $product['price'] = floatval($product['price']);
            
            // Convert sold to int
            $product['sold'] = (int)$product['sold'];
            
            // Ensure image_url is set, use default if null
            if (empty($product['image_url'])) {
                $product['image_url'] = "";
            }
            
            // Add imageRes field for compatibility (always 0)
            $product['imageRes'] = 0;
        }
        
        echo json_encode([
            'status' => 'success',
            'products' => $products,
            'count' => count($products)
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'products' => [],
            'count' => 0,
            'message' => 'No products found for this seller'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn = null;
?>