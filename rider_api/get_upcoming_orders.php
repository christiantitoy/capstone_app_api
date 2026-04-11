<?php
header('Content-Type: application/json');

require_once '/var/www/html/connection/db_connection.php';

try {
    // Check if rider_id is provided
    if (!isset($_GET['rider_id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Rider ID is required'
        ]);
        exit;
    }

    $rider_id = intval($_GET['rider_id']);

    // 1️⃣ Revert expired locked orders to shipped
    $updateSql = "UPDATE orders
                  SET status = 'shipped'
                  WHERE status = 'locked'
                  AND locked_at < NOW() - INTERVAL '30 seconds'";
    $conn->exec($updateSql);

    // 2️⃣ Fetch all shipped orders, excluding ones cancelled by this rider
    $sql = "SELECT
                o.id,
                o.buyer_id,
                o.address_id,
                o.payment_method,
                o.subtotal,
                o.shipping_fee,
                o.platform_fee,
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
            AND NOT EXISTS (
                SELECT 1
                FROM order_deliveries od
                WHERE od.order_id = o.id
                AND od.rider_id = :rider_id
                AND od.status = 'cancelled'
            )
            ORDER BY o.created_at ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':rider_id', $rider_id, PDO::PARAM_INT);
    $stmt->execute();

    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the orders array
    $formattedOrders = [];
    foreach ($orders as $row) {
        $formattedOrders[] = [
            'id' => (int)$row['id'],
            'buyerId' => (int)$row['buyer_id'],
            'addressId' => (int)$row['address_id'],
            'paymentMethod' => $row['payment_method'],
            'subtotal' => (float)$row['subtotal'],
            'shippingFee' => (float)$row['shipping_fee'],
            'discount' => (float)$row['platform_fee'], // Changed: Map platform_fee to discount
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
        'orders' => $formattedOrders,
        'count' => count($formattedOrders)
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>