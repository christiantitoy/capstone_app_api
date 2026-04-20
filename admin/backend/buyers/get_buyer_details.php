<?php
// /admin/backend/buyers/get_buyer_details.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid buyer ID'
    ]);
    exit;
}

$buyerId = (int) $_GET['id'];

try {
    // Get buyer details
    $sql = "
        SELECT 
            b.id, 
            b.username, 
            b.email, 
            b.avatar_url,
            b.created_at as buyer_since
        FROM buyers b
        WHERE b.id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$buyerId]);
    $buyer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$buyer) {
        echo json_encode([
            'success' => false,
            'message' => 'Buyer not found'
        ]);
        exit;
    }
    
    // Get buyer addresses
    $stmt = $conn->prepare("
        SELECT 
            id,
            recipient_name,
            phone_number,
            is_default,
            gps_location,
            full_address,
            created_at,
            updated_at
        FROM buyer_addresses 
        WHERE buyer_id = ?
        ORDER BY is_default DESC, created_at DESC
    ");
    $stmt->execute([$buyerId]);
    $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get order statistics
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) AS total_orders,
            SUM(total_amount) AS total_spent,
            MAX(created_at) AS last_order_date,
            SUM(CASE WHEN status IN ('pending', 'pending_payment', 'packed', 'ready_for_pickup', 'shipped', 'assigned', 'reassigned') THEN 1 ELSE 0 END) AS active_orders,
            SUM(CASE WHEN status IN ('delivered', 'complete') THEN 1 ELSE 0 END) AS completed_orders,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_orders
        FROM orders
        WHERE buyer_id = ?
    ");
    $stmt->execute([$buyerId]);
    $orderStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get recent orders
    $stmt = $conn->prepare("
        SELECT 
            id,
            total_amount,
            status,
            created_at,
            updated_at
        FROM orders
        WHERE buyer_id = ?
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$buyerId]);
    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'buyer' => $buyer,
            'addresses' => $addresses,
            'order_stats' => [
                'total_orders' => (int) $orderStats['total_orders'],
                'total_spent' => (float) $orderStats['total_spent'],
                'last_order_date' => $orderStats['last_order_date'],
                'active_orders' => (int) $orderStats['active_orders'],
                'completed_orders' => (int) $orderStats['completed_orders'],
                'cancelled_orders' => (int) $orderStats['cancelled_orders']
            ],
            'recent_orders' => $recentOrders
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