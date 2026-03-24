<?php
// /seller/backend/dashboard_backends/get_recent_orders.php

require_once '/var/www/html/connection/db_connection.php';

$seller_id = $_GET['seller_id'] ?? $_POST['seller_id'] ?? null;

if (empty($seller_id) || !is_numeric($seller_id)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'orders' => []
    ]);
    exit;
}

try {
    // Get 5 most recent orders for the seller's products
    $stmt = $conn->prepare("
        SELECT 
            o.id as order_id,
            o.total_amount,
            o.status,
            b.username as customer_name,
            STRING_AGG(i.product_name, ', ') as products
        FROM orders o
        INNER JOIN order_items oi ON o.id = oi.order_id
        INNER JOIN items i ON oi.product_id = i.id
        LEFT JOIN buyers b ON o.buyer_id = b.id
        WHERE i.seller_id = ?
        GROUP BY o.id, o.total_amount, o.status, b.username
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$seller_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'orders' => $orders
    ]);
    exit;
    
} catch (PDOException $e) {
    error_log("Recent orders error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'orders' => []
    ]);
    exit;
}
?>