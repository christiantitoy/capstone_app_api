<?php
header("Content-Type: application/json");
require_once 'db_connection.php';

$order_id = $_POST['order_id'] ?? null;
$rider_id = $_POST['rider_id'] ?? null;

if (!$order_id || !$rider_id) {
    echo json_encode([
        "success" => false,
        "message" => "Missing parameters"
    ]);
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO order_deliveries (
        order_id,
        rider_id,
        status,
        assigned_at
    ) VALUES (?, ?, 'assigned', NOW())
");

$stmt->bind_param("ii", $order_id, $rider_id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Order delivery record created"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to create order delivery"
    ]);
}

$stmt->close();
$conn->close();
