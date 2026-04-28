<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once '/var/www/html/connection/db_connection.php';

try {
    // ────────────────────────────────────────────────────────────────
    // Get inputs (POST only for data creation)
    // ────────────────────────────────────────────────────────────────
    $delivery_id = $_POST['delivery_id'] ?? null;
    $buyer_id    = $_POST['buyer_id']    ?? null;
    $issue_type  = $_POST['issue_type']  ?? null;

    // Validate required fields
    if (!$delivery_id || !is_numeric($delivery_id)) {
        http_response_code(400);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Valid delivery_id (integer) is required'
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

    $delivery_id = (int)$delivery_id;
    $buyer_id    = (int)$buyer_id;
    $issue_type  = trim($issue_type);

    // ────────────────────────────────────────────────────────────────
    // Verify the delivery belongs to this buyer's order
    // ────────────────────────────────────────────────────────────────
    $verifySql = "SELECT od.id, od.order_id, od.status
                  FROM order_deliveries od
                  JOIN orders o ON od.order_id = o.id
                  WHERE od.id = :delivery_id 
                  AND o.buyer_id = :buyer_id";
    $verifyStmt = $conn->prepare($verifySql);
    $verifyStmt->execute([
        ':delivery_id' => $delivery_id,
        ':buyer_id'    => $buyer_id
    ]);
    $delivery = $verifyStmt->fetch(PDO::FETCH_ASSOC);

    if (!$delivery) {
        http_response_code(404);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Delivery not found or does not belong to this buyer'
        ]);
        exit;
    }

    // ────────────────────────────────────────────────────────────────
    // Check for duplicate (same buyer, same delivery, same issue)
    // ────────────────────────────────────────────────────────────────
    $dupSql = "SELECT id FROM buyer_reports 
               WHERE delivery_id = :delivery_id 
               AND buyer_id = :buyer_id 
               AND issue_type = :issue_type
               AND status = 'pending'";
    $dupStmt = $conn->prepare($dupSql);
    $dupStmt->execute([
        ':delivery_id' => $delivery_id,
        ':buyer_id'    => $buyer_id,
        ':issue_type'  => $issue_type
    ]);

    if ($dupStmt->fetch()) {
        http_response_code(409);
        echo json_encode([
            'status'  => 'error',
            'message' => 'A pending report with this issue already exists for this delivery'
        ]);
        exit;
    }

    // ────────────────────────────────────────────────────────────────
    // Insert the report
    // ────────────────────────────────────────────────────────────────
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

    // ────────────────────────────────────────────────────────────────
    // Success response
    // ────────────────────────────────────────────────────────────────
    http_response_code(201);
    echo json_encode([
        'status'  => 'success',
        'message' => 'Report submitted successfully',
        'report'  => [
            'id'          => (int)$report['id'],
            'delivery_id' => $delivery_id,
            'buyer_id'    => $buyer_id,
            'issue_type'  => $issue_type,
            'status'      => 'pending',
            'created_at'  => $report['created_at']
        ]
    ], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Database error occurred',
        'debug'   => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}
?>