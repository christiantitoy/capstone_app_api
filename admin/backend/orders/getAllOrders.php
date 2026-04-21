<?php
// /admin/backend/orders/getAllOrders.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

try {
    // Query to get all orders with buyer information and product IDs
    $sql = "SELECT 
                o.id as order_id,
                o.buyer_id,
                o.address_id,
                o.payment_method,
                o.subtotal,
                o.shipping_fee,
                o.platform_fee,
                o.total_amount,
                o.status,
                o.created_at,
                o.updated_at,
                o.locked_at,
                b.username as customer_name,
                b.email as customer_email,
                STRING_AGG(DISTINCT oi.product_id::text, ', ') as product_ids,
                COUNT(DISTINCT oi.id) as total_items
            FROM public.orders o
            LEFT JOIN public.buyers b ON o.buyer_id = b.id
            LEFT JOIN public.order_items oi ON o.id = oi.order_id
            GROUP BY o.id, b.id, b.username, b.email
            ORDER BY o.id DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM public.orders";
    $countStmt = $conn->prepare($countSql);
    $countStmt->execute();
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get status counts matching your schema's status check constraint
    $statusSql = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                    COUNT(CASE WHEN status = 'pending_payment' THEN 1 END) as pending_payment,
                    COUNT(CASE WHEN status = 'packed' THEN 1 END) as packed,
                    COUNT(CASE WHEN status = 'ready_for_pickup' THEN 1 END) as ready_for_pickup,
                    COUNT(CASE WHEN status = 'shipped' THEN 1 END) as shipped,
                    COUNT(CASE WHEN status = 'delivered' THEN 1 END) as delivered,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled,
                    COUNT(CASE WHEN status = 'complete' THEN 1 END) as complete,
                    COUNT(CASE WHEN status = 'locked' THEN 1 END) as locked,
                    COUNT(CASE WHEN status = 'assigned' THEN 1 END) as assigned,
                    COUNT(CASE WHEN status = 'reassigned' THEN 1 END) as reassigned
                  FROM public.orders";
    $statusStmt = $conn->prepare($statusSql);
    $statusStmt->execute();
    $statusCounts = $statusStmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $orders,
        'total_count' => (int)$totalCount,
        'status_counts' => [
            'total' => (int)$statusCounts['total'],
            'pending' => (int)$statusCounts['pending'],
            'pending_payment' => (int)$statusCounts['pending_payment'],
            'packed' => (int)$statusCounts['packed'],
            'ready_for_pickup' => (int)$statusCounts['ready_for_pickup'],
            'shipped' => (int)$statusCounts['shipped'],
            'delivered' => (int)$statusCounts['delivered'],
            'cancelled' => (int)$statusCounts['cancelled'],
            'complete' => (int)$statusCounts['complete'],
            'locked' => (int)$statusCounts['locked'],
            'assigned' => (int)$statusCounts['assigned'],
            'reassigned' => (int)$statusCounts['reassigned']
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