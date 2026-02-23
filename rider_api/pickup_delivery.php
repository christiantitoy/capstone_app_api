<?php
header("Content-Type: application/json");
require_once "db_connection.php";

$delivery_id = $_POST['delivery_id'] ?? null;

if (!$delivery_id) {
    echo json_encode([
        "success" => false,
        "message" => "Missing delivery_id"
    ]);
    exit;
}

$sql = "
    UPDATE order_deliveries
    SET 
        status = 'picked_up',
        picked_up_at = NOW()
    WHERE id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $delivery_id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Order picked up successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to update status"
    ]);
}

$stmt->close();
$conn->close();
