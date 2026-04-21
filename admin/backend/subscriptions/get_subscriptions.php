<?php
// /admin/backend/subscriptions/get_subscriptions.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

try {
    // Get pending seller plan payments with related information
    $sql = "
        SELECT 
            spp.id as payment_id,
            spp.seller_id,
            spp.seller_plan_id,
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
            s.full_name as seller_name,
            s.email as seller_email,
            st.store_name,
            st.logo_url as store_logo
        FROM public.seller_plan_payments spp
        INNER JOIN public.sellers_plan sp ON spp.seller_plan_id = sp.id
        INNER JOIN public.sellers s ON spp.seller_id = s.id
        LEFT JOIN public.stores st ON s.id = st.seller_id
        WHERE spp.status = 'pending'
        ORDER BY spp.submitted_at DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get status counts
    $statusSql = "
        SELECT 
            COUNT(*) as total_pending,
            COALESCE(SUM(amount), 0) as total_amount
        FROM public.seller_plan_payments
        WHERE status = 'pending'
    ";
    $statusStmt = $conn->prepare($statusSql);
    $statusStmt->execute();
    $statusCounts = $statusStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get plan breakdown
    $planSql = "
        SELECT 
            sp.plan,
            COUNT(*) as count,
            COALESCE(SUM(spp.amount), 0) as total
        FROM public.seller_plan_payments spp
        INNER JOIN public.sellers_plan sp ON spp.seller_plan_id = sp.id
        WHERE spp.status = 'pending'
        GROUP BY sp.plan
    ";
    $planStmt = $conn->prepare($planSql);
    $planStmt->execute();
    $planBreakdown = $planStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $subscriptions,
        'status_counts' => [
            'total_pending' => (int)($statusCounts['total_pending'] ?? 0),
            'total_amount' => floatval($statusCounts['total_amount'] ?? 0)
        ],
        'plan_breakdown' => $planBreakdown ?: []
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