<?php
// /seller/backend/payouts/get_payout_details.php
require_once '/var/www/html/connection/db_connection.php';
require_once __DIR__ . '/../session/auth.php';

header('Content-Type: application/json');

$response = ['success' => false, 'data' => null];

try {
    $seller_id = $_SESSION['seller_id'] ?? null;
    $payout_id = $_GET['id'] ?? null;

    if (!$seller_id) {
        throw new Exception('Unauthorized');
    }

    if (!$payout_id || !is_numeric($payout_id)) {
        throw new Exception('Invalid payout ID');
    }

    // Get payout details (verify it belongs to this seller)
    $payoutSql = "
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
        WHERE p.id = ? AND p.seller_id = ?
    ";
    
    $stmt = $conn->prepare($payoutSql);
    $stmt->execute([$payout_id, $seller_id]);
    $payout = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payout) {
        throw new Exception('Payout not found');
    }
    
    // Parse sold_items_ids
    $soldItemsIds = trim($payout['sold_items_ids'], '{}');
    $idsArray = explode(',', $soldItemsIds);
    
    if (!empty($idsArray)) {
        $placeholders = implode(',', array_fill(0, count($idsArray), '?'));
        
        // Get detailed items for this payout
        $itemsSql = "
            SELECT 
                si.id as sold_item_id,
                si.orders_id,
                oi.product_id,
                oi.quantity,
                oi.unit_price,
                oi.total_price as item_total,
                i.product_name,
                i.main_image_url,
                o.created_at as order_date,
                o.status as order_status,
                b.username as buyer_name
            FROM public.sold_items si
            INNER JOIN public.order_items oi ON si.order_items_id = oi.id
            INNER JOIN public.items i ON oi.product_id = i.id
            INNER JOIN public.orders o ON si.orders_id = o.id
            LEFT JOIN public.buyers b ON o.buyer_id = b.id
            WHERE si.id IN ($placeholders)
            ORDER BY o.created_at DESC
        ";
        
        $itemsStmt = $conn->prepare($itemsSql);
        $itemsStmt->execute($idsArray);
        $payout['items'] = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $payout['items'] = [];
    }
    
    // Format dates
    $payout['paid_at_formatted'] = $payout['paid_at'] ? date('M d, Y h:i A', strtotime($payout['paid_at'])) : null;
    $payout['created_at_formatted'] = date('M d, Y', strtotime($payout['created_at']));
    
    $response['success'] = true;
    $response['data'] = $payout;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Get Payout Details Error: " . $e->getMessage());
} catch (PDOException $e) {
    $response['message'] = 'Database error. Please try again later.';
    error_log("Get Payout Details DB Error: " . $e->getMessage());
}

echo json_encode($response);
?>