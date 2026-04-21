<?php
// /admin/backend/remittances/get_remit_details.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid remit ID']);
    exit;
}

$remitId = (int) $_GET['id'];

try {
    // Get remit proof with rider information
    $sql = "
        SELECT 
            rp.id as remit_id,
            rp.rider_id,
            rp.earning_ids,
            rp.gcash_number,
            rp.amount,
            rp.proof_image_url,
            rp.status as remit_status,
            rp.submitted_at,
            rp.rejection_reason,
            r.username as rider_name,
            r.email as rider_email,
            r.status as rider_status,
            r.verification_status
        FROM public.remit_proofs rp
        INNER JOIN public.riders r ON rp.rider_id = r.id
        WHERE rp.id = ?
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$remitId]);
    $remitProof = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$remitProof) {
        echo json_encode(['success' => false, 'message' => 'Remit proof not found']);
        exit;
    }
    
    // Parse earning IDs
    $earningIds = explode(',', trim($remitProof['earning_ids'], '{}'));
    
    // Get earnings details
    if (!empty($earningIds)) {
        $placeholders = implode(',', array_fill(0, count($earningIds), '?'));
        $earningsSql = "
            SELECT 
                re.id as earning_id,
                re.rider_id,
                re.order_id,
                re.delivery_id,
                re.shipping_fee,
                re.total_amount as cod_amount,
                re.created_at,
                re.is_remitted,
                o.subtotal,
                o.platform_fee,
                o.total_amount as order_total,
                o.payment_method,
                o.status as order_status,
                b.username as buyer_name,
                b.email as buyer_email,
                -- COD amount = subtotal + platform_fee (excluding shipping_fee)
                (o.subtotal + o.platform_fee) as calculated_cod_amount
            FROM public.rider_earnings re
            INNER JOIN public.orders o ON re.order_id = o.id
            LEFT JOIN public.buyers b ON o.buyer_id = b.id
            WHERE re.id IN ($placeholders)
            ORDER BY re.created_at DESC
        ";
        
        $earningsStmt = $conn->prepare($earningsSql);
        $earningsStmt->execute($earningIds);
        $earnings = $earningsStmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $earnings = [];
    }
    
    // Calculate summary
    // Total COD amount = sum of (subtotal + platform_fee) for all earnings
    $totalCOD = 0;
    foreach ($earnings as $earning) {
        $totalCOD += floatval($earning['calculated_cod_amount']);
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'remit_proof' => $remitProof,
            'earnings' => $earnings,
            'summary' => [
                'total_earnings' => count($earnings),
                'total_cod_amount' => $totalCOD,
                'remitted_amount' => floatval($remitProof['amount'])
            ]
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} finally {
    $conn = null;
}
?>