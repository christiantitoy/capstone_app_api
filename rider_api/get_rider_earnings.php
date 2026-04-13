<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

require_once "/var/www/html/connection/db_connection.php";

try {
    // Get rider_id from either GET or POST
    $rider_id = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $rider_id = $_GET['rider_id'] ?? null;
    } else {
        $rider_id = $_POST['rider_id'] ?? null;
        
        if (!$rider_id) {
            $data = json_decode(file_get_contents("php://input"), true);
            $rider_id = $data['rider_id'] ?? null;
        }
    }

    if (!$rider_id || !is_numeric($rider_id)) {
        echo json_encode([
            "success" => false,
            "message" => "Valid rider_id is required"
        ]);
        exit;
    }

    $rider_id = intval($rider_id);

    // 1️⃣ Get total shipping_fee and total_amount from ALL completed deliveries (All time)
    $sql1 = "
        SELECT 
            COALESCE(SUM(o.shipping_fee), 0) as total_shipping_fee,
            COALESCE(SUM(o.total_amount), 0) as total_amount
        FROM order_deliveries od
        INNER JOIN orders o ON od.order_id = o.id
        WHERE od.rider_id = ? AND od.status = 'completed'
    ";
    
    $stmt1 = $conn->prepare($sql1);
    $stmt1->execute([$rider_id]);
    $allTimeTotals = $stmt1->fetch(PDO::FETCH_ASSOC);

    // 2️⃣ Get TODAY's earnings
    $sql2 = "
        SELECT 
            COUNT(*) as total_today_deliveries,
            COALESCE(SUM(o.shipping_fee), 0) as today_shipping_fee,
            COALESCE(SUM(o.total_amount), 0) as today_total_amount
        FROM order_deliveries od
        INNER JOIN orders o ON od.order_id = o.id
        WHERE od.rider_id = ? 
        AND od.status = 'completed'
        AND DATE(od.completed_at) = CURDATE()
    ";
    
    $stmt2 = $conn->prepare($sql2);
    $stmt2->execute([$rider_id]);
    $todayTotals = $stmt2->fetch(PDO::FETCH_ASSOC);

    // 3️⃣ Get TODAY's pending shipping_fee (from non-completed deliveries)
    $sql3 = "
        SELECT COALESCE(SUM(o.shipping_fee), 0) as today_pending_shipping_fee
        FROM order_deliveries od
        INNER JOIN orders o ON od.order_id = o.id
        WHERE od.rider_id = ? 
        AND od.status IN ('assigned', 'picked_up', 'delivering')
        AND DATE(od.created_at) = CURDATE()
    ";
    
    $stmt3 = $conn->prepare($sql3);
    $stmt3->execute([$rider_id]);
    $todayPending = $stmt3->fetch(PDO::FETCH_ASSOC);

    // 4️⃣ Get recent earnings history with buyer details
    $sql4 = "
        SELECT 
            (SELECT COUNT(*) FROM order_deliveries WHERE rider_id = ? AND status = 'completed') as total_all_deliveries,
            od.id as delivery_id,
            od.order_id,
            od.status as order_status,
            o.shipping_fee,
            o.total_amount,
            od.completed_at as earned_at,
            a.recipient_name,
            a.full_address as recipient_address
        FROM order_deliveries od
        INNER JOIN orders o ON od.order_id = o.id
        LEFT JOIN address a ON o.address_id = a.id
        WHERE od.rider_id = ? AND od.status = 'completed'
        ORDER BY od.completed_at DESC
        LIMIT 10
    ";
    
    $stmt4 = $conn->prepare($sql4);
    $stmt4->execute([$rider_id, $rider_id]);
    $recentEarningsRaw = $stmt4->fetchAll(PDO::FETCH_ASSOC);

    // Format recent earnings with total deliveries count
    $totalAllDeliveries = 0;
    $recentEarnings = [];
    
    if (!empty($recentEarningsRaw)) {
        $totalAllDeliveries = intval($recentEarningsRaw[0]['total_all_deliveries']);
        
        foreach ($recentEarningsRaw as $row) {
            $recentEarnings[] = [
                "totalAllDeliveries" => $totalAllDeliveries,
                "orderStatus" => $row['order_status'],
                "recipientName" => $row['recipient_name'] ?? "Unknown",
                "recipientAddress" => $row['recipient_address'] ?? "No address",
                "delivery_id" => intval($row['delivery_id']),
                "order_id" => intval($row['order_id']),
                "shipping_fee" => $row['shipping_fee'],
                "total_amount" => $row['total_amount'],
                "earned_at" => $row['earned_at']
            ];
        }
    }

    // Build today's earnings object
    $todayEarnings = [
        "totalTodayDeliveries" => intval($todayTotals['total_today_deliveries']),
        "totalTodayShippingFee" => $todayTotals['today_shipping_fee'],
        "todayTotalAmount" => $todayTotals['today_total_amount'],
        "todayPending_shipping_fee" => floatval($todayPending['today_pending_shipping_fee']),
        "earned_at" => date('Y-m-d')
    ];

    // Build earnings summary (all time)
    $earningsSummary = [
        "total_shipping_fee" => floatval($allTimeTotals['total_shipping_fee']),
        "total_amount" => floatval($allTimeTotals['total_amount'])
    ];

    echo json_encode([
        "success" => true,
        "rider_id" => $rider_id,
        "earnings_summary" => $earningsSummary,
        "today_earnings" => $todayEarnings,
        "recent_earnings" => $recentEarnings
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>