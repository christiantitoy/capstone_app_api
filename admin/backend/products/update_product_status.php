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

if (!in_array($status, ['approved', 'removed'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

// Require reason when removing
if ($status === 'removed' && empty($reason)) {
    echo json_encode(['success' => false, 'message' => 'Removal reason is required']);
    exit;
}

try {
    $conn->beginTransaction();
    
    if ($status === 'removed') {
        // Update with removal reason
        $stmt = $conn->prepare("
            UPDATE items 
            SET status = ?, 
                remove_reason = ?,
                updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$status, $reason, $productId]);
    } else {
        // Update without reason (clear remove_reason if approved/restored)
        $stmt = $conn->prepare("
            UPDATE items 
            SET status = ?, 
                remove_reason = NULL,
                updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$status, $productId]);
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => $status === 'approved' ? 'Product restored successfully!' : 'Product removed successfully!'
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