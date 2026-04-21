<?php
// /admin/backend/payouts/get_payouts.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

try {
    // Get orders with GCash - Rider Delivery payment method
    // that are delivered or completed
    $sql = "
        SELECT 
            o.id as order_id,
            o.buyer_id,
            o.payment_method,
            o.total_amount,
            o.status as order_status,
            o.created_at as order_date,
            o.updated_at,
            oi.id as item_id,
            oi.product_id,
            oi.variation_id,
            oi.quantity,
            oi.unit_price,
            oi.total_price,
            i.seller_id,
            i.product_name,
            s.full_name as seller_name,
            s.email as seller_email,
            st.store_name,
            od.status as delivery_status,
            od.completed_at as delivery_completed
        FROM public.orders o
        INNER JOIN public.order_items oi ON o.id = oi.order_id
        INNER JOIN public.items i ON oi.product_id = i.id
        INNER JOIN public.sellers s ON i.seller_id = s.id
        LEFT JOIN public.stores st ON s.id = st.seller_id
        LEFT JOIN public.order_deliveries od ON o.id = od.order_id
        WHERE o.payment_method = 'gcash'
          AND o.status IN ('delivered', 'complete')
        ORDER BY o.id DESC, i.seller_id
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $payoutItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group by seller for summary
    $sellerSummary = [];
    $orderGroups = [];
    
    foreach ($payoutItems as $item) {
        $sellerId = $item['seller_id'];
        
        if (!isset($sellerSummary[$sellerId])) {
            $sellerSummary[$sellerId] = [
                'seller_id' => $sellerId,
                'seller_name' => $item['seller_name'],
                'seller_email' => $item['seller_email'],
                'store_name' => $item['store_name'],
                'total_sales' => 0,
                'total_orders' => 0,
                'items' => []
            ];
        }
        
        $sellerSummary[$sellerId]['total_sales'] += floatval($item['total_price']);
        
        // Track unique orders
        if (!isset($orderGroups[$item['order_id']])) {
            $orderGroups[$item['order_id']] = true;
            $sellerSummary[$sellerId]['total_orders']++;
        }
        
        $sellerSummary[$sellerId]['items'][] = $item;
    }
    
    // Calculate totals
    $totalPayout = array_sum(array_column($sellerSummary, 'total_sales'));
    $totalOrders = count($orderGroups);
    $totalSellers = count($sellerSummary);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'items' => $payoutItems,
            'seller_summary' => array_values($sellerSummary),
            'totals' => [
                'total_payout' => $totalPayout,
                'total_orders' => $totalOrders,
                'total_sellers' => $totalSellers
            ]
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