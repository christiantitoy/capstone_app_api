<?php
// /admin/backend/deliveries/getAllDeliveries.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

try {
    // Query to get all deliveries with order, rider, and buyer information
    $sql = "SELECT 
                od.id as delivery_id,
                od.order_id,
                od.rider_id,
                od.status as delivery_status,
                od.assigned_at,
                od.picked_up_at,
                od.completed_at,
                od.abandoned_at,
                od.cancelled_at,
                od.created_at,
                od.updated_at,
                o.total_amount as order_total,
                o.status as order_status,
                o.payment_method,
                r.username as rider_name,
                r.email as rider_email,
                r.status as rider_status,
                b.username as buyer_name,
                b.email as buyer_email
            FROM public.order_deliveries od
            LEFT JOIN public.orders o ON od.order_id = o.id
            LEFT JOIN public.riders r ON od.rider_id = r.id
            LEFT JOIN public.buyers b ON o.buyer_id = b.id
            ORDER BY od.id DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM public.order_deliveries";
    $countStmt = $conn->prepare($countSql);
    $countStmt->execute();
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get status counts
    $statusSql = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'assigned' THEN 1 END) as assigned,
                    COUNT(CASE WHEN status = 'picked_up' THEN 1 END) as picked_up,
                    COUNT(CASE WHEN status = 'delivering' THEN 1 END) as delivering,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                    COUNT(CASE WHEN status = 'abandoned' THEN 1 END) as abandoned,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled
                  FROM public.order_deliveries";
    $statusStmt = $conn->prepare($statusSql);
    $statusStmt->execute();
    $statusCounts = $statusStmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $deliveries,
        'total_count' => (int)$totalCount,
        'status_counts' => [
            'total' => (int)$statusCounts['total'],
            'assigned' => (int)$statusCounts['assigned'],
            'picked_up' => (int)$statusCounts['picked_up'],
            'delivering' => (int)$statusCounts['delivering'],
            'completed' => (int)$statusCounts['completed'],
            'abandoned' => (int)$statusCounts['abandoned'],
            'cancelled' => (int)$statusCounts['cancelled']
        ]
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