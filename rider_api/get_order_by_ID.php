<?php
header('Content-Type: application/json');

require_once '/var/www/html/connection/db_connection.php';

try {
    // Check if order_id is provided
    if (!isset($_GET['order_id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Order ID is required'
        ]);
        exit;
    }

    $order_id = intval($_GET['order_id']);

    // Fetch order details with required fields
    $sql = "SELECT
                o.id AS order_id,
                o.status,
                ba.recipient_name,
                ba.full_address,
                s.plus_code,
                s.store_name,
                COUNT(oi.id) AS item_count,
                s.id AS store_id
            FROM orders o
            INNER JOIN buyer_addresses ba ON o.address_id = ba.id
            INNER JOIN order_items oi ON o.id = oi.order_id
            INNER JOIN items i ON oi.product_id = i.id
            INNER JOIN stores s ON i.seller_id = s.seller_id
            WHERE o.id = :order_id
            GROUP BY o.id, o.status, ba.recipient_name, ba.full_address, 
                     s.plus_code, s.store_name, s.id
            ORDER BY s.store_name";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $stmt->execute();

    $orderDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if order exists
    if (empty($orderDetails)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Order not found or has no items'
        ]);
        exit;
    }

    // Format the response
    $formattedResponse = [
        'order_id' => (int)$orderDetails[0]['order_id'],
        'status' => $orderDetails[0]['status'],
        'recipient_name' => $orderDetails[0]['recipient_name'],
        'full_address' => $orderDetails[0]['full_address'],
        'stores' => []
    ];

    // Group items by store
    foreach ($orderDetails as $row) {
        $formattedResponse['stores'][] = [
            'store_id' => (int)$row['store_id'],
            'store_name' => $row['store_name'],
            'plus_code' => $row['plus_code'],
            'item_count' => (int)$row['item_count']
        ];
    }

    // Calculate total items across all stores
    $totalItems = array_sum(array_column($formattedResponse['stores'], 'item_count'));
    $formattedResponse['total_item_count'] = $totalItems;
    $formattedResponse['store_count'] = count($formattedResponse['stores']);

    echo json_encode([
        'status' => 'success',
        'data' => $formattedResponse
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