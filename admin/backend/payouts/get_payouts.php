<?php
// /admin/backend/payouts/get_payouts.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

try {
    // Get all payouts with seller information
    $payoutsSql = "
        SELECT 
            p.id as payout_id,
            p.seller_id,
            p.sold_items_ids,
            p.gcash_number,
            p.proof_url,
            p.total_amount,
            p.paid_at,
            p.created_at,
            s.full_name as seller_name,
            s.email as seller_email,
            st.store_name
        FROM public.payouts p
        INNER JOIN public.sellers s ON p.seller_id = s.id
        LEFT JOIN public.stores st ON s.id = st.seller_id
        ORDER BY p.created_at DESC
    ";
    
    $stmt = $conn->prepare($payoutsSql);
    $stmt->execute();
    $payouts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get unpaid sold items grouped by seller
    $unpaidSql = "
        SELECT 
            i.seller_id,
            s.full_name as seller_name,
            s.email as seller_email,
            st.store_name,
            COUNT(*) as total_items,
            COALESCE(SUM(oi.total_price), 0) as total_amount,
            ARRAY_AGG(si.id) as sold_items_ids
        FROM public.sold_items si
        INNER JOIN public.order_items oi ON si.order_items_id = oi.id
        INNER JOIN public.items i ON oi.product_id = i.id
        INNER JOIN public.orders o ON si.orders_id = o.id
        INNER JOIN public.sellers s ON i.seller_id = s.id
        LEFT JOIN public.stores st ON s.id = st.seller_id
        WHERE si.paid_status IS NULL
          AND o.payment_method = 'Gcash - Rider Delivery'
          AND o.status IN ('delivered', 'complete')
        GROUP BY i.seller_id, s.full_name, s.email, st.store_name
        ORDER BY total_amount DESC
    ";
    
    $unpaidStmt = $conn->prepare($unpaidSql);
    $unpaidStmt->execute();
    $unpaidSellers = $unpaidStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate totals
    $totalPending = array_sum(array_column($unpaidSellers, 'total_amount'));
    $totalSellers = count($unpaidSellers);
    $totalItems = array_sum(array_column($unpaidSellers, 'total_items'));
    
    // Process each seller to add paid_status
    foreach ($unpaidSellers as &$seller) {
        $seller['paid_status'] = 'Unpaid';
        // Convert PostgreSQL array string to actual array for JSON
        if (isset($seller['sold_items_ids'])) {
            $seller['sold_items_ids'] = trim($seller['sold_items_ids'], '{}');
            $seller['sold_items_ids_array'] = explode(',', $seller['sold_items_ids']);
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'payouts' => $payouts,
            'seller_summary' => $unpaidSellers,
            'totals' => [
                'total_pending' => $totalPending,
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