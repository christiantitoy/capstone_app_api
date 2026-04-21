<?php
// /admin/backend/payouts/get_all_payouts.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

try {
    // Get all sold_items with GCash payment method
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
        WHERE o.payment_method = 'Gcash - Rider Delivery'
          AND o.status IN ('delivered', 'complete')
        ORDER BY s.id, si.created_at DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $soldItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group by seller for summary
    $sellerSummary = [];
    
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
                'paid_status' => 'Unpaid', // Will be updated based on items
                'unpaid_amount' => 0,
                'paid_amount' => 0,
                'sold_items' => []
            ];
        }
        
        // Track paid/unpaid status
        $isPaid = ($item['paid_status'] === 'paid');
        $amount = floatval($item['item_total']);
        
        $sellerSummary[$sellerId]['total_amount'] += $amount;
        $sellerSummary[$sellerId]['total_items']++;
        
        if ($isPaid) {
            $sellerSummary[$sellerId]['paid_amount'] += $amount;
        } else {
            $sellerSummary[$sellerId]['unpaid_amount'] += $amount;
        }
        
        $sellerSummary[$sellerId]['sold_items'][] = $item;
    }
    
    // Determine overall paid status for each seller
    foreach ($sellerSummary as &$seller) {
        $allPaid = true;
        $allUnpaid = true;
        
        foreach ($seller['sold_items'] as $item) {
            if ($item['paid_status'] === 'paid') {
                $allUnpaid = false;
            } else {
                $allPaid = false;
            }
        }
        
        if ($allPaid) {
            $seller['paid_status'] = 'Paid';
        } elseif ($allUnpaid) {
            $seller['paid_status'] = 'Unpaid';
        } else {
            $seller['paid_status'] = 'Partial';
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'sold_items' => $soldItems,
            'seller_summary' => array_values($sellerSummary)
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