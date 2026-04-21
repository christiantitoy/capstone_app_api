<?php
// /admin/backend/riders/get_rider_details.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid rider ID'
    ]);
    exit;
}

$riderId = (int) $_GET['id'];

try {
    // Get rider details with rejection reason
    $sql = "
        SELECT 
            r.id,
            r.username,
            r.email,
            r.status,
            r.verification_status,
            r.rejection_reason,
            r.created_at
        FROM riders r
        WHERE r.id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$riderId]);
    $rider = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rider) {
        echo json_encode([
            'success' => false,
            'message' => 'Rider not found'
        ]);
        exit;
    }

    // Get rider verification documents
    $verificationSql = "
        SELECT 
            id,
            id_type,
            id_number,
            id_front_url,
            id_back_url,
            barangay_clearance_url,
            status as verification_doc_status,
            submitted_at,
            reviewed_at
        FROM rider_verifications
        WHERE rider_id = ?
        ORDER BY submitted_at DESC
        LIMIT 1
    ";
    $verificationStmt = $conn->prepare($verificationSql);
    $verificationStmt->execute([$riderId]);
    $verification = $verificationStmt->fetch(PDO::FETCH_ASSOC);

    // Get delivery statistics (only total and completed)
    $deliverySql = "
        SELECT 
            COUNT(*) as total_deliveries,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_deliveries
        FROM order_deliveries
        WHERE rider_id = ?
    ";
    $deliveryStmt = $conn->prepare($deliverySql);
    $deliveryStmt->execute([$riderId]);
    $deliveryStats = $deliveryStmt->fetch(PDO::FETCH_ASSOC);

    // Combine all data
    $rider['verification'] = $verification ?: null;
    $rider['delivery_stats'] = [
        'total_deliveries' => (int)($deliveryStats['total_deliveries'] ?? 0),
        'completed_deliveries' => (int)($deliveryStats['completed_deliveries'] ?? 0)
    ];

    echo json_encode([
        'success' => true,
        'data' => $rider
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>