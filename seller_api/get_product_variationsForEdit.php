<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'db_connection.php';

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "DB connection failed"]));
}

// Get product_id from POST or GET
$product_id = $_POST['product_id'] ?? $_GET['product_id'] ?? null;

if (!$product_id) {
    echo json_encode(["status" => "error", "message" => "Product ID required"]);
    exit;
}

$product_id = intval($conn->real_escape_string($product_id));

// Fetch variations for this product
$sql = "SELECT 
            id,
            options_json_value AS option_value,
            sku,
            price,
            stock
        FROM product_variants
        WHERE product_id = '$product_id'
        ORDER BY id ASC";

$result = $conn->query($sql);

if ($result) {
    $variations = [];
    while ($row = $result->fetch_assoc()) {
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
        "product_id" => $product_id,
        "variations" => $variations
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to fetch variations: " . $conn->error
    ]);
}

$conn->close();
?>
