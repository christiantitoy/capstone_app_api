<?php
header('Content-Type: application/json');

require_once '/var/www/html/connection/db_connection.php';

// Check if connection exists
if (!isset($conn) || $conn === null) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

try {
    // If an ID is passed in the query (e.g. get_all_products.php?id=5&buyerId=123)
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $buyerId = isset($_GET['buyerId']) ? intval($_GET['buyerId']) : null;

        $sql = "SELECT p.id, p.product_name, p.product_description, p.price, p.stock, p.sold, p.main_image_url, p.has_variations, 
                       s.store_name as shop_name, s.seller_id, s.logo_url, s.contact_number,
                       (SELECT COUNT(*) FROM items WHERE seller_id = s.seller_id AND status = 'approved') as seller_products_count
                FROM items p 
                JOIN stores s ON p.seller_id = s.seller_id
                WHERE p.id = :id AND p.status = 'approved'";

        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $products = [];

        if (count($result) > 0) {
            foreach ($result as $row) {
                // Check if this product is favorited by the buyer
                $isFavorite = false;
                if ($buyerId !== null) {
                    $checkFavSql = "SELECT COUNT(*) FROM favorites WHERE buyer_id = :buyerId AND product_id = :productId";
                    $favStmt = $conn->prepare($checkFavSql);
                    $favStmt->execute([
                        ':buyerId' => $buyerId,
                        ':productId' => $row['id']
                    ]);
                    $isFavorite = $favStmt->fetchColumn() > 0;
                }

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
                    'image_url' => $row['main_image_url'],
                    'is_favorite' => $isFavorite
                ];
            }
            echo json_encode(['status' => 'success', 'products' => $products]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Product not found']);
        }

        exit;
    }

    // ✅ UPDATED: Original list-all-products with RANDOM ORDER
    $sql = "SELECT p.id, p.product_name, p.price, p.sold, p.main_image_url, p.seller_id, 
                   s.store_name as shop_name, s.logo_url, s.contact_number,
                   (SELECT COUNT(*) FROM items WHERE seller_id = s.seller_id AND status = 'approved') as seller_products_count
            FROM items p 
            JOIN stores s ON p.seller_id = s.seller_id
            WHERE p.status = 'approved' 
            ORDER BY RANDOM()";  // ✅ PostgreSQL: RANDOM() instead of RAND()

    $stmt = $conn->query($sql);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $products = [];

    if (count($result) > 0) {
        foreach ($result as $row) {
            $products[] = [
                'id' => (int)$row['id'],
                'title' => $row['product_name'],
                'price' => (float)$row['price'],
                'sold' => (int)$row['sold'],
                'shop' => $row['shop_name'],
                'seller_id' => (int)$row['seller_id'],
                'logo_url' => $row['logo_url'],
                'contact_number' => $row['contact_number'],
                'seller_products_count' => (int)$row['seller_products_count'],
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