<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'db_connection.php';

if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]);
    exit;
}

// Get inputs
$order_id = $_POST['order_id'] ?? $_GET['order_id'] ?? null;
$status   = $_POST['status'] ?? $_GET['status'] ?? null;

if (!$order_id || !$status) {
    echo json_encode([
        "status" => "error",
        "message" => "order_id and status are required"
    ]);
    exit;
}

// Allowed statuses (match ENUM)
$allowed_statuses = [
    "pending",
    "packed",
    "shipped",
    "delivered",
    "locked",
    "assigned",
    "reassigned",
    "complete",
    "cancelled"
];

if (!in_array($status, $allowed_statuses)) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid status value"
    ]);
    exit;
}

$order_id = intval($order_id);
$status   = $conn->real_escape_string($status);

// Update query
$sql = "UPDATE orders 
        SET status = '$status', updated_at = NOW()
        WHERE id = $order_id";

if ($conn->query($sql)) {

    if ($conn->affected_rows > 0) {
        echo json_encode([
            "status" => "success",
            "message" => "Order status updated successfully",
            "order_id" => $order_id,
            "new_status" => $status
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Order not found or status already the same"
        ]);
    }

} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to update order",
        "error" => $conn->error
    ]);
}

$conn->close();
?>
