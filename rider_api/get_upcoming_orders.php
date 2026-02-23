<?php
header('Content-Type: application/json');
require 'db_connection.php';

try {
    if (!isset($_GET['rider_id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Rider ID is required'
        ]);
        exit;
    }

    $rider_id = intval($_GET['rider_id']);

    // 1️⃣ First, revert expired locked orders to shipped
    $updateSql = "UPDATE orders 
                  SET status = 'shipped'
                  WHERE status = 'locked' 
                  AND locked_at < (NOW() - INTERVAL 30 SECOND)";
    $conn->query($updateSql);

    // 2️⃣ Fetch all orders with status = 'shipped' (now includes previously locked but expired)
    $sql = "SELECT 
                o.id,
                o.buyer_id,
                o.address_id,
                o.payment_method,
                o.subtotal,
                o.shipping_fee,
                o.discount,
                o.total_amount,
                o.status,
                o.created_at,
                ba.recipient_name,
                ba.phone_number,
                ba.barangay,
                ba.street_address
            FROM orders o
            LEFT JOIN buyer_addresses ba ON o.address_id = ba.id
            WHERE o.status = 'shipped'
            ORDER BY o.created_at ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $result = $stmt->get_result();
    $orders = [];

    while ($row = $result->fetch_assoc()) {
        $orders[] = [
            'id' => (int)$row['id'],
            'buyerId' => (int)$row['buyer_id'],
            'addressId' => (int)$row['address_id'],
            'paymentMethod' => $row['payment_method'],
            'subtotal' => (float)$row['subtotal'],
            'shippingFee' => (float)$row['shipping_fee'],
            'discount' => (float)$row['discount'],
            'totalAmount' => (float)$row['total_amount'],
            'status' => $row['status'],
            'createdAt' => $row['created_at'],
            'recipientName' => $row['recipient_name'],
            'phoneNumber' => $row['phone_number'],
            'barangay' => $row['barangay'],
            'streetAddress' => $row['street_address']
        ];
    }

    echo json_encode([
        'status' => 'success',
        'orders' => $orders,
        'count' => count($orders)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
