<?php
header("Content-Type: application/json");

require_once "/var/www/html/connection/db_connection.php";

try {

    $delivery_id = null;
    $rider_id = null;
    $order_id = null;

    // Handle JSON or form-data
    if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
        $data = json_decode(file_get_contents("php://input"), true);
        $delivery_id = $data['delivery_id'] ?? null;
        $rider_id = $data['rider_id'] ?? null;
        $order_id = $data['order_id'] ?? null;
    } else {
        $delivery_id = $_POST['delivery_id'] ?? null;
        $rider_id = $_POST['rider_id'] ?? null;
        $order_id = $_POST['order_id'] ?? null;
    }

    if (!$delivery_id || !$rider_id || !$order_id) {
        echo json_encode([
            "success" => false,
            "message" => "Missing delivery_id, rider_id, or order_id"
        ]);
        exit;
    }

    $conn->beginTransaction();

    try {

        // 1️⃣ Cancel delivery
        $sqlDelivery = "UPDATE order_deliveries
                        SET status = 'cancelled',
                            cancelled_at = NOW()
                        WHERE id = ?";
        $stmtDelivery = $conn->prepare($sqlDelivery);
        $stmtDelivery->execute([$delivery_id]);

        if ($stmtDelivery->rowCount() === 0) {
            throw new Exception("Delivery not found or already cancelled");
        }

        // 2️⃣ Update rider status to online
        $sqlRider = "UPDATE riders
                     SET status = 'online'
                     WHERE id = ?";
        $stmtRider = $conn->prepare($sqlRider);
        $stmtRider->execute([$rider_id]);

        if ($stmtRider->rowCount() === 0) {
            throw new Exception("Rider not found");
        }

        // 3️⃣ Update order status to shipped
        $sqlOrder = "UPDATE orders
                     SET status = 'shipped'
                     WHERE id = ?";
        $stmtOrder = $conn->prepare($sqlOrder);
        $stmtOrder->execute([$order_id]);

        if ($stmtOrder->rowCount() === 0) {
            throw new Exception("Order not found");
        }

        $conn->commit();

        echo json_encode([
            "success" => true,
            "message" => "Delivery cancelled, rider set to online, and order marked as shipped"
        ]);

    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }

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