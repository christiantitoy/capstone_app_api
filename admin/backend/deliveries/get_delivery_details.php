<?php
// /admin/backend/deliveries/get_delivery_details.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid delivery ID'
    ]);
    exit;
}

$deliveryId = (int) $_GET['id'];

try {
    // Get delivery details with related information
    $sql = "
        SELECT 
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
            o.buyer_id,
            o.payment_method,
            o.subtotal,
            o.shipping_fee,
            o.platform_fee,
            o.total_amount,
            o.status as order_status,
            o.created_at as order_created,
            r.username as rider_name,
            r.email as rider_email,
            r.status as rider_status,
            r.verification_status as rider_verification,
            b.username as buyer_name,
            b.email as buyer_email,
            ba.recipient_name,
            ba.phone_number,
            ba.full_address,
            ba.gps_location
        FROM public.order_deliveries od
        LEFT JOIN public.orders o ON od.order_id = o.id
        LEFT JOIN public.riders r ON od.rider_id = r.id
        LEFT JOIN public.buyers b ON o.buyer_id = b.id
        LEFT JOIN public.buyer_addresses ba ON o.address_id = ba.id
        WHERE od.id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$deliveryId]);
    $delivery = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$delivery) {
        echo json_encode([
            'success' => false,
            'message' => 'Delivery not found'
        ]);
        exit;
    }
    
    // Get order items
    $itemsSql = "
        SELECT 
            oi.id,
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
    $itemsStmt->execute([$delivery['order_id']]);
    $orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate delivery duration if completed
    $deliveryDuration = null;
    if ($delivery['completed_at'] && $delivery['assigned_at']) {
        $assigned = new DateTime($delivery['assigned_at']);
        $completed = new DateTime($delivery['completed_at']);
        $interval = $assigned->diff($completed);
        $deliveryDuration = $interval->format('%h hrs %i mins');
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'delivery' => $delivery,
            'items' => $orderItems,
            'delivery_duration' => $deliveryDuration
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