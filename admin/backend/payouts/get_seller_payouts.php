<?php
// /admin/backend/payouts/get_seller_payouts.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['seller_id']) || !is_numeric($_GET['seller_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid seller ID']);
    exit;
}

$sellerId = (int) $_GET['seller_id'];

try {
    // Get seller info
    $sellerSql = "
        SELECT 
            s.id as seller_id,
            s.full_name as seller_name,
            s.email as seller_email,
            st.store_name
        FROM public.sellers s
        LEFT JOIN public.stores st ON s.id = st.seller_id
        WHERE s.id = ?
    ";
    $stmt = $conn->prepare($sellerSql);
    $stmt->execute([$sellerId]);
    $seller = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$seller) {
        echo json_encode(['success' => false, 'message' => 'Seller not found']);
        exit;
    }
    
    // Get sold items for this seller
    $itemsSql = "
        SELECT 
            si.id as sold_item_id,
            si.orders_id,
            si.created_at as sold_date,
            si.paid_status,
            o.payment_method,
            oi.product_id,
            oi.quantity,
            oi.unit_price,
            oi.total_price as item_total,
            i.product_name
        FROM public.sold_items si
        INNER JOIN public.orders o ON si.orders_id = o.id
        INNER JOIN public.order_items oi ON si.order_items_id = oi.id
        INNER JOIN public.items i ON oi.product_id = i.id
        WHERE i.seller_id = ?
          AND o.payment_method = 'Gcash - Rider Delivery'
          AND o.status IN ('delivered', 'complete')
        ORDER BY si.created_at DESC
    ";
    
    $stmt = $conn->prepare($itemsSql);
    $stmt->execute([$sellerId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate totals
    $totalAmount = 0;
    $unpaidAmount = 0;
    $paidAmount = 0;
    $allPaid = true;
    $allUnpaid = true;
    
    foreach ($items as $item) {
        $amount = floatval($item['item_total']);
        $totalAmount += $amount;
        
        if ($item['paid_status'] === 'paid') {
            $paidAmount += $amount;
            $allUnpaid = false;
        } else {
            $unpaidAmount += $amount;
            $allPaid = false;
        }
    }
    
    $seller['total_items'] = count($items);
    $seller['total_amount'] = $totalAmount;
    $seller['unpaid_amount'] = $unpaidAmount;
    $seller['paid_amount'] = $paidAmount;
    
    if ($allPaid) {
        $seller['paid_status'] = 'Paid';
    } elseif ($allUnpaid) {
        $seller['paid_status'] = 'Unpaid';
    } else {
        $seller['paid_status'] = 'Partial';
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'seller' => $seller,
            'items' => $items
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>