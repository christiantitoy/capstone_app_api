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

    // Query to get employee's items with calculated fields
    $sql = "SELECT
                i.id,
                i.product_name,
                i.category,
                i.price as base_price,
                i.stock as base_stock,
                i.main_image_url,
                i.image_urls,
                i.product_description,
                i.status,
                i.has_variations,
                i.created_at,
                i.updated_at,

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
            WHERE i.employee_id = :employee_id
            ORDER BY i.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':employee_id' => $employee_id]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $items = [];

    foreach ($rows as $row) {
        $item_id = (int)$row['id'];
        
        // Fetch variants for this item if it has variations
        $variants = [];
        if ($row['has_variations'] == 1) {
            $variantSql = "SELECT 
                            iv.id,
                            iv.item_id,
                            iv.options_json,
                            iv.options_json_value,
                            iv.price,
                            iv.stock,
                            iv.sku,
                            iv.image_urls,
                            iv.created_at
                          FROM item_variants iv 
                          WHERE iv.item_id = :item_id
                          ORDER BY iv.id ASC";
            
            $variantStmt = $conn->prepare($variantSql);
            $variantStmt->execute([':item_id' => $item_id]);
            $variantRows = $variantStmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($variantRows as $variantRow) {
                // Parse options_json if it exists
                $options = null;
                if (!empty($variantRow['options_json'])) {
                    $options = json_decode($variantRow['options_json'], true);
                } elseif (!empty($variantRow['options_json_value'])) {
                    $options = json_decode($variantRow['options_json_value'], true);
                }
                
                // Parse image_urls into array
                $variantImageUrls = [];
                if (!empty($variantRow['image_urls'])) {
                    $variantImageUrls = array_filter(
                        array_map('trim', explode(',', $variantRow['image_urls']))
                    );
                }
                
                $variants[] = [
                    "id" => (int)$variantRow['id'],
                    "item_id" => (int)$variantRow['item_id'],
                    "options" => $options,
                    "price" => (float)$variantRow['price'],
                    "stock" => (int)$variantRow['stock'],
                    "sku" => $variantRow['sku'],
                    "image_urls" => $variantImageUrls,
                    "image_urls_string" => $variantRow['image_urls'],
                    "created_at" => $variantRow['created_at']
                ];
            }
        }
        
        // Parse main image_urls into array
        $imageUrls = [];
        if (!empty($row['image_urls'])) {
            $imageUrls = array_filter(
                array_map('trim', explode(',', $row['image_urls']))
            );
        }
        
        $items[] = [
            "id" => $item_id,
            "product_name" => $row['product_name'],
            "product_description" => $row['product_description'] ?? '',
            "category" => $row['category'],
            "base_price" => (float)$row['base_price'],
            "min_price" => (float)$row['min_price'],
            "max_price" => (float)$row['max_price'],
            "total_stock" => (int)$row['total_stock'],
            "base_stock" => (int)$row['base_stock'],
            "main_image_url" => $row['main_image_url'],
            "image_urls" => $imageUrls,
            "image_urls_string" => $row['image_urls'],
            "status" => $row['status'],
            "has_variations" => (bool)$row['has_variations'],
            "variant_count" => (int)$row['variant_count'],
            "variants" => $variants,
            "created_at" => $row['created_at'],
            "updated_at" => $row['updated_at']
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