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

    // Get delivery statistics
    $deliverySql = "
        SELECT 
            COUNT(*) as total_deliveries,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_deliveries,
            SUM(CASE WHEN status = 'delivering' THEN 1 ELSE 0 END) as active_deliveries,
            SUM(CASE WHEN status = 'assigned' THEN 1 ELSE 0 END) as assigned_deliveries,
            SUM(CASE WHEN status = 'picked_up' THEN 1 ELSE 0 END) as picked_up_deliveries,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_deliveries,
            SUM(CASE WHEN status = 'abandoned' THEN 1 ELSE 0 END) as abandoned_deliveries,
            AVG(CASE WHEN completed_at IS NOT NULL 
                THEN EXTRACT(EPOCH FROM (completed_at - assigned_at)) / 60 
                ELSE NULL END) as avg_delivery_time_minutes
        FROM order_deliveries
        WHERE rider_id = ?
    ";
    $deliveryStmt = $conn->prepare($deliverySql);
    $deliveryStmt->execute([$riderId]);
    $deliveryStats = $deliveryStmt->fetch(PDO::FETCH_ASSOC);

    // Get recent deliveries (last 10)
    $recentDeliveriesSql = "
        SELECT 
            od.id,
            od.order_id,
            od.status,
            od.assigned_at,
            od.picked_up_at,
            od.completed_at,
            o.total_amount,
            o.delivery_fee
        FROM order_deliveries od
        LEFT JOIN orders o ON od.order_id = o.id
        WHERE od.rider_id = ?
        ORDER BY od.created_at DESC
        LIMIT 10
    ";
    $recentStmt = $conn->prepare($recentDeliveriesSql);
    $recentStmt->execute([$riderId]);
    $recentDeliveries = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get rating statistics (if you have a ratings table)
    $ratingSql = "
        SELECT 
            AVG(rating) as average_rating,
            COUNT(*) as total_ratings
        FROM rider_ratings
        WHERE rider_id = ?
    ";
    $ratingStmt = $conn->prepare($ratingSql);
    $ratingStmt->execute([$riderId]);
    $ratingStats = $ratingStmt->fetch(PDO::FETCH_ASSOC);

    // Combine all data
    $rider['delivery_stats'] = [
        'total_deliveries' => (int)($deliveryStats['total_deliveries'] ?? 0),
        'completed_deliveries' => (int)($deliveryStats['completed_deliveries'] ?? 0),
        'active_deliveries' => (int)($deliveryStats['active_deliveries'] ?? 0),
        'assigned_deliveries' => (int)($deliveryStats['assigned_deliveries'] ?? 0),
        'picked_up_deliveries' => (int)($deliveryStats['picked_up_deliveries'] ?? 0),
        'cancelled_deliveries' => (int)($deliveryStats['cancelled_deliveries'] ?? 0),
        'abandoned_deliveries' => (int)($deliveryStats['abandoned_deliveries'] ?? 0),
        'avg_delivery_time_minutes' => round($deliveryStats['avg_delivery_time_minutes'] ?? 0, 2)
    ];
    
    $rider['rating_stats'] = [
        'average_rating' => round($ratingStats['average_rating'] ?? 0, 1),
        'total_ratings' => (int)($ratingStats['total_ratings'] ?? 0)
    ];
    
    $rider['recent_deliveries'] = $recentDeliveries;

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