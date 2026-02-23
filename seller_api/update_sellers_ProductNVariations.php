<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'db_connection.php';

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "DB connection failed"]));
}

// Get JSON body
$input = json_decode(file_get_contents("php://input"), true);

if (!$input || !isset($input['product_id'])) {
    echo json_encode(["status" => "error", "message" => "Product ID required"]);
    exit;
}

// Main product
$product_id = intval($input['product_id']);
$base_price = isset($input['base_price']) ? floatval($input['base_price']) : null;
$base_stock = isset($input['base_stock']) ? intval($input['base_stock']) : null;

// Update main product if price or stock is provided
if ($base_price !== null || $base_stock !== null) {
    $setClauses = [];
    if ($base_price !== null) $setClauses[] = "price=$base_price";
    if ($base_stock !== null) $setClauses[] = "stock=$base_stock";

    $setSql = implode(", ", $setClauses);

    $updateProductSql = "UPDATE products SET $setSql WHERE id=$product_id";

    if (!$conn->query($updateProductSql)) {
        echo json_encode(["status" => "error", "message" => "Failed to update product: " . $conn->error]);
        exit;
    }
}

// Variations
if (isset($input['variations']) && is_array($input['variations'])) {
    foreach ($input['variations'] as $variant) {
        $variant_id = intval($variant['id']);
        $price = isset($variant['price']) ? floatval($variant['price']) : null;
        $stock = isset($variant['stock']) ? intval($variant['stock']) : null;

        if ($price !== null || $stock !== null) {
            $setClauses = [];
            if ($price !== null) $setClauses[] = "price=$price";
            if ($stock !== null) $setClauses[] = "stock=$stock";

            $setSql = implode(", ", $setClauses);

            $updateVariantSql = "UPDATE product_variants SET $setSql WHERE id=$variant_id";

            if (!$conn->query($updateVariantSql)) {
                echo json_encode(["status" => "error", "message" => "Failed to update variant ID $variant_id: " . $conn->error]);
                exit;
            }
        }
    }
}

echo json_encode(["status" => "success", "message" => "Product and variations updated successfully"]);

$conn->close();
?>
