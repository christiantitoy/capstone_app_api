<?php
// /admin/backend/buyers/get_all_buyers.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

try {
    // Get total buyers count
    $stmt = $conn->prepare("SELECT COUNT(*) AS total_buyers FROM buyers");
    $stmt->execute();
    $totalBuyers = $stmt->fetch(PDO::FETCH_ASSOC)['total_buyers'];

    // Get active buyers count (buyers who have placed at least one order)
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT buyer_id) AS active_buyers 
        FROM orders
    ");
    $stmt->execute();
    $activeBuyers = $stmt->fetch(PDO::FETCH_ASSOC)['active_buyers'];

    // Query to get all buyers with their order count
    $sql = "
        SELECT 
            b.id, 
            b.username, 
            b.email, 
            b.avatar_url,
            COUNT(DISTINCT o.id) AS order_count,
            MAX(o.created_at) AS last_order_date,
            SUM(CASE WHEN o.status IN ('pending', 'pending_payment', 'packed', 'ready_for_pickup', 'shipped', 'assigned', 'reassigned') THEN 1 ELSE 0 END) AS active_orders_count,
            SUM(CASE WHEN o.status IN ('delivered', 'complete') THEN 1 ELSE 0 END) AS completed_orders_count,
            SUM(o.total_amount) AS total_spent
        FROM buyers b
        LEFT JOIN orders o ON b.id = o.buyer_id
        GROUP BY b.id, b.username, b.email, b.avatar_url
        ORDER BY b.id DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $buyers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'statistics' => [
            'total_buyers' => (int) $totalBuyers,
            'active_buyers' => (int) $activeBuyers
        ],
        'data' => $buyers
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} finally {
    $conn = null; // Close the database connection
}
?>