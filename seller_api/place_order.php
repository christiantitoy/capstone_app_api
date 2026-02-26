<?php
header('Content-Type: application/json');

require_once '/var/www/html/connection/db_connection.php';

try {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid JSON payload"
        ]);
        exit;
    }

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

    $conn->beginTransaction();

    // ✅ Insert Order
    $orderSql = "
        INSERT INTO orders (
            buyer_id, address_id, payment_method,
            subtotal, shipping_fee, discount, total_amount
        ) VALUES (
            :buyer_id, :address_id, :payment_method,
            :subtotal, :shipping_fee, :discount, :total_amount
        )
    ";

    $stmt = $conn->prepare($orderSql);
    $stmt->execute([
        ':buyer_id' => $data['buyer_id'],
        ':address_id' => $data['address_id'],
        ':payment_method' => $data['payment_method'],
        ':subtotal' => $data['subtotal'],
        ':shipping_fee' => $data['shipping_fee'],
        ':discount' => $data['discount'],
        ':total_amount' => $data['total_amount']
    ]);

    $orderId = $conn->lastInsertId();

    // ✅ Insert Order Items
    $itemSql = "
        INSERT INTO order_items (
            order_id,
            product_id,
            variation_id,
            selected_options,
            quantity,
            unit_price,
            total_price
        ) VALUES (
            :order_id,
            :product_id,
            :variation_id,
            :selected_options,
            :quantity,
            :unit_price,
            :total_price
        )
    ";

    $itemStmt = $conn->prepare($itemSql);

    foreach ($data['items'] as $item) {

        $totalPrice = $item['unit_price'] * $item['quantity'];

        $itemStmt->execute([
            ':order_id' => $orderId,
            ':product_id' => $item['product_id'],
            ':variation_id' => $item['variation_id'],
            ':selected_options' => $item['selected_options'],
            ':quantity' => $item['quantity'],
            ':unit_price' => $item['unit_price'],
            ':total_price' => $totalPrice
        ]);

        // ✅ SIMPLE POSTGRES FIX HERE
        $updateSql = "
            UPDATE cart_items
            SET is_purchased = 1
            WHERE buyer_id = :buyer_id
              AND product_id = :product_id
              AND variation_id IS NOT DISTINCT FROM :variation_id
              AND is_purchased = 0
        ";

        $updateStmt = $conn->prepare($updateSql);

        $updateStmt->execute([
            ':buyer_id' => $data['buyer_id'],
            ':product_id' => $item['product_id'],
            ':variation_id' => $item['variation_id']
        ]);
    }

    $conn->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Order placed successfully",
        "order_id" => $orderId
    ]);

} catch (PDOException $e) {

    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);

} catch (Exception $e) {

    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    echo json_encode([
        "status" => "error",
        "message" => "Failed to place order",
        "error" => $e->getMessage()
    ]);
}

$conn = null;
?>