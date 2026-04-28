<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '/var/www/html/connection/db_connection.php';

try {
    // ────────────────────────────────────────────────────────────────
    // Get inputs
    // ────────────────────────────────────────────────────────────────
    $delivery_id = $_GET['delivery_id'] ?? null;
    $buyer_id    = $_GET['buyer_id']    ?? null;

    // Both parameters are required
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

    $delivery_id = (int)$delivery_id;
    $buyer_id    = (int)$buyer_id;

    // ────────────────────────────────────────────────────────────────
    // Query — both filters required
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
        'status'  => 'success',
        'reports' => $formattedReports,
        'count'   => count($formattedReports)
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