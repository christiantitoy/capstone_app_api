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
$gcashNumber = $input['gcash_number'] ?? null;
$proofUrl = $input['proof_url'] ?? null;

if (!$sellerId) {
    echo json_encode(['success' => false, 'message' => 'Missing seller ID']);
    exit;
}

if (!$gcashNumber) {
    echo json_encode(['success' => false, 'message' => 'GCash number is required']);
    exit;
}

if (!$proofUrl) {
    echo json_encode(['success' => false, 'message' => 'Proof of payment is required']);
    exit;
}

// Validate GCash number format (09xxxxxxxxx)
if (!preg_match('/^09[0-9]{9}$/', $gcashNumber)) {
    echo json_encode(['success' => false, 'message' => 'Invalid GCash number format']);
    exit;
}

try {
    $conn->beginTransaction();
    
    // PostgreSQL UPDATE with FROM requires a different syntax
    $sql = "
        UPDATE public.sold_items 
        SET paid_status = 'paid',
            gcash_number = ?,
            proof_url = ?,
            paid_at = NOW()
        WHERE id IN (
            SELECT si.id
            FROM public.sold_items si
            INNER JOIN public.order_items oi ON si.order_items_id = oi.id
            INNER JOIN public.items i ON oi.product_id = i.id
            INNER JOIN public.orders o ON si.orders_id = o.id
            WHERE i.seller_id = ?
              AND si.paid_status IS NULL
              AND o.payment_method = 'Gcash - Rider Delivery'
        )
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$gcashNumber, $proofUrl, $sellerId]);
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