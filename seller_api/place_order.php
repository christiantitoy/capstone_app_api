<?php
header('Content-Type: application/json');
require 'db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid JSON payload"
    ]);
    exit;
}

// Validate required fields
$required = [
    'buyer_id',
    'address_id',
    'payment_method',
    'subtotal',
    'shipping_fee',
    'discount',
    'total_amount',
    'items'
];

foreach ($required as $field) {
    if (!isset($data[$field])) {
        echo json_encode([
            "status" => "error",
            "message" => "Missing field: $field"
        ]);
        exit;
    }
}

if (empty($data['items'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Order must contain at least one item"
    ]);
    exit;
}

try {
    $conn->begin_transaction();

    // 1️⃣ Insert order
    $orderSql = "
        INSERT INTO orders (
            buyer_id, address_id, payment_method,
            subtotal, shipping_fee, discount, total_amount
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($orderSql);
    $stmt->bind_param(
        "iisdddd",
        $data['buyer_id'],
        $data['address_id'],
        $data['payment_method'],
        $data['subtotal'],
        $data['shipping_fee'],
        $data['discount'],
        $data['total_amount']
    );

    $stmt->execute();
    $orderId = $stmt->insert_id;
    $stmt->close();

    // 2️⃣ Insert order items
    $itemSql = "
        INSERT INTO order_items (
            order_id,
            product_id,
            variation_id,
            selected_options,
            quantity,
            unit_price,
            total_price
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ";

    $itemStmt = $conn->prepare($itemSql);

    foreach ($data['items'] as $item) {
        $totalPrice = $item['unit_price'] * $item['quantity'];

        $itemStmt->bind_param(
            "iiisidd",
            $orderId,
            $item['product_id'],
            $item['variation_id'],
            $item['selected_options'],
            $item['quantity'],
            $item['unit_price'],
            $totalPrice
        );

        $itemStmt->execute();

        // 3️⃣ Mark cart items as purchased
        // Find cart items for this buyer + product + variation that are not yet purchased
        $updateSql = "
            UPDATE cart_items
            SET is_purchased = 1
            WHERE buyer_id = ?
              AND product_id = ?
              AND (variation_id = ? OR (variation_id IS NULL AND ? IS NULL))
              AND is_purchased = 0
        ";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param(
            "iiii",
            $data['buyer_id'],
            $item['product_id'],
            $item['variation_id'],
            $item['variation_id']
        );
        $updateStmt->execute();
        $updateStmt->close();
    }

    $itemStmt->close();

    $conn->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Order placed successfully",
        "order_id" => $orderId
    ]);

} catch (Exception $e) {
    $conn->rollback();

    echo json_encode([
        "status" => "error",
        "message" => "Failed to place order",
        "error" => $e->getMessage()
    ]);
}
?>
