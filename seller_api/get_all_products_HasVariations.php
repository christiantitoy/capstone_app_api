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
                s.store_name as shop_name,
                io.option_name,
                io.option_value,
                iv.price,
                iv.stock,
                iv.image_urls 
            FROM items i
            JOIN stores s ON s.seller_id = i.seller_id
            JOIN item_options io ON io.item_id = i.id
            JOIN item_variants iv ON iv.item_id = i.id
            WHERE i.id = :id 
              AND i.status = 'approved'";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id]);
    
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $products = [];

    foreach ($result as $row) {
        $products[] = [
            'id' => (int)$row['id'],
            'seller_id' => (int)$row['seller_id'],
            'shop_name' => $row['shop_name'],
            'option_name' => $row['option_name'],
            'option_value' => $row['option_value'],
            'price' => (float)$row['price'],
            'stock' => (int)$row['stock'],
            'image_urls' => $row['image_urls']
        ];
    }

    if (empty($products)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Product not found'
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'products' => $products
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>