<?php
header('Content-Type: application/json');
require 'db_connection.php'; // your existing DB connection

try {
    //  If an ID is passed in the query (e.g. get_all_products.php?id=5)
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);

        $sql = "SELECT p.id, p.product_name, p.product_description, p.price, p.stock, p.main_image_url, p.has_variations, s.shop_name 
            FROM products p 
            JOIN seller_profiles s ON p.seller_id = s.id
            WHERE p.id = ? AND p.status = 'approved'";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
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


    //  Original list-all-products code remains here:
    $sql = "SELECT p.id, p.product_name, p.price, p.main_image_url, p.seller_id, s.shop_name 
            FROM products p 
            JOIN seller_profiles s ON p.seller_id = s.id
            WHERE p.status = 'approved' 
            ORDER BY p.id DESC";

    $result = $conn->query($sql);

    $products = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
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
        echo json_encode(['status' => 'success', 'No Products Found Boss']);
    }

    

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
