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
        
        // Also check JSON body
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

    // 1️⃣ Get total deliveries count
    $sql1 = "
        SELECT COUNT(*) as total_deliveries
        FROM order_deliveries
        WHERE rider_id = ? AND status = 'completed'
    ";
    
    $stmt1 = $conn->prepare($sql1);
    $stmt1->execute([$rider_id]);
    $total_deliveries = $stmt1->fetch(PDO::FETCH_ASSOC)['total_deliveries'];

    // 2️⃣ Get total shipping_fee and total_amount from completed deliveries
    $sql2 = "
        SELECT 
            COALESCE(SUM(o.shipping_fee), 0) as total_shipping_fee,
            COALESCE(SUM(o.total_amount), 0) as total_amount
        FROM order_deliveries od
        INNER JOIN orders o ON od.order_id = o.id
        WHERE od.rider_id = ? AND od.status = 'completed'
    ";
    
    $stmt2 = $conn->prepare($sql2);
    $stmt2->execute([$rider_id]);
    $totals = $stmt2->fetch(PDO::FETCH_ASSOC);
    
    $total_shipping_fee = floatval($totals['total_shipping_fee']);
    $total_amount = floatval($totals['total_amount']);

    // 3️⃣ Get pending shipping_fee (from non-completed deliveries)
    $sql3 = "
        SELECT COALESCE(SUM(o.shipping_fee), 0) as pending_shipping_fee
        FROM order_deliveries od
        INNER JOIN orders o ON od.order_id = o.id
        WHERE od.rider_id = ? 
        AND od.status IN ('assigned', 'picked_up', 'delivering')
    ";
    
    $stmt3 = $conn->prepare($sql3);
    $stmt3->execute([$rider_id]);
    $pending = $stmt3->fetch(PDO::FETCH_ASSOC);
    
    $pending_shipping_fee = floatval($pending['pending_shipping_fee']);

    // 4️⃣ Get recent earnings history (optional but useful)
    $sql4 = "
        SELECT 
            od.id as delivery_id,
            od.order_id,
            o.shipping_fee,
            o.total_amount,
            od.completed_at as earned_at
        FROM order_deliveries od
        INNER JOIN orders o ON od.order_id = o.id
        WHERE od.rider_id = ? AND od.status = 'completed'
        ORDER BY od.completed_at DESC
        LIMIT 10
    ";
    
    $stmt4 = $conn->prepare($sql4);
    $stmt4->execute([$rider_id]);
    $recent_earnings = $stmt4->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "rider_id" => $rider_id,
        "earnings_summary" => [
            "total_deliveries" => intval($total_deliveries),
            "total_shipping_fee" => $total_shipping_fee,
            "total_amount" => $total_amount,
            "pending_shipping_fee" => $pending_shipping_fee
        ],
        "recent_earnings" => $recent_earnings
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