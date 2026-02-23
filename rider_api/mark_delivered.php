<?php
header("Content-Type: application/json");
require_once "db_connection.php";

$delivery_id = $_POST['delivery_id'] ?? null;
$rider_id = $_POST['rider_id'] ?? null; // Get rider_id from the request

if (!$delivery_id) {
    echo json_encode([
        "success" => false,
        "message" => "Missing delivery_id"
    ]);
    exit;
}

if (!$rider_id) {
    echo json_encode([
        "success" => false,
        "message" => "Missing rider_id"
    ]);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // First, update the order delivery status
    $sql1 = "
        UPDATE order_deliveries
        SET status = 'completed',
            completed_at = NOW()
        WHERE id = ?
    ";
    
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("i", $delivery_id);
    
    if (!$stmt1->execute()) {
        throw new Exception("Failed to update order delivery status");
    }
    $stmt1->close();
    
    // Second, update the rider status to online
    $sql2 = "
        UPDATE riders
        SET status = 'online'
        WHERE id = ?
    ";
    
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i", $rider_id);
    
    if (!$stmt2->execute()) {
        throw new Exception("Failed to update rider status");
    }
    $stmt2->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        "success" => true,
        "message" => "Order marked as delivered and rider status updated to online"
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}

$conn->close();
?>