<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once '/var/www/html/connection/db_connection.php';

try {
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

    // Allowed statuses (match database constraint)
    $allowed_statuses = [
        "pending",
        "pending_payment",
        "packed",
        "ready_for_pickup",
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

    // Update query
    $sql = "UPDATE orders
            SET status = :status, updated_at = NOW()
            WHERE id = :order_id";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':status' => $status,
        ':order_id' => $order_id
    ]);

    $rowCount = $stmt->rowCount();

    if ($rowCount > 0) {
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

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}

$conn = null; // Close PDO connection
?>