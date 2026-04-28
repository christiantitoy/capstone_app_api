<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once '/var/www/html/connection/db_connection.php';

try {
    // ────────────────────────────────────────────────────────────────
    // Get inputs (POST only for data creation)
    // ────────────────────────────────────────────────────────────────
    $order_id  = $_POST['order_id']  ?? null;
    $buyer_id  = $_POST['buyer_id']  ?? null;
    $issue_type = $_POST['issue_type'] ?? null;

    // Validate required fields
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

    // ────────────────────────────────────────────────────────────────
    // Verify the order belongs to this buyer
    // ────────────────────────────────────────────────────────────────
    $verifySql = "SELECT id, status FROM orders WHERE id = :order_id AND buyer_id = :buyer_id";
    $verifyStmt = $conn->prepare($verifySql);
    $verifyStmt->execute([
        ':order_id' => $order_id,
        ':buyer_id' => $buyer_id
    ]);
    $order = $verifyStmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        http_response_code(404);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Order not found or does not belong to this buyer'
        ]);
        exit;
    }

    // ────────────────────────────────────────────────────────────────
    // Check for duplicate (same buyer, same order, same issue)
    // ────────────────────────────────────────────────────────────────
    $dupSql = "SELECT id FROM buyer_reports 
               WHERE order_id = :order_id 
               AND buyer_id = :buyer_id 
               AND issue_type = :issue_type
               AND status = 'pending'";
    $dupStmt = $conn->prepare($dupSql);
    $dupStmt->execute([
        ':order_id'   => $order_id,
        ':buyer_id'   => $buyer_id,
        ':issue_type' => $issue_type
    ]);

    if ($dupStmt->fetch()) {
        http_response_code(409);
        echo json_encode([
            'status'  => 'error',
            'message' => 'A pending report with this issue already exists for this order'
        ]);
        exit;
    }

    // ────────────────────────────────────────────────────────────────
    // Insert the report
    // ────────────────────────────────────────────────────────────────
    $insertSql = "INSERT INTO buyer_reports (order_id, buyer_id, issue_type)
                  VALUES (:order_id, :buyer_id, :issue_type)
                  RETURNING id, created_at";

    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->execute([
        ':order_id'   => $order_id,
        ':buyer_id'   => $buyer_id,
        ':issue_type' => $issue_type
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
            'id'         => (int)$report['id'],
            'order_id'   => $order_id,
            'buyer_id'   => $buyer_id,
            'issue_type' => $issue_type,
            'status'     => 'pending',
            'created_at' => $report['created_at']
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