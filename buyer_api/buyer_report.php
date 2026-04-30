<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once '/var/www/html/connection/db_connection.php';

try {
    // ───────────────────────────────────────────────
    // Get inputs (supports both form-data & JSON)
    // ───────────────────────────────────────────────
    $data = json_decode(file_get_contents("php://input"), true);

    $order_id    = $data['order_id']    ?? $_POST['order_id'] ?? null;
    $buyer_id    = $data['buyer_id']    ?? $_POST['buyer_id'] ?? null;
    $issue_type  = $data['issue_type']  ?? $_POST['issue_type'] ?? null;

    // ───────────────────────────────────────────────
    // Validate inputs
    // ───────────────────────────────────────────────
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

    if (!$issue_type || trim($issue_type) === '') {
        http_response_code(400);
        echo json_encode([
            'status'  => 'error',
            'message' => 'issue_type is required'
        ]);
        exit;
    }

    $order_id   = (int)$order_id;
    $buyer_id   = (int)$buyer_id;
    $issue_type = trim($issue_type);

    // ───────────────────────────────────────────────
    // Get delivery info and verify ownership
    // ───────────────────────────────────────────────
    $verifySql = "SELECT od.id as delivery_id, od.status as delivery_status
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

    // Optional: Check if delivery is in a reportable state
    // Uncomment if you want to restrict based on delivery status
    /*
    $allowed_statuses = ['completed', 'picked_up', 'delivering'];
    if (!in_array($delivery['delivery_status'], $allowed_statuses)) {
        http_response_code(400);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Cannot report a delivery with status: ' . $delivery['delivery_status']
        ]);
        exit;
    }
    */

    // ───────────────────────────────────────────────
    // DUPLICATE CHECK (delivery_id + buyer_id)
    // ───────────────────────────────────────────────
    $dupSql = "SELECT id FROM buyer_reports 
               WHERE delivery_id = :delivery_id 
               AND buyer_id = :buyer_id";

    $dupStmt = $conn->prepare($dupSql);
    $dupStmt->execute([
        ':delivery_id' => $delivery_id,
        ':buyer_id'    => $buyer_id
    ]);

    if ($dupStmt->fetch()) {
        http_response_code(409);
        echo json_encode([
            'status'  => 'error',
            'message' => 'You have already submitted a report for this order'
        ]);
        exit;
    }

    // ───────────────────────────────────────────────
    // Insert report
    // ───────────────────────────────────────────────

    // ⚠️ If PostgreSQL (RETURNING works)
    $insertSql = "INSERT INTO buyer_reports (delivery_id, buyer_id, issue_type)
                  VALUES (:delivery_id, :buyer_id, :issue_type)
                  RETURNING id, created_at";

    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->execute([
        ':delivery_id' => $delivery_id,
        ':buyer_id'    => $buyer_id,
        ':issue_type'  => $issue_type
    ]);

    $report = $insertStmt->fetch(PDO::FETCH_ASSOC);

    // ───────────────────────────────────────────────
    // Success response
    // ───────────────────────────────────────────────
    http_response_code(201);
    echo json_encode([
        'status'  => 'success',
        'message' => 'Report submitted successfully',
        'report'  => [
            'id'          => (int)$report['id'],
            'delivery_id' => $delivery_id,
            'order_id'    => $order_id,
            'buyer_id'    => $buyer_id,
            'issue_type'  => $issue_type,
            'status'      => 'pending',
            'created_at'  => $report['created_at']
        ]
    ], JSON_PRETTY_PRINT);

} catch (PDOException $e) {

    // Handle UNIQUE constraint (extra safety)
    if ($e->getCode() == '23505') { // PostgreSQL duplicate error
        http_response_code(409);
        echo json_encode([
            'status'  => 'error',
            'message' => 'You have already submitted a report for this order'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Database error occurred',
            'debug'   => $e->getMessage()
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}
?>