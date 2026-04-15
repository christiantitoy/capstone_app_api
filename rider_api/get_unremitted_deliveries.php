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
            "status_updated" => false,
            "message" => "Rider is currently delivering, status unchanged",
            "has_unremitted" => false,
            "deliveries" => []
        ]);
        exit;
    }

    // Get yesterday's date at 00:00:00
    $yesterday = date('Y-m-d 00:00:00', strtotime('-1 day'));
    $today = date('Y-m-d 00:00:00');

    // Query to get unremitted deliveries (yesterday and before, not today)
    // FIXED: Using od.id as delivery_id since that's the actual delivery ID
    $unremittedSql = "
        SELECT DISTINCT
            od.id AS delivery_id,
            od.order_id,
            od.status,
            od.completed_at,
            re.total_amount
        FROM public.order_deliveries od
        INNER JOIN public.rider_earnings re ON od.id = re.delivery_id 
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

    // Format deliveries for response
    $formattedDeliveries = [];
    foreach ($unremittedDeliveries as $delivery) {
        $formattedDeliveries[] = [
            "delivery_id" => (int)$delivery['delivery_id'],
            "order_id" => (int)$delivery['order_id'],
            "status" => $delivery['status'],
            "completed_at" => $delivery['completed_at'],
            "total_amount" => floatval($delivery['total_amount'])
        ];
    }

    echo json_encode([
        "success" => true,
        "message" => $message,
        "rider_status" => $newStatus,
        "status_updated" => $statusUpdated,
        "has_unremitted" => $hasUnremittedDeliveries,
        "deliveries" => $formattedDeliveries
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