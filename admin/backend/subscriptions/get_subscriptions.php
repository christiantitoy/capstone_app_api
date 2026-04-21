<?php
// /admin/backend/subscriptions/get_subscriptions.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

try {
    // First, let's check what status values exist
    $checkSql = "SELECT DISTINCT status FROM seller_plan_payments";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->execute();
    $statuses = $checkStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get pending seller plan payments with related information
    // Using LEFT JOIN in case some relations are missing
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
            COALESCE(sp.plan, 'bronze') as plan,
            COALESCE(sp.billing, 'lifetime') as billing,
            sp.start_date,
            sp.end_date,
            COALESCE(sp.status, 'pending') as plan_status,
            COALESCE(s.full_name, 'Unknown') as seller_name,
            s.email as seller_email,
            st.store_name,
            st.logo_url as store_logo
        FROM seller_plan_payments spp
        LEFT JOIN sellers_plan sp ON spp.seller_plan_id = sp.id
        LEFT JOIN sellers s ON spp.seller_id = s.id
        LEFT JOIN stores st ON s.id = st.seller_id
        WHERE LOWER(spp.status) = 'pending'
           OR spp.status = 'pending'
           OR spp.status LIKE '%pending%'
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
        FROM seller_plan_payments
        WHERE LOWER(status) = 'pending'
           OR status = 'pending'
           OR status LIKE '%pending%'
    ";
    $statusStmt = $conn->prepare($statusSql);
    $statusStmt->execute();
    $statusCounts = $statusStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get plan breakdown
    $planSql = "
        SELECT 
            COALESCE(sp.plan, 'bronze') as plan,
            COUNT(*) as count,
            COALESCE(SUM(spp.amount), 0) as total
        FROM seller_plan_payments spp
        LEFT JOIN sellers_plan sp ON spp.seller_plan_id = sp.id
        WHERE LOWER(spp.status) = 'pending'
           OR spp.status = 'pending'
           OR spp.status LIKE '%pending%'
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
        'plan_breakdown' => $planBreakdown ?: [],
        'debug_statuses' => $statuses
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