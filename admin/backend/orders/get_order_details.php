<?php
// /admin/backend/orders/get_order_details.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid order ID'
    ]);
    exit;
}

$orderId = (int) $_GET['id'];

try {
    // Get order details with buyer and address information
    $sql = "
        SELECT 
            o.id,
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
            b.username as buyer_name,
            b.email as buyer_email,
            b.phone as buyer_phone,
            a.street_address,
            a.city,
            a.province,
            a.postal_code,
            a.plus_code
        FROM public.orders o
        LEFT JOIN public.buyers b ON o.buyer_id = b.id
        LEFT JOIN public.addresses a ON o.address_id = a.id
        WHERE o.id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode([
            'success' => false,
            'message' => 'Order not found'
        ]);
        exit;
    }
    
    // Get order items with product details
    $itemsSql = "
        SELECT 
            oi.id,
            oi.order_id,
            oi.product_id,
            oi.variation_id,
            oi.selected_options,
            oi.quantity,
            oi.unit_price,
            oi.total_price,
            i.product_name,
            i.main_image_url,
            i.seller_id,
            s.full_name as seller_name,
            st.store_name,
            iv.options_json,
            iv.options_json_value,
            iv.sku
        FROM public.order_items oi
        LEFT JOIN public.items i ON oi.product_id = i.id
        LEFT JOIN public.sellers s ON i.seller_id = s.id
        LEFT JOIN public.stores st ON s.id = st.seller_id
        LEFT JOIN public.item_variants iv ON oi.variation_id = iv.id
        WHERE oi.order_id = ?
        ORDER BY oi.id
    ";
    $itemsStmt = $conn->prepare($itemsSql);
    $itemsStmt->execute([$orderId]);
    $orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process order items
    foreach ($orderItems as &$item) {
        // Parse selected options
        if (!empty($item['selected_options'])) {
            $item['options_display'] = $item['selected_options'];
        } elseif (!empty($item['options_json_value'])) {
            $item['options_display'] = $item['options_json_value'];
        } else {
            $item['options_display'] = null;
        }
    }
    
    // Get delivery information if exists
    $deliverySql = "
        SELECT 
            od.id as delivery_id,
            od.rider_id,
            od.status as delivery_status,
            od.assigned_at,
            od.picked_up_at,
            od.completed_at,
            od.cancelled_at,
            r.username as rider_name,
            r.email as rider_email
        FROM public.order_deliveries od
        LEFT JOIN public.riders r ON od.rider_id = r.id
        WHERE od.order_id = ?
        LIMIT 1
    ";
    $deliveryStmt = $conn->prepare($deliverySql);
    $deliveryStmt->execute([$orderId]);
    $delivery = $deliveryStmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'order' => $order,
            'items' => $orderItems,
            'delivery' => $delivery ?: null
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