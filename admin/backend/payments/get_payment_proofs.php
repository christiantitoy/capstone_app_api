<?php
// /admin/backend/payments/get_payment_proofs.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

try {
    // Get payment proofs with related order and buyer information
    $sql = "
        SELECT 
            pp.id as proof_id,
            pp.order_id,
            pp.gcash_number,
            pp.proof_image_url,
            pp.amount,
            pp.submitted_at,
            pp.status as payment_status,
            pp.buyer_id,
            o.total_amount as order_total,
            o.status as order_status,
            o.payment_method,
            o.created_at as order_date,
            b.username as buyer_name,
            b.email as buyer_email
        FROM public.payment_proofs pp
        INNER JOIN public.orders o ON pp.order_id = o.id
        INNER JOIN public.buyers b ON pp.buyer_id = b.id
        ORDER BY 
            CASE pp.status 
                WHEN 'pending' THEN 1 
                WHEN 'verified' THEN 2 
                WHEN 'rejected' THEN 3 
            END,
            pp.submitted_at DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $paymentProofs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get status counts
    $statusSql = "
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
            COUNT(CASE WHEN status = 'verified' THEN 1 END) as verified,
            COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected
        FROM public.payment_proofs
    ";
    $statusStmt = $conn->prepare($statusSql);
    $statusStmt->execute();
    $statusCounts = $statusStmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $paymentProofs,
        'status_counts' => [
            'total' => (int)$statusCounts['total'],
            'pending' => (int)$statusCounts['pending'],
            'verified' => (int)$statusCounts['verified'],
            'rejected' => (int)$statusCounts['rejected']
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>