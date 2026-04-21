<?php
// /admin/backend/remittances/get_remit_proofs.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

try {
    // Get remit proofs with rider information
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
            r.username as rider_name,
            r.email as rider_email,
            r.status as rider_status,
            array_length(rp.earning_ids, 1) as total_earnings_count
        FROM public.remit_proofs rp
        INNER JOIN public.riders r ON rp.rider_id = r.id
        ORDER BY 
            CASE rp.status 
                WHEN 'pending' THEN 1 
                WHEN 'confirmed' THEN 2 
                WHEN 'rejected' THEN 3 
            END,
            rp.submitted_at DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $remitProofs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get status counts
    $statusSql = "
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
            COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed,
            COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected,
            COALESCE(SUM(amount), 0) as total_amount
        FROM public.remit_proofs
    ";
    $statusStmt = $conn->prepare($statusSql);
    $statusStmt->execute();
    $statusCounts = $statusStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get pending remittance total
    $pendingSql = "
        SELECT COALESCE(SUM(amount), 0) as pending_amount
        FROM public.remit_proofs
        WHERE status = 'pending'
    ";
    $pendingStmt = $conn->prepare($pendingSql);
    $pendingStmt->execute();
    $pendingAmount = $pendingStmt->fetch(PDO::FETCH_ASSOC)['pending_amount'];
    
    echo json_encode([
        'success' => true,
        'data' => $remitProofs,
        'status_counts' => [
            'total' => (int)$statusCounts['total'],
            'pending' => (int)$statusCounts['pending'],
            'confirmed' => (int)$statusCounts['confirmed'],
            'rejected' => (int)$statusCounts['rejected'],
            'total_amount' => floatval($statusCounts['total_amount']),
            'pending_amount' => floatval($pendingAmount)
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