<?php
// /admin/backend/payments/update_payment_status.php
require_once '/var/www/html/connection/db_connection.php';
require_once '../session/auth_admin.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$proofId = $input['proof_id'] ?? null;
$status = $input['status'] ?? null;
$reason = $input['reason'] ?? null;

if (!$proofId || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if (!in_array($status, ['verified', 'rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

// Require reason when rejecting
if ($status === 'rejected' && empty($reason)) {
    echo json_encode(['success' => false, 'message' => 'Rejection reason is required']);
    exit;
}

try {
    $conn->beginTransaction();
    
    if ($status === 'rejected') {
        // Update with rejection reason
        $stmt = $conn->prepare("
            UPDATE payment_proofs 
            SET status = ?, 
                rejection_reason = ?
            WHERE id = ?
        ");
        $stmt->execute([$status, $reason, $proofId]);
    } else {
        // Update without reason
        $stmt = $conn->prepare("
            UPDATE payment_proofs 
            SET status = ?, 
                rejection_reason = NULL
            WHERE id = ?
        ");
        $stmt->execute([$status, $proofId]);
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Payment proof {$status} successfully!"
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