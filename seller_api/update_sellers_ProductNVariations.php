<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once '/var/www/html/connection/db_connection.php';

try {
    // Get JSON body
    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input || !isset($input['product_id'])) {
        echo json_encode(["status" => "error", "message" => "Product ID required"]);
        exit;
    }

    // Start transaction
    $conn->beginTransaction();

    // Main product
    $product_id = intval($input['product_id']);
    $base_price = isset($input['base_price']) ? floatval($input['base_price']) : null;
    $base_stock = isset($input['base_stock']) ? intval($input['base_stock']) : null;

    // Update main product if price or stock is provided
    if ($base_price !== null || $base_stock !== null) {
        $updateFields = [];
        $params = [':product_id' => $product_id];

        if ($base_price !== null) {
            $updateFields[] = "price = :price";
            $params[':price'] = $base_price;
        }
        if ($base_stock !== null) {
            $updateFields[] = "stock = :stock";
            $params[':stock'] = $base_stock;
        }

        $updateProductSql = "UPDATE products SET " . implode(", ", $updateFields) . " WHERE id = :product_id";

        $stmt = $conn->prepare($updateProductSql);
        $stmt->execute($params);
    }

    // Variations
    if (isset($input['variations']) && is_array($input['variations'])) {
        foreach ($input['variations'] as $variant) {
            $variant_id = intval($variant['id']);
            $price = isset($variant['price']) ? floatval($variant['price']) : null;
            $stock = isset($variant['stock']) ? intval($variant['stock']) : null;

            if ($price !== null || $stock !== null) {
                $updateFields = [];
                $params = [':variant_id' => $variant_id];

                if ($price !== null) {
                    $updateFields[] = "price = :price";
                    $params[':price'] = $price;
                }
                if ($stock !== null) {
                    $updateFields[] = "stock = :stock";
                    $params[':stock'] = $stock;
                }

                $updateVariantSql = "UPDATE product_variants SET " . implode(", ", $updateFields) . " WHERE id = :variant_id";

                $stmt = $conn->prepare($updateVariantSql);
                $stmt->execute($params);
            }
        }
    }

    // Commit transaction
    $conn->commit();

    echo json_encode(["status" => "success", "message" => "Product and variations updated successfully"]);

} catch (PDOException $e) {
    // Rollback on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Rollback on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}

$conn = null; // Close PDO connection
?>