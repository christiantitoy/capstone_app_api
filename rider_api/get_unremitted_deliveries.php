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

    // Start transaction
    $conn->beginTransaction();

    // Check current rider status
    $checkRiderSql = "
        SELECT id, status 
        FROM public.riders 
        WHERE id = ?
        FOR UPDATE
    ";
    
    $stmt = $conn->prepare($checkRiderSql);
    $stmt->execute([$rider_id]);
    $rider = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rider) {
        $conn->rollBack();
        echo json_encode([
            "success" => false,
            "message" => "Rider not found"
        ]);
        exit;
    }

    // If rider is busy (delivering), don't change status
    if ($rider['status'] === 'delivering') {
        $conn->rollBack();
        echo json_encode([
            "success" => true,
            "rider_id" => (int)$rider_id,
            "rider_status" => $rider['status'],
            "message" => "Rider is currently delivering, status unchanged",
            "unremitted_deliveries" => [],
            "has_unremitted_deliveries" => null
        ]);
        exit;
    }

    // Get yesterday's date at 00:00:00
    $yesterday = date('Y-m-d 00:00:00', strtotime('-1 day'));
    $today = date('Y-m-d 00:00:00');

    // Query to get unremitted deliveries (yesterday and before, not today)
    $unremittedSql = "
        SELECT DISTINCT
            od.id AS delivery_id,
            od.order_id,
            od.status AS delivery_status,
            od.completed_at,
            re.is_remitted,
            re.total_amount,
            re.created_at AS earning_created_at
        FROM public.order_deliveries od
        INNER JOIN public.rider_earnings re ON od.delivery_id = re.delivery_id 
            AND od.rider_id = re.rider_id
        WHERE od.rider_id = ? 
        AND re.is_remitted = false
        AND od.completed_at IS NOT NULL
        AND od.completed_at < ?
        ORDER BY od.completed_at DESC
    ";

    $stmt = $conn->prepare($unremittedSql);
    $stmt->execute([$rider_id, $today]);
    $unremittedDeliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $hasUnremittedDeliveries = count($unremittedDeliveries) > 0;
    
    // Determine new status based on unremitted deliveries
    $newStatus = $hasUnremittedDeliveries ? 'offline' : 'online';
    $statusUpdated = false;
    $message = '';

    // Update rider status if it's different from current status
    if ($rider['status'] !== $newStatus) {
        $updateSql = "
            UPDATE public.riders 
            SET status = ? 
            WHERE id = ?
        ";
        $stmt = $conn->prepare($updateSql);
        $stmt->execute([$newStatus, $rider_id]);
        $statusUpdated = true;
        $message = $hasUnremittedDeliveries 
            ? "Rider has unremitted deliveries from previous days. Status set to offline." 
            : "No unremitted deliveries from previous days. Status set to online.";
    } else {
        $message = $hasUnremittedDeliveries 
            ? "Rider already offline due to unremitted deliveries." 
            : "Rider already online with no unremitted deliveries.";
    }

    // Commit transaction
    $conn->commit();

    // Calculate summary
    $totalUnremittedAmount = 0;
    $deliveryCount = count($unremittedDeliveries);
    
    foreach ($unremittedDeliveries as $delivery) {
        $totalUnremittedAmount += floatval($delivery['total_amount']);
    }

    echo json_encode([
        "success" => true,
        "rider_id" => (int)$rider_id,
        "previous_status" => $rider['status'],
        "current_status" => $newStatus,
        "status_updated" => $statusUpdated,
        "message" => $message,
        "has_unremitted_deliveries" => $hasUnremittedDeliveries,
        "summary" => [
            "total_unremitted_deliveries" => $deliveryCount,
            "total_unremitted_amount" => round($totalUnremittedAmount, 2)
        ],
        "unremitted_deliveries" => $unremittedDeliveries,
        "check_period" => [
            "yesterday" => $yesterday,
            "today_start" => $today,
            "description" => "Checking deliveries completed before today"
        ]
    ]);

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>