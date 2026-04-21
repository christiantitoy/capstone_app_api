<?php
// /admin/backend/products/update_product_status.php
require_once '/var/www/html/connection/db_connection.php';
require_once '../session/auth_admin.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$productId = $input['product_id'] ?? null;
$status = $input['status'] ?? null;
$reason = $input['reason'] ?? null;

if (!$productId || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if (!in_array($status, ['approved', 'on_hold', 'removed', 'on_review'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

// Require reason when removing or putting on hold
if (($status === 'removed' || $status === 'on_hold') && empty($reason)) {
    echo json_encode(['success' => false, 'message' => 'Reason is required for this action']);
    exit;
}

try {
    $conn->beginTransaction();
    
    if ($status === 'removed' || $status === 'on_hold') {
        // Update with reason
        $stmt = $conn->prepare("
            UPDATE items 
            SET status = ?, 
                status_reason = ?,
                updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$status, $reason, $productId]);
    } else {
        // Update without reason (clear status reason if approved)
        $stmt = $conn->prepare("
            UPDATE items 
            SET status = ?, 
                status_reason = NULL,
                updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$status, $productId]);
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Product status updated to {$status}"
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