<?php
// /seller/backend/payouts/get_seller_payouts.php
require_once '/var/www/html/connection/db_connection.php';
require_once __DIR__ . '/../session/auth.php';

header('Content-Type: application/json');

$response = ['success' => false, 'data' => null];

try {
    $seller_id = $_SESSION['seller_id'] ?? null;

    if (!$seller_id) {
        throw new Exception('Unauthorized');
    }

    // Get all payouts for this seller
    $payoutsSql = "
        SELECT 
            p.id as payout_id,
            p.sold_items_ids,
            p.gcash_number,
            p.proof_url,
            p.total_amount,
            p.paid_at,
            p.notes,
            p.created_at
        FROM public.payouts p
        WHERE p.seller_id = ?
        ORDER BY p.created_at DESC
    ";
    
    $stmt = $conn->prepare($payoutsSql);
    $stmt->execute([$seller_id]);
    $payouts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process each payout to get order details
    foreach ($payouts as &$payout) {
        // Parse sold_items_ids array
        $soldItemsIds = trim($payout['sold_items_ids'], '{}');
        $idsArray = explode(',', $soldItemsIds);
        
        if (!empty($idsArray)) {
            $placeholders = implode(',', array_fill(0, count($idsArray), '?'));
            
            // Get order details for these sold items
            $ordersSql = "
                SELECT DISTINCT
                    si.orders_id as order_id,
                    o.created_at as order_date,
                    o.total_amount as order_total,
                    o.status as order_status,
                    b.username as buyer_name,
                    COUNT(oi.id) as item_count
                FROM public.sold_items si
                INNER JOIN public.orders o ON si.orders_id = o.id
                INNER JOIN public.order_items oi ON si.order_items_id = oi.id
                LEFT JOIN public.buyers b ON o.buyer_id = b.id
                WHERE si.id IN ($placeholders)
                GROUP BY si.orders_id, o.created_at, o.total_amount, o.status, b.username
                ORDER BY o.created_at DESC
            ";
            
            $ordersStmt = $conn->prepare($ordersSql);
            $ordersStmt->execute($idsArray);
            $payout['orders'] = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate total items
            $payout['total_items'] = count($idsArray);
            $payout['order_count'] = count($payout['orders']);
        } else {
            $payout['orders'] = [];
            $payout['total_items'] = 0;
            $payout['order_count'] = 0;
        }
        
        // Format dates
        $payout['paid_at_formatted'] = $payout['paid_at'] ? date('M d, Y h:i A', strtotime($payout['paid_at'])) : null;
        $payout['created_at_formatted'] = date('M d, Y', strtotime($payout['created_at']));
    }
    
    // Get summary stats
    $statsSql = "
        SELECT 
            COUNT(*) as total_payouts,
            COALESCE(SUM(total_amount), 0) as total_received,
            MAX(paid_at) as last_payout_date
        FROM public.payouts
        WHERE seller_id = ?
    ";
    $statsStmt = $conn->prepare($statsSql);
    $statsStmt->execute([$seller_id]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    $response['success'] = true;
    $response['data'] = [
        'payouts' => $payouts,
        'stats' => [
            'total_payouts' => (int)($stats['total_payouts'] ?? 0),
            'total_received' => floatval($stats['total_received'] ?? 0),
            'last_payout_date' => $stats['last_payout_date'] ? date('M d, Y', strtotime($stats['last_payout_date'])) : null
        ]
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Get Seller Payouts Error: " . $e->getMessage());
} catch (PDOException $e) {
    $response['message'] = 'Database error. Please try again later.';
    error_log("Get Seller Payouts DB Error: " . $e->getMessage());
}

echo json_encode($response);
?>