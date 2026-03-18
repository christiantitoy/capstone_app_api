<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once '/var/www/html/connection/db_connection.php';

try {
    // Get employee_id from POST or GET
    $employee_id = $_POST['employee_id'] ?? $_GET['employee_id'] ?? null;

    if (!$employee_id) {
        echo json_encode(["status" => "error", "message" => "Employee ID required"]);
        exit;
    }

    $employee_id = intval($employee_id);

    // Query to get employee's items with calculated fields - using employee_id directly
    $sql = "SELECT
                i.id,
                i.product_name,
                i.category,
                i.price as base_price,
                i.stock as base_stock,
                i.main_image_url,
                i.status,
                i.has_variations,

                -- Calculate min and max prices for items with variations
                CASE
                    WHEN i.has_variations = 1 THEN
                        (SELECT MIN(price) FROM item_variants iv WHERE iv.item_id = i.id)
                    ELSE i.price
                END as min_price,

                CASE
                    WHEN i.has_variations = 1 THEN
                        (SELECT MAX(price) FROM item_variants iv WHERE iv.item_id = i.id)
                    ELSE i.price
                END as max_price,

                -- Calculate total stock
                CASE
                    WHEN i.has_variations = 1 THEN
                        (SELECT COALESCE(SUM(stock), 0) FROM item_variants iv WHERE iv.item_id = i.id)
                    ELSE i.stock
                END as total_stock,

                -- Count variants
                (SELECT COUNT(*) FROM item_variants iv WHERE iv.item_id = i.id) as variant_count

            FROM items i
            WHERE i.employee_id = :employee_id  -- Changed from seller_id to employee_id
            ORDER BY i.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':employee_id' => $employee_id]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $items = [];

    foreach ($rows as $row) {
        $items[] = [
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
            "base_stock" => (int)$row['base_stock']
        ];
    }

    echo json_encode([
        "status" => "success",
        "message" => "Items retrieved successfully",
        "products" => $items,
        "count" => count($items),
        "employee_id" => $employee_id
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

// Close PDO connection
if (isset($conn) && $conn !== null) {
    $conn = null;
}
?>