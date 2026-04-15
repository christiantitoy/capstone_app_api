<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once '/var/www/html/connection/db_connection.php';

// Get POST parameters - handling both form-data and JSON input
$rider_id = 0;
$status = '';

// Check if it's JSON content type
if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
    $data = json_decode(file_get_contents("php://input"), true);
    $rider_id = isset($data['rider_id']) ? intval($data['rider_id']) : 0;
    $status = isset($data['status']) ? $data['status'] : '';
} else {
    // Regular form POST
    $rider_id = isset($_POST['rider_id']) ? intval($_POST['rider_id']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';
}

if ($rider_id <= 0 || !in_array($status, ['online', 'offline'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid rider_id or status",
        "has_unremitted_delivery" => false
    ]);
    exit;
}

try {
    // Start transaction
    $conn->beginTransaction();

    // Check if rider has unremitted deliveries from previous days
    $today = date('Y-m-d 00:00:00');
    
    $unremittedSql = "
        SELECT COUNT(*) as unremitted_count
        FROM public.order_deliveries od
        INNER JOIN public.rider_earnings re ON od.id = re.delivery_id 
            AND od.rider_id = re.rider_id
        WHERE od.rider_id = ? 
        AND re.is_remitted = false
        AND od.completed_at IS NOT NULL
        AND od.completed_at < ?
    ";
    
    $stmt = $conn->prepare($unremittedSql);
    $stmt->execute([$rider_id, $today]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $hasUnremittedDelivery = $result['unremitted_count'] > 0;

    // If trying to go online but has unremitted deliveries, prevent it
    if ($status === 'online' && $hasUnremittedDelivery) {
        $conn->rollBack();
        echo json_encode([
            "status" => "error",
            "message" => "Cannot go online. You have unremitted deliveries from previous days.",
            "has_unremitted_delivery" => true
        ]);
        exit;
    }

    // Prepare and execute update query using PDO
    $stmt = $conn->prepare("UPDATE riders SET status = ? WHERE id = ?");
    $success = $stmt->execute([$status, $rider_id]);

    // Commit transaction
    $conn->commit();

    if ($success) {
        // Check if any row was actually affected
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                "status" => "success",
                "message" => "Rider status updated successfully",
                "has_unremitted_delivery" => $hasUnremittedDelivery
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Rider not found or status unchanged",
                "has_unremitted_delivery" => $hasUnremittedDelivery
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to update status",
            "has_unremitted_delivery" => $hasUnremittedDelivery
        ]);
    }
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage(),
        "has_unremitted_delivery" => false
    ]);
}

// No need to explicitly close the connection with PDO
?>