<?php
header('Content-Type: application/json');

require_once '/var/www/html/connection/db_connection.php';

// Check if connection exists
if (!isset($conn) || $conn === null) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

try {
    // ✅ Fetch products with sold > 0, ordered by most sold first
    // Join with stores table to get shop name and seller info
    $sql = "SELECT p.id, 
                   p.product_name, 
                   p.price, 
                   p.main_image_url, 
                   p.seller_id, 
                   p.sold,
                   p.stock,
                   p.product_description,
                   p.has_variations,
                   s.store_name as shop_name, 
                   s.logo_url,
                   s.contact_number,
                   (SELECT COUNT(*) FROM items WHERE seller_id = s.seller_id AND status = 'approved') as seller_products_count
            FROM items p 
            JOIN stores s ON p.seller_id = s.seller_id
            WHERE p.status = 'approved' 
              AND p.sold > 0
            ORDER BY p.sold DESC, p.id DESC
            LIMIT 50"; // Limit to top 50 best sellers

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $products = [];

    if (count($result) > 0) {
        foreach ($result as $row) {
            $products[] = [
                'id' => (int)$row['id'],
                'title' => $row['product_name'],
                'description' => $row['product_description'],
                'price' => (float)$row['price'],
                'stock' => (int)$row['stock'],
                'sold' => (int)$row['sold'],
                'shop' => $row['shop_name'],
                'seller_id' => (int)$row['seller_id'],
                'logo_url' => $row['logo_url'],
                'contact_number' => $row['contact_number'],
                'seller_products_count' => (int)$row['seller_products_count'],
                'has_variations' => (int)$row['has_variations'],
                'image_url' => $row['main_image_url'] ?? ''
            ];
        }
        
        echo json_encode([
            'status' => 'success',
            'products' => $products,
            'count' => count($products)
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'message' => 'No products with sales found',
            'products' => []
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn = null;
?>