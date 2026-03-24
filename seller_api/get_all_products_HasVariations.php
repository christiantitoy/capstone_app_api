<?php
header('Content-Type: application/json');

require_once '/var/www/html/connection/db_connection.php';

try {

    if (!isset($_GET['id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Product ID is required'
        ]);
        exit;
    }

    $id = intval($_GET['id']);

    $sql = "SELECT 
                i.id,
                i.seller_id,
                i.product_name,
                i.product_description,
                i.price,
                i.stock,
                i.main_image_url,
                i.image_urls,
                i.has_variations,
                s.store_name AS shop_name
            FROM items i
            JOIN stores s ON s.seller_id = i.seller_id
            WHERE i.id = :id
              AND i.status = 'approved'
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Product not found'
        ]);
        exit;
    }

    $product = [
        'id' => (int)$row['id'],
        'seller_id' => (int)$row['seller_id'],
        'title' => $row['product_name'], // ✅ mapped
        'description' => $row['product_description'], // ✅ mapped
        'price' => (float)$row['price'],
        'stock' => (int)$row['stock'],
        'shop_name' => $row['shop_name'],
        'image_url' => $row['main_image_url'] ?? '', // ✅ main image
        'image_urls' => $row['image_urls'] ?? '', // optional extra images
        'has_variations' => (int)$row['has_variations']
    ];

    echo json_encode([
        'status' => 'success',
        'products' => [$product]
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>