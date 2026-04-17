<?php
// /seller/backend/orders/get_order_details.php
require_once '/var/www/html/connection/db_connection.php';
require_once __DIR__ . '/../session/auth.php';

header('Content-Type: application/json');

try {
    $seller_id = $_SESSION['seller_id'] ?? null;
    $order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if (!$seller_id || !$order_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }
    
    // Get order details with address
    $stmt = $conn->prepare("
        SELECT DISTINCT
            o.id,
            o.status,
            o.subtotal,
            o.shipping_fee,
            o.platform_fee,
            o.total_amount,
            o.payment_method,
            o.created_at,
            o.updated_at,
            ba.recipient_name as customer_name,
            ba.phone_number,
            ba.full_address as shipping_address,
            ba.gps_location
        FROM orders o
        INNER JOIN buyer_addresses ba ON o.address_id = ba.id
        INNER JOIN order_items oi ON o.id = oi.order_id
        INNER JOIN items i ON oi.item_id = i.id
        WHERE o.id = ? AND i.seller_id = ?
        GROUP BY o.id, o.status, o.subtotal, o.shipping_fee, o.platform_fee, o.total_amount, o.payment_method, o.created_at, o.updated_at, ba.recipient_name, ba.phone_number, ba.full_address, ba.gps_location
    ");
    $stmt->execute([$order_id, $seller_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }
    
    $order['order_number'] = '#' . str_pad($order['id'], 6, '0', STR_PAD_LEFT);
    $order['subtotal_formatted'] = '₱' . number_format($order['subtotal'], 2);
    $order['shipping_fee_formatted'] = '₱' . number_format($order['shipping_fee'], 2);
    $order['platform_fee_formatted'] = '₱' . number_format($order['platform_fee'], 2);
    $order['total_amount_formatted'] = '₱' . number_format($order['total_amount'], 2);
    $order['created_datetime'] = date('M d, Y h:i A', strtotime($order['created_at']));
    
    // Get order items
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
    $items_stmt->execute([$order_id]);
    $order['items'] = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'order' => $order]);
    exit;
    
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>