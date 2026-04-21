<?php
// /admin/backend/subscriptions/get_subscription_details.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid payment ID']);
    exit;
}

$paymentId = (int) $_GET['id'];

try {
    // Get payment details with seller and plan information
    $sql = "
        SELECT 
            spp.id as payment_id,
            spp.seller_id,
            spp.seller_plan_id,
            spp.gcash_number,
            spp.amount,
            spp.proof_image_url,
            spp.submitted_at,
            spp.reviewed_at,
            spp.status as payment_status,
            spp.notes,
            sp.plan,
            sp.billing,
            sp.start_date,
            sp.end_date,
            sp.status as plan_status,
            sp.created_at as plan_created,
            s.full_name as seller_name,
            s.email as seller_email,
            s.approval_status as seller_status
        FROM public.seller_plan_payments spp
        INNER JOIN public.sellers_plan sp ON spp.seller_plan_id = sp.id
        INNER JOIN public.sellers s ON spp.seller_id = s.id
        WHERE spp.id = ?
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$paymentId]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payment) {
        echo json_encode(['success' => false, 'message' => 'Payment not found']);
        exit;
    }
    
    // Get seller's store info
    $storeSql = "
        SELECT store_name, logo_url
        FROM public.stores
        WHERE seller_id = ?
    ";
    $storeStmt = $conn->prepare($storeSql);
    $storeStmt->execute([$payment['seller_id']]);
    $store = $storeStmt->fetch(PDO::FETCH_ASSOC);
    
    $payment['store_name'] = $store['store_name'] ?? null;
    $payment['store_logo'] = $store['logo_url'] ?? null;
    
    // Get seller's previous subscriptions
    $historySql = "
        SELECT 
            spp.id as payment_id,
            sp.plan,
            sp.billing,
            spp.amount,
            spp.status as payment_status,
            spp.submitted_at,
            sp.start_date,
            sp.end_date,
            sp.status as plan_status
        FROM public.seller_plan_payments spp
        INNER JOIN public.sellers_plan sp ON spp.seller_plan_id = sp.id
        WHERE spp.seller_id = ?
        ORDER BY spp.submitted_at DESC
        LIMIT 5
    ";
    
    $historyStmt = $conn->prepare($historySql);
    $historyStmt->execute([$payment['seller_id']]);
    $subscriptionHistory = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'payment' => $payment,
            'history' => $subscriptionHistory ?: []
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} finally {
    $conn = null;
}
?>