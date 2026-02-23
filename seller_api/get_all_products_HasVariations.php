<?php
header('Content-Type: application/json');
require 'db_connection.php';

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
                p.id,
                p.seller_id,
                s.shop_name,
                po.option_name,
                po.option_value,
                pv.price,
                pv.stock,
                pv.image_urls 
            FROM products p
            JOIN seller_profiles s ON s.id = p.seller_id
            JOIN product_options po ON po.product_id = p.id
            JOIN product_variants pv ON pv.product_id = p.id
            WHERE p.id = ? 
              AND p.status = 'approved'";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    $products = [];

    while ($row = $result->fetch_assoc()) {
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

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
