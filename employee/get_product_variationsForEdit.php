<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once '/var/www/html/connection/db_connection.php';

try {
    // Get item_id from POST or GET (keeping parameter name as product_id for frontend compatibility)
    $item_id = $_POST['product_id'] ?? $_GET['product_id'] ?? null;

    if (!$item_id) {
        echo json_encode(["status" => "error", "message" => "Product ID required"]);
        exit;
    }

    $item_id = intval($item_id);

    // Fetch variations for this item
    $sql = "SELECT
                id,
                options_json_value AS option_value,
                sku,
                price,
                stock
            FROM item_variants
            WHERE item_id = :item_id
            ORDER BY id ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':item_id' => $item_id]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $variations = [];

    foreach ($rows as $row) {
        $variations[] = [
            "id" => (int)$row['id'],
            "option_value" => $row['option_value'],
            "sku" => $row['sku'],
            "price" => (float)$row['price'],
            "stock" => (int)$row['stock']
        ];
    }

    echo json_encode([
        "status" => "success",
        "message" => "Variations retrieved successfully",
        "product_id" => $item_id, // Keeping "product_id" key for frontend compatibility
        "variations" => $variations
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
$conn = null;
?>