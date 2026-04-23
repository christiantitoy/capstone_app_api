<?php
// /admin/backend/payments/update_payment_status.php
require_once '/var/www/html/connection/db_connection.php';
require_once '../session/auth_admin.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// ✅ Notification function
function sendPushNotification($user_id, $title, $message) {
    $url = 'https://capstone-app-api-r1ux.onrender.com/connection/notif/sendNotification.php';
    
    $data = json_encode([
        'user_id' => $user_id,
        'title' => $title,
        'message' => $message
    ]);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// ✅ Get verified message
function getVerifiedMessage($order_id) {
    return "Great news! Your GCash payment for order #$order_id has been verified successfully. Your order is now being processed and will be packed soon. Thank you for shopping with DaguitZone!";
}

// ✅ Get rejected message
function getRejectedMessage($order_id, $reason) {
    return "Your GCash payment for order #$order_id has been rejected. Reason: $reason The order has been cancelled. Please place a new order.";
}

// ✅ Send payment notification (calls sendNotification.php which handles save + push)
function sendPaymentNotification($conn, $buyer_id, $order_id, $status, $reason = null) {
    try {
        if ($status === 'verified') {
            $title = "Payment Verified";
            $message = getVerifiedMessage($order_id);
        } else {
            $title = "Payment Rejected";
            $message = getRejectedMessage($order_id, $reason);
        }
        
        // ✅ Call sendNotification.php - it handles BOTH saving and sending
        $result = sendPushNotification($buyer_id, $title, $message);
        $saved = $result['notification_saved'] ?? $result['success'] ?? false;
        $sent = $result['success'] ?? false;
        
        error_log("Payment notification for order $order_id ($status) - Saved: " . ($saved ? 'Yes' : 'No') . ", Sent: " . ($sent ? 'Yes' : 'No'));
        
        return ['saved' => $saved, 'sent' => $sent];
        
    } catch (Exception $e) {
        error_log("Payment Notification error: " . $e->getMessage());
        return ['saved' => false, 'sent' => false];
    }
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
    
    // Get the order_id and buyer_id from the payment proof
    $stmt = $conn->prepare("SELECT order_id, buyer_id FROM payment_proofs WHERE id = ?");
    $stmt->execute([$proofId]);
    $paymentProof = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$paymentProof) {
        throw new Exception('Payment proof not found');
    }
    
    $orderId = $paymentProof['order_id'];
    $buyerId = $paymentProof['buyer_id'];
    
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
        
        // Update order status to 'pending'
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
    
    // Send notification after successful commit
    $notification_result = sendPaymentNotification($conn, $buyerId, $orderId, $status, $reason);
    
    echo json_encode([
        'success' => true,
        'message' => "Payment proof {$status} successfully! {$orderMessage}",
        'order_id' => $orderId,
        'buyer_id' => $buyerId,
        'notification_saved' => $notification_result['saved'],
        'notification_sent' => $notification_result['sent']
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