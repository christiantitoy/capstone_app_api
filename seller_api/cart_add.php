<?php
header('Content-Type: application/json');
require 'db_connection.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid JSON data'
        ]);
        exit;
    }

    // Validate required fields
    $required_fields = ['buyer_id', 'product_id', 'quantity', 'unit_price'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            echo json_encode([
                'status' => 'error',
                'message' => "Missing required field: $field"
            ]);
            exit;
        }
    }

    $buyer_id = intval($data['buyer_id']);
    $product_id = intval($data['product_id']);
    $variation_id = isset($data['variation_id']) ? intval($data['variation_id']) : null;
    $selected_options = isset($data['selected_options']) ? $data['selected_options'] : '';
    $quantity = intval($data['quantity']);
    $unit_price = floatval($data['unit_price']);

    // ✅ ALWAYS INSERT NEW ROW
    $insert_sql = "
        INSERT INTO cart_items
        (buyer_id, product_id, variation_id, selected_options, quantity, unit_price)
        VALUES (?, ?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param(
        "iiisid",
        $buyer_id,
        $product_id,
        $variation_id,
        $selected_options,
        $quantity,
        $unit_price
    );

    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Item added to cart',
            'cart_item_id' => $conn->insert_id
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to add item to cart'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
