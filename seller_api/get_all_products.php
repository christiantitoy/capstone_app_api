<?php
header('Content-Type: application/json');

require_once '/var/www/html/connection/db_connection.php';

// Check if connection exists
if (!isset($conn) || $conn === null) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

try {
    // If an ID is passed in the query (e.g. get_all_products.php?id=5)
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);

        $sql = "SELECT p.id, p.product_name, p.product_description, p.price, p.stock, p.main_image_url, p.has_variations, s.shop_name 
            FROM products p 
            JOIN seller_profiles s ON p.seller_id = s.id
            WHERE p.id = :id AND p.status = 'approved'";

        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
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
                    'shop' => $row['shop_name'],
                    'has_variations' => (int)$row['has_variations'],
                    'image_url' => $row['main_image_url']
                ];
            }
            echo json_encode(['status' => 'success', 'products' => $products]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Product not found']);
        }

        exit;
    }

    // Original list-all-products code remains here:
    $sql = "SELECT p.id, p.product_name, p.price, p.main_image_url, p.seller_id, s.store_name as shop_name 
        FROM items p 
        JOIN stores s ON p.seller_id = s.seller_id
        WHERE p.status = 'approved' 
        ORDER BY p.id DESC";

    $stmt = $conn->query($sql);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $products = [];

    if (count($result) > 0) {
        foreach ($result as $row) {
            $products[] = [
                'id' => (int)$row['id'],
                'title' => $row['product_name'],
                'price' => (float)$row['price'],
                'shop' => $row['shop_name'],
                'seller_id' => $row['seller_id'],
                'image_url' => $row['main_image_url']
            ];
        }
        echo json_encode(['status' => 'success', 'products' => $products]);
    } else {
        echo json_encode(['status' => 'success', 'message' => 'No Products Found Boss']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn = null;

?>