<?php
// /seller/backend/dashboard_backends/count_products.php (enhanced with orders count)

require_once '/var/www/html/connection/db_connection.php';

// Get seller_id from request
$seller_id = $_GET['seller_id'] ?? $_POST['seller_id'] ?? null;

if (empty($seller_id) || !is_numeric($seller_id)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Invalid seller_id provided',
        'count' => 0,
        'orders_count' => 0
    ]);
    exit;
}

try {
    // Count all products for the seller (including all statuses)
    $stmt = $conn->prepare("SELECT COUNT(*) as total_count FROM items WHERE seller_id = ?");
    $stmt->execute([$seller_id]);
    $product_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $product_count = (int)($product_result['total_count'] ?? 0);
    
    // Count orders for the seller's products
    // This counts unique orders that contain at least one product from this seller
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT o.id) as total_orders
        FROM orders o
        INNER JOIN order_items oi ON o.id = oi.order_id
        INNER JOIN items i ON oi.product_id = i.id
        WHERE i.seller_id = ?
    ");
    $stmt->execute([$seller_id]);
    $order_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $orders_count = (int)($order_result['total_orders'] ?? 0);
    
    // Optional: Get detailed order statistics by status
    $stmt = $conn->prepare("
        SELECT 
            o.status,
            COUNT(DISTINCT o.id) as order_count
        FROM orders o
        INNER JOIN order_items oi ON o.id = oi.order_id
        INNER JOIN items i ON oi.product_id = i.id
        WHERE i.seller_id = ?
        GROUP BY o.status
    ");
    $stmt->execute([$seller_id]);
    $order_status_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $order_status_summary = [];
    foreach ($order_status_counts as $status_count) {
        $order_status_summary[$status_count['status']] = (int)$status_count['order_count'];
    }
    
    // Return JSON response with both counts
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'seller_id' => (int)$seller_id,
        'products_count' => $product_count,
        'orders_count' => $orders_count,
        'order_status_summary' => $order_status_summary,
        'message' => 'Counts retrieved successfully'
    ]);
    exit;
    
} catch (PDOException $e) {
    error_log("Product/Order count error for seller_id {$seller_id}: " . $e->getMessage());
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred',
        'products_count' => 0,
        'orders_count' => 0
    ]);
    exit;
}
?>