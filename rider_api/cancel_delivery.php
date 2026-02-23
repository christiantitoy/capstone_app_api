<?php
header("Content-Type: application/json");
require_once "db_connection.php";

$delivery_id = $_POST['delivery_id'] ?? null;
$rider_id    = $_POST['rider_id'] ?? null;

if (!$delivery_id || !$rider_id) {
    echo json_encode([
        "success" => false,
        "message" => "Missing delivery_id or rider_id"
    ]);
    exit;
}

$conn->begin_transaction(); // Start transaction to ensure both queries succeed

try {
    // 1️⃣ Cancel the order
    $sqlOrder = "UPDATE order_deliveries
                 SET status = 'cancelled', cancelled_at = NOW()
                 WHERE id = ?";
    $stmtOrder = $conn->prepare($sqlOrder);
    $stmtOrder->bind_param("i", $delivery_id);
    $stmtOrder->execute();
    $stmtOrder->close();

    // 2️⃣ Update rider status to online
    $sqlRider = "UPDATE riders SET status = 'online' WHERE id = ?";
    $stmtRider = $conn->prepare($sqlRider);
    $stmtRider->bind_param("i", $rider_id);
    $stmtRider->execute();
    $stmtRider->close();

    $conn->commit(); // Commit both updates

    echo json_encode([
        "success" => true,
        "message" => "Order cancelled and rider status updated to online"
    ]);
} catch (Exception $e) {
    $conn->rollback(); // Rollback on error
    echo json_encode([
        "success" => false,
        "message" => "Failed to cancel order or update rider status",
        "error" => $e->getMessage()
    ]);
}

$conn->close();
