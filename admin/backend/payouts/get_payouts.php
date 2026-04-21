<?php
// /admin/backend/payouts/get_payouts.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

try {
    // Get sold items with NULL paid_status and GCash payment method
    $sql = "
        SELECT 
            si.id as sold_item_id,
            si.order_deliveries_id,
            si.order_items_id,
            si.orders_id,
            si.created_at as sold_date,
            si.paid_status,
            o.payment_method,
            o.total_amount as order_total,
            o.status as order_status,
            oi.product_id,
            oi.quantity,
            oi.unit_price,
            oi.total_price as item_total,
            i.seller_id,
            i.product_name,
            s.full_name as seller_name,
            s.email as seller_email,
            st.store_name,
            od.rider_id,
            od.status as delivery_status,
            r.username as rider_name
        FROM public.sold_items si
        INNER JOIN public.orders o ON si.orders_id = o.id
        INNER JOIN public.order_items oi ON si.order_items_id = oi.id
        INNER JOIN public.items i ON oi.product_id = i.id
        INNER JOIN public.sellers s ON i.seller_id = s.id
        LEFT JOIN public.stores st ON s.id = st.seller_id
        LEFT JOIN public.order_deliveries od ON si.order_deliveries_id = od.id
        LEFT JOIN public.riders r ON od.rider_id = r.id
        WHERE si.paid_status IS NULL
          AND o.payment_method = 'gcash'
          AND o.status IN ('delivered', 'complete')
        ORDER BY s.id, si.created_at DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $soldItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group by seller for summary
    $sellerSummary = [];
    $sellerItems = [];
    
    foreach ($soldItems as $item) {
        $sellerId = $item['seller_id'];
        
        if (!isset($sellerSummary[$sellerId])) {
            $sellerSummary[$sellerId] = [
                'seller_id' => $sellerId,
                'seller_name' => $item['seller_name'],
                'seller_email' => $item['seller_email'],
                'store_name' => $item['store_name'],
                'total_amount' => 0,
                'total_items' => 0,
                'paid_status' => 'Unpaid',
                'sold_items' => []
            ];
        }
        
        // Add item total to seller's total amount
        $sellerSummary[$sellerId]['total_amount'] += floatval($item['item_total']);
        $sellerSummary[$sellerId]['total_items']++;
        $sellerSummary[$sellerId]['sold_items'][] = $item;
    }
    
    // Calculate totals
    $totalPayout = array_sum(array_column($sellerSummary, 'total_amount'));
    $totalSellers = count($sellerSummary);
    $totalItems = count($soldItems);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'sold_items' => $soldItems,
            'seller_summary' => array_values($sellerSummary),
            'totals' => [
                'total_payout' => $totalPayout,
                'total_sellers' => $totalSellers,
                'total_items' => $totalItems
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