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

    // ✅ Required fields - Changed 'discount' to 'platform_fee'
    $required = [
        'buyer_id',
        'address_id',
        'payment_method',
        'subtotal',
        'shipping_fee',
        'platform_fee',     // ✅ Changed from 'discount'
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

    // ✅ Generate 8-character numeric OTP
    $deliveryOtp = str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);

    // ✅ INSERT ORDER - Changed column from 'discount' to 'platform_fee'
    $orderSql = "
        INSERT INTO orders (
            buyer_id,
            address_id,
            payment_method,
            subtotal,
            shipping_fee,
            platform_fee,      -- ✅ Changed from discount
            total_amount,
            delivery_otp,      -- ✅ Added delivery_otp
            status,
            created_at,
            updated_at
        )
        VALUES (
            :buyer_id,
            :address_id,
            :payment_method,
            :subtotal,
            :shipping_fee,
            :platform_fee,      -- ✅ Changed from discount
            :total_amount,
            :delivery_otp,      -- ✅ Added delivery_otp
            'pending',
            CURRENT_TIMESTAMP,
            CURRENT_TIMESTAMP
        )
    ";

    $stmt = $conn->prepare($orderSql);

    $stmt->execute([
        ':buyer_id' => $data['buyer_id'],
        ':address_id' => $data['address_id'],
        ':payment_method' => $data['payment_method'],
        ':subtotal' => $data['subtotal'],
        ':shipping_fee' => $data['shipping_fee'],
        ':platform_fee' => $data['platform_fee'],      // ✅ Changed from discount
        ':total_amount' => $data['total_amount'],
        ':delivery_otp' => $deliveryOtp                 // ✅ Added delivery_otp
    ]);

    $orderId = $conn->lastInsertId();

    // ✅ INSERT ORDER ITEMS
    $itemSql = "
        INSERT INTO order_items (
            order_id,
            product_id,
            variation_id,
            selected_options,
            quantity,
            unit_price,
            total_price
        )
        VALUES (
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

        // ✅ Validate required fields for each item
        if (!isset($item['product_id']) || !isset($item['quantity']) || !isset($item['unit_price'])) {
            throw new Exception("Missing required fields in order item");
        }

        // ✅ SIMPLE NULL FIX
        $variationId = isset($item['variation_id']) &&
                       $item['variation_id'] !== '' &&
                       $item['variation_id'] !== null
                       ? intval($item['variation_id'])
                       : null;

        $selectedOptions = isset($item['selected_options']) && !empty($item['selected_options'])
            ? $item['selected_options']
            : null;

        $quantity = intval($item['quantity']);
        $unitPrice = floatval($item['unit_price']);
        $totalPrice = $unitPrice * $quantity;

        // ✅ Insert item
        $itemStmt->execute([
            ':order_id' => $orderId,
            ':product_id' => intval($item['product_id']),
            ':variation_id' => $variationId,
            ':selected_options' => $selectedOptions,
            ':quantity' => $quantity,
            ':unit_price' => $unitPrice,
            ':total_price' => $totalPrice
        ]);

        // ✅ Check if cart_items table exists and update is_purchased
        // This part assumes you have a cart_items table with is_purchased column
        try {
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
                ':product_id' => intval($item['product_id']),
                ':variation_id' => $variationId
            ]);
        } catch (PDOException $e) {
            // Log error but don't fail the order if cart update fails
            error_log("Failed to update cart_items: " . $e->getMessage());
        }
    }

    $conn->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Order placed successfully",
        "order_id" => $orderId,
        "delivery_otp" => $deliveryOtp   // ✅ Return OTP in response
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
        "message" => $e->getMessage()
    ]);
}

$conn = null;
?>