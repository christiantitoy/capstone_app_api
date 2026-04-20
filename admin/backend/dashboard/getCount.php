<?php
// /admin/backend/dashboard/getCount.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

try {
    // Count total buyers
    $stmt = $conn->prepare("SELECT COUNT(*) AS total_buyers FROM buyers");
    $stmt->execute();
    $totalBuyers = $stmt->fetch(PDO::FETCH_ASSOC)['total_buyers'];

    // Count total sellers
    $stmt = $conn->prepare("SELECT COUNT(*) AS total_sellers FROM sellers");
    $stmt->execute();
    $totalSellers = $stmt->fetch(PDO::FETCH_ASSOC)['total_sellers'];

    // Count total riders
    $stmt = $conn->prepare("SELECT COUNT(*) AS total_riders FROM riders");
    $stmt->execute();
    $totalRiders = $stmt->fetch(PDO::FETCH_ASSOC)['total_riders'];

    // Count total products (approved only)
    $stmt = $conn->prepare("SELECT COUNT(*) AS total_products FROM items WHERE status = 'approved'");
    $stmt->execute();
    $totalProducts = $stmt->fetch(PDO::FETCH_ASSOC)['total_products'];

    // Count total orders
    $stmt = $conn->prepare("SELECT COUNT(*) AS total_orders FROM orders");
    $stmt->execute();
    $totalOrders = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];

    // Count orders by status
    $stmt = $conn->prepare("
        SELECT status, COUNT(*) AS count 
        FROM orders 
        GROUP BY status
    ");
    $stmt->execute();
    $ordersByStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format orders by status into key-value
    $orderStatusCounts = [];
    foreach ($ordersByStatus as $row) {
        $orderStatusCounts[$row['status']] = (int) $row['count'];
    }

    // Count confirmed vs unconfirmed sellers
    $stmt = $conn->prepare("
        SELECT 
            SUM(CASE WHEN is_confirmed = true THEN 1 ELSE 0 END) AS confirmed_sellers,
            SUM(CASE WHEN is_confirmed = false THEN 1 ELSE 0 END) AS unconfirmed_sellers
        FROM sellers
    ");
    $stmt->execute();
    $sellerConfirmation = $stmt->fetch(PDO::FETCH_ASSOC);

    // Count sellers by plan
    $stmt = $conn->prepare("
        SELECT seller_plan, COUNT(*) AS count 
        FROM sellers 
        GROUP BY seller_plan
    ");
    $stmt->execute();
    $sellersByPlan = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sellerPlanCounts = [];
    foreach ($sellersByPlan as $row) {
        $sellerPlanCounts[$row['seller_plan']] = (int) $row['count'];
    }

    // Count buyers who have placed at least one order
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT buyer_id) AS active_buyers 
        FROM orders
    ");
    $stmt->execute();
    $activeBuyers = $stmt->fetch(PDO::FETCH_ASSOC)['active_buyers'];

    // Count riders by status
    $stmt = $conn->prepare("
        SELECT status, COUNT(*) AS count 
        FROM riders 
        GROUP BY status
    ");
    $stmt->execute();
    $ridersByStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $riderStatusCounts = [];
    foreach ($ridersByStatus as $row) {
        $riderStatusCounts[$row['status']] = (int) $row['count'];
    }

    // Count riders by verification status
    $stmt = $conn->prepare("
        SELECT verification_status, COUNT(*) AS count 
        FROM riders 
        GROUP BY verification_status
    ");
    $stmt->execute();
    $ridersByVerification = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $riderVerificationCounts = [];
    foreach ($ridersByVerification as $row) {
        $riderVerificationCounts[$row['verification_status']] = (int) $row['count'];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'buyers' => [
                'total'  => (int) $totalBuyers,
                'active' => (int) $activeBuyers,   // buyers with at least 1 order
            ],
            'sellers' => [
                'total'       => (int) $totalSellers,
                'confirmed'   => (int) $sellerConfirmation['confirmed_sellers'],
                'unconfirmed' => (int) $sellerConfirmation['unconfirmed_sellers'],
                'by_plan'     => $sellerPlanCounts,
            ],
            'riders' => [
                'total' => (int) $totalRiders,
                'by_status' => $riderStatusCounts,
                'by_verification' => $riderVerificationCounts,
            ],
            'products' => [
                'total_approved' => (int) $totalProducts,
            ],
            'orders' => [
                'total'     => (int) $totalOrders,
                'by_status' => $orderStatusCounts,
            ],
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