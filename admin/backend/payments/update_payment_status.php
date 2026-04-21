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
    
    // Get the order_id from the payment proof
    $stmt = $conn->prepare("SELECT order_id FROM payment_proofs WHERE id = ?");
    $stmt->execute([$proofId]);
    $paymentProof = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$paymentProof) {
        throw new Exception('Payment proof not found');
    }
    
    $orderId = $paymentProof['order_id'];
    
    if ($status === 'rejected') {
        // Update payment proof with rejection reason
        $stmt = $conn->prepare("
            UPDATE payment_proofs 
            SET status = ?, 
                rejection_reason = ?
            WHERE id = ?
        ");
        $stmt->execute([$status, $reason, $proofId]);
        
        // Update order status to 'cancelled'
        $stmt = $conn->prepare("
            UPDATE orders 
            SET status = 'cancelled', 
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$orderId]);
        
        $orderMessage = "Order #{$orderId} has been cancelled";
    } else {
        // Update payment proof (clear rejection reason if verified)
        $stmt = $conn->prepare("
            UPDATE payment_proofs 
            SET status = ?, 
                rejection_reason = NULL
            WHERE id = ?
        ");
        $stmt->execute([$status, $proofId]);
        
        // Update order status to 'pending' (not pending_payment)
        $stmt = $conn->prepare("
            UPDATE orders 
            SET status = 'pending', 
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$orderId]);
        
        $orderMessage = "Order #{$orderId} status updated to pending";
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Payment proof {$status} successfully! {$orderMessage}",
        'order_id' => $orderId
    ]);
    
} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
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