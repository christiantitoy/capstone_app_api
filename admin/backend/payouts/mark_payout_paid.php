<?php
// /admin/backend/payouts/mark_payout_paid.php
require_once '/var/www/html/connection/db_connection.php';
require_once '../session/auth_admin.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$sellerId = $input['seller_id'] ?? null;

if (!$sellerId) {
    echo json_encode(['success' => false, 'message' => 'Missing seller ID']);
    exit;
}

try {
    $conn->beginTransaction();
    
    // Update all unpaid sold_items for this seller to 'paid'
    $sql = "
        UPDATE public.sold_items si
        SET paid_status = 'paid'
        FROM public.order_items oi
        INNER JOIN public.items i ON oi.product_id = i.id
        INNER JOIN public.orders o ON si.orders_id = o.id
        WHERE si.order_items_id = oi.id
          AND i.seller_id = ?
          AND si.paid_status IS NULL
          AND o.payment_method = 'gcash'
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$sellerId]);
    $rowCount = $stmt->rowCount();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Marked {$rowCount} items as paid",
        'items_updated' => $rowCount
    ]);
    
} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>