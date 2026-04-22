<?php
// /admin/backend/payouts/create_payout.php
require_once '/var/www/html/connection/db_connection.php';
require_once '../session/auth_admin.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$sellerId = $input['seller_id'] ?? null;
$gcashNumber = $input['gcash_number'] ?? null;
$proofUrl = $input['proof_url'] ?? null;
$adminId = $_SESSION['admin_id'] ?? null;

if (!$sellerId || !$gcashNumber || !$proofUrl) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if (!preg_match('/^09[0-9]{9}$/', $gcashNumber)) {
    echo json_encode(['success' => false, 'message' => 'Invalid GCash number format']);
    exit;
}

try {
    $conn->beginTransaction();
    
    // Get all unpaid sold items for this seller
    $itemsSql = "
        SELECT 
            si.id as sold_item_id,
            oi.total_price as item_total
        FROM public.sold_items si
        INNER JOIN public.order_items oi ON si.order_items_id = oi.id
        INNER JOIN public.items i ON oi.product_id = i.id
        INNER JOIN public.orders o ON si.orders_id = o.id
        WHERE i.seller_id = ?
          AND si.paid_status IS NULL
          AND o.payment_method = 'Gcash - Rider Delivery'
          AND o.status IN ('delivered', 'complete')
    ";
    
    $itemsStmt = $conn->prepare($itemsSql);
    $itemsStmt->execute([$sellerId]);
    $unpaidItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($unpaidItems)) {
        throw new Exception('No unpaid items found for this seller');
    }
    
    // Extract sold_item_ids and calculate total
    $soldItemsIds = array_column($unpaidItems, 'sold_item_id');
    $totalAmount = array_sum(array_column($unpaidItems, 'item_total'));
    
    // Convert array to PostgreSQL array format
    $soldItemsIdsPg = '{' . implode(',', $soldItemsIds) . '}';
    
    // Insert payout record
    $payoutSql = "
        INSERT INTO public.payouts 
            (seller_id, sold_items_ids, gcash_number, proof_url, total_amount, created_by, created_at)
        VALUES 
            (?, ?, ?, ?, ?, ?, NOW())
        RETURNING id
    ";
    
    $payoutStmt = $conn->prepare($payoutSql);
    $payoutStmt->execute([$sellerId, $soldItemsIdsPg, $gcashNumber, $proofUrl, $totalAmount, $adminId]);
    $payoutId = $payoutStmt->fetchColumn();
    
    // Update all sold_items to paid
    $placeholders = implode(',', array_fill(0, count($soldItemsIds), '?'));
    $updateSql = "
        UPDATE public.sold_items 
        SET paid_status = 'paid'
        WHERE id IN ($placeholders)
    ";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->execute($soldItemsIds);
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Payout processed successfully!',
        'payout_id' => $payoutId,
        'total_amount' => $totalAmount,
        'items_paid' => count($soldItemsIds)
    ]);
    
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>