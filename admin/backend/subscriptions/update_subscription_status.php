<?php
// /admin/backend/subscriptions/update_subscription_status.php
require_once '/var/www/html/connection/db_connection.php';
require_once '../session/auth_admin.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$paymentId = $input['payment_id'] ?? null;
$status = $input['status'] ?? null;
$notes = $input['notes'] ?? null;

if (!$paymentId || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if (!in_array($status, ['confirmed', 'rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

// Require notes when rejecting
if ($status === 'rejected' && empty($notes)) {
    echo json_encode(['success' => false, 'message' => 'Rejection notes are required']);
    exit;
}

try {
    $conn->beginTransaction();
    
    // Get payment and plan info
    $stmt = $conn->prepare("
        SELECT spp.seller_plan_id, sp.seller_id, sp.plan, sp.billing 
        FROM seller_plan_payments spp
        INNER JOIN sellers_plan sp ON spp.seller_plan_id = sp.id
        WHERE spp.id = ?
    ");
    $stmt->execute([$paymentId]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$info) {
        throw new Exception('Payment not found');
    }
    
    if ($status === 'confirmed') {
        // Update payment status
        $stmt = $conn->prepare("
            UPDATE seller_plan_payments 
            SET status = 'confirmed', reviewed_at = NOW(), notes = NULL
            WHERE id = ?
        ");
        $stmt->execute([$paymentId]);
        
        // Calculate end date based on billing
        $endDate = null;
        if ($info['billing'] === 'monthly') {
            $endDate = date('Y-m-d H:i:s', strtotime('+1 month'));
        } elseif ($info['billing'] === 'yearly') {
            $endDate = date('Y-m-d H:i:s', strtotime('+1 year'));
        }
        
        // Update seller's plan status
        if ($endDate) {
            $stmt = $conn->prepare("
                UPDATE sellers_plan 
                SET status = 'active', start_date = NOW(), end_date = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$endDate, $info['seller_plan_id']]);
        } else {
            // Lifetime plan
            $stmt = $conn->prepare("
                UPDATE sellers_plan 
                SET status = 'active', start_date = NOW(), end_date = NULL, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$info['seller_plan_id']]);
        }
    } else {
        // Rejected
        $stmt = $conn->prepare("
            UPDATE seller_plan_payments 
            SET status = 'rejected', reviewed_at = NOW(), notes = ?
            WHERE id = ?
        ");
        $stmt->execute([$notes, $paymentId]);
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Subscription payment " . ($status === 'confirmed' ? 'confirmed' : 'rejected') . " successfully!"
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