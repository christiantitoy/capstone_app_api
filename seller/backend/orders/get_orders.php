<?php
// /seller/backend/orders/get_orders.php
require_once '/var/www/html/connection/db_connection.php';
require_once __DIR__ . '/../session/auth.php';

header('Content-Type: application/json');

try {
    $seller_id = $_SESSION['seller_id'] ?? null;
    $status_filter = $_GET['status'] ?? '';
    $search = $_GET['search'] ?? '';
    
    if (!$seller_id) {
        echo json_encode(['success' => false, 'orders' => []]);
        exit;
    }
    
    // Build query to get orders for seller's products
    $sql = "
        SELECT DISTINCT
            o.id,
            o.status,
            o.subtotal,
            o.shipping_fee,
            o.platform_fee,
            o.total_amount,
            o.created_at,
            o.updated_at,
            ba.recipient_name as customer_name,
            ba.full_address as shipping_address,
            ba.phone_number,
            COUNT(oi.id) as item_count,
            SUM(oi.quantity) as total_quantity
        FROM orders o
        INNER JOIN order_items oi ON o.id = oi.order_id
        INNER JOIN items i ON oi.item_id = i.id
        INNER JOIN buyer_addresses ba ON o.address_id = ba.id
        WHERE i.seller_id = ?
    ";
    
    $params = [$seller_id];
    
    // Add status filter
    if (!empty($status_filter)) {
        $sql .= " AND o.status = ?";
        $params[] = $status_filter;
    }
    
    // Add search filter - search by order ID or customer name
    if (!empty($search)) {
        $sql .= " AND (CAST(o.id AS TEXT) LIKE ? OR ba.recipient_name ILIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }
    
    $sql .= " GROUP BY o.id, o.status, o.subtotal, o.shipping_fee, o.platform_fee, o.total_amount, o.created_at, o.updated_at, ba.recipient_name, ba.full_address, ba.phone_number
              ORDER BY o.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for display
    foreach ($orders as &$order) {
        $order['order_number'] = '#' . str_pad($order['id'], 6, '0', STR_PAD_LEFT);
        $order['subtotal_formatted'] = '₱' . number_format($order['subtotal'], 2);
        $order['created_date'] = date('M d, Y', strtotime($order['created_at']));
        $order['created_datetime'] = date('M d, Y h:i A', strtotime($order['created_at']));
        
        // Get order items for details
        $items_stmt = $conn->prepare("
            SELECT 
                oi.id,
                oi.quantity,
                oi.price,
                oi.total_price,
                i.product_name,
                i.main_image_url,
                v.options_json as variant_options,
                v.sku as variant_sku
            FROM order_items oi
            INNER JOIN items i ON oi.item_id = i.id
            LEFT JOIN item_variants v ON oi.variant_id = v.id
            WHERE oi.order_id = ?
        ");
        $items_stmt->execute([$order['id']]);
        $order['items'] = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode(['success' => true, 'orders' => $orders]);
    exit;
    
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'orders' => [], 'error' => $e->getMessage()]);
    exit;
}
?>