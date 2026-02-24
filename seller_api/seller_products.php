<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once '/var/www/html/connection/db_connection.php';

try {
    // Get seller_id from POST or GET
    $seller_id = $_POST['seller_id'] ?? $_GET['seller_id'] ?? null;

    if (!$seller_id) {
        echo json_encode(["status" => "error", "message" => "Seller ID required"]);
        exit;
    }

    $seller_id = intval($seller_id);

    // Query to get seller's products with calculated fields
    $sql = "SELECT
                p.id,
                p.product_name,
                p.category,
                p.price as base_price,
                p.stock as base_stock,
                p.main_image_url,
                p.status,
                p.has_variations,

                -- Calculate min and max prices for products with variations
                CASE
                    WHEN p.has_variations = 1 THEN
                        (SELECT MIN(price) FROM product_variants pv WHERE pv.product_id = p.id)
                    ELSE p.price
                END as min_price,

                CASE
                    WHEN p.has_variations = 1 THEN
                        (SELECT MAX(price) FROM product_variants pv WHERE pv.product_id = p.id)
                    ELSE p.price
                END as max_price,

                -- Calculate total stock
                CASE
                    WHEN p.has_variations = 1 THEN
                        (SELECT COALESCE(SUM(stock), 0) FROM product_variants pv WHERE pv.product_id = p.id)
                    ELSE p.stock
                END as total_stock,

                -- Count variants
                (SELECT COUNT(*) FROM product_variants pv WHERE pv.product_id = p.id) as variant_count

            FROM products p
            WHERE p.seller_id = :seller_id
            ORDER BY p.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':seller_id' => $seller_id]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $products = [];

    foreach ($rows as $row) {
        $products[] = [
            "id" => (int)$row['id'],
            "product_name" => $row['product_name'],
            "category" => $row['category'],
            "base_price" => (float)$row['base_price'],
            "min_price" => (float)$row['min_price'],
            "max_price" => (float)$row['max_price'],
            "total_stock" => (int)$row['total_stock'],
            "main_image_url" => $row['main_image_url'],
            "status" => $row['status'],
            "has_variations" => (bool)$row['has_variations'],
            "variant_count" => (int)$row['variant_count'],
            "base_stock" => (int)$row['base_stock']  // For simple products
        ];
    }

    echo json_encode([
        "status" => "success",
        "message" => "Products retrieved successfully",
        "products" => $products,
        "count" => count($products)
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}

$conn = null; // Close PDO connection
?>