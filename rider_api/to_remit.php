<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

require_once "/var/www/html/connection/db_connection.php";

try {
    // Get rider_id from query parameter or POST body
    $rider_id = $_GET['rider_id'] ?? $_POST['rider_id'] ?? null;

    if (!$rider_id) {
        echo json_encode([
            "success" => false,
            "message" => "Missing rider_id parameter"
        ]);
        exit;
    }

    // Validate rider_id is numeric
    if (!is_numeric($rider_id)) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid rider_id format"
        ]);
        exit;
    }

    // Query to get unremitted earnings with order details
    $sql = "
        SELECT 
            re.id AS earning_id,
            re.rider_id,
            re.order_id,
            re.delivery_id,
            re.shipping_fee,
            re.total_amount,
            re.created_at AS earning_created_at,
            re.is_remitted,
            o.subtotal,
            o.platform_fee,
            o.payment_method,
            o.status AS order_status,
            o.created_at AS order_created_at
        FROM public.rider_earnings re
        INNER JOIN public.orders o ON re.order_id = o.id
        WHERE re.rider_id = ? 
        AND re.is_remitted = false
        ORDER BY re.created_at DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$rider_id]);

    $earnings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate summary totals
    $total_unremitted = 0;
    $total_cod_amount = 0;
    $total_orders = count($earnings);
    
    foreach ($earnings as $earning) {
        $total_unremitted += floatval($earning['total_amount']);
        // Calculate COD amount (subtotal + platform_fee)
        $total_cod_amount += floatval($earning['subtotal']) + floatval($earning['platform_fee']);
    }

    echo json_encode([
        "success" => true,
        "rider_id" => (int)$rider_id,
        "summary" => [
            "total_unremitted_orders" => $total_orders,
            "total_unremitted_amount" => round($total_unremitted, 2),
            "total_cod_amount" => round($total_cod_amount, 2)
        ],
        "earnings" => $earnings
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