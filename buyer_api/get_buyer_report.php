<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '/var/www/html/connection/db_connection.php';

try {
    // ────────────────────────────────────────────────────────────────
    // Get inputs
    // ────────────────────────────────────────────────────────────────
    $order_id  = $_GET['order_id'] ?? null;
    $buyer_id  = $_GET['buyer_id'] ?? null;

    // Both parameters are required
    if (!$order_id || !is_numeric($order_id)) {
        http_response_code(400);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Valid order_id (integer) is required'
        ]);
        exit;
    }

    if (!$buyer_id || !is_numeric($buyer_id)) {
        http_response_code(400);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Valid buyer_id (integer) is required'
        ]);
        exit;
    }

    $order_id = (int)$order_id;
    $buyer_id = (int)$buyer_id;

    // ────────────────────────────────────────────────────────────────
    // First, get delivery_id from order and verify ownership
    // ────────────────────────────────────────────────────────────────
    $verifySql = "SELECT od.id as delivery_id
                  FROM order_deliveries od
                  JOIN orders o ON od.order_id = o.id
                  WHERE od.order_id = :order_id 
                  AND o.buyer_id = :buyer_id
                  LIMIT 1";

    $verifyStmt = $conn->prepare($verifySql);
    $verifyStmt->execute([
        ':order_id' => $order_id,
        ':buyer_id' => $buyer_id
    ]);

    $delivery = $verifyStmt->fetch(PDO::FETCH_ASSOC);

    if (!$delivery) {
        http_response_code(404);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Order not found or does not belong to this buyer'
        ]);
        exit;
    }

    $delivery_id = (int)$delivery['delivery_id'];

    // ────────────────────────────────────────────────────────────────
    // Query reports using delivery_id and buyer_id
    // ────────────────────────────────────────────────────────────────
    $sql = "SELECT 
                id,
                delivery_id,
                buyer_id,
                issue_type,
                status,
                created_at,
                updated_at
            FROM buyer_reports
            WHERE delivery_id = :delivery_id
            AND buyer_id = :buyer_id
            ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':delivery_id' => $delivery_id,
        ':buyer_id'    => $buyer_id
    ]);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ────────────────────────────────────────────────────────────────
    // Format response
    // ────────────────────────────────────────────────────────────────
    $formattedReports = [];
    foreach ($reports as $row) {
        $formattedReports[] = [
            'id'          => (int)$row['id'],
            'delivery_id' => (int)$row['delivery_id'],
            'buyer_id'    => (int)$row['buyer_id'],
            'issue_type'  => $row['issue_type'],
            'status'      => $row['status'],
            'created_at'  => $row['created_at'],
            'updated_at'  => $row['updated_at']
        ];
    }

    echo json_encode([
        'status'   => 'success',
        'order_id' => $order_id,        // Added for reference
        'reports'  => $formattedReports,
        'count'    => count($formattedReports)
    ], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}
?>