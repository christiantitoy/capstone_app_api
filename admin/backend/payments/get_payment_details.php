<?php
// /admin/backend/payments/get_payment_details.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid payment ID']);
    exit;
}

$proofId = (int) $_GET['id'];

try {
    $sql = "
        SELECT 
            pp.id as proof_id,
            pp.order_id,
            pp.gcash_number,
            pp.proof_image_url,
            pp.amount,
            pp.submitted_at,
            pp.status as payment_status,
            pp.rejection_reason,
            pp.buyer_id,
            o.total_amount as order_total,
            o.subtotal,
            o.shipping_fee,
            o.platform_fee,
            o.status as order_status,
            o.payment_method,
            o.created_at as order_date,
            b.username as buyer_name,
            b.email as buyer_email
        FROM public.payment_proofs pp
        INNER JOIN public.orders o ON pp.order_id = o.id
        INNER JOIN public.buyers b ON pp.buyer_id = b.id
        WHERE pp.id = ?
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$proofId]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payment) {
        echo json_encode(['success' => false, 'message' => 'Payment proof not found']);
        exit;
    }
    
    // Get order items
    $itemsSql = "
        SELECT 
            oi.id,
            oi.product_id,
            oi.quantity,
            oi.unit_price,
            oi.total_price,
            i.product_name,
            i.main_image_url,
            s.full_name as seller_name,
            st.store_name
        FROM public.order_items oi
        LEFT JOIN public.items i ON oi.product_id = i.id
        LEFT JOIN public.sellers s ON i.seller_id = s.id
        LEFT JOIN public.stores st ON s.id = st.seller_id
        WHERE oi.order_id = ?
        ORDER BY oi.id
    ";
    
    $itemsStmt = $conn->prepare($itemsSql);
    $itemsStmt->execute([$payment['order_id']]);
    $orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'payment' => $payment,
            'items' => $orderItems
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} finally {
    $conn = null;
}
?>