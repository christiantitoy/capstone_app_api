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

        // 1️⃣ Update order_deliveries to completed
        $sql1 = "
            UPDATE order_deliveries
            SET status = 'completed',
                completed_at = NOW()
            WHERE id = ?
        ";

        $stmt1 = $conn->prepare($sql1);
        $stmt1->execute([$delivery_id]);

        if ($stmt1->rowCount() === 0) {
            throw new Exception("Delivery not found or already completed");
        }

        // 2️⃣ Update rider status to online
        $sql2 = "
            UPDATE riders
            SET status = 'online'
            WHERE id = ?
        ";

        $stmt2 = $conn->prepare($sql2);
        $stmt2->execute([$rider_id]);

        if ($stmt2->rowCount() === 0) {
            throw new Exception("Rider not found");
        }

        // 3️⃣ Update orders table to delivered
        $sql3 = "
            UPDATE orders
            SET status = 'delivered'
            WHERE id = ?
        ";

        $stmt3 = $conn->prepare($sql3);
        $stmt3->execute([$order_id]);

        if ($stmt3->rowCount() === 0) {
            throw new Exception("Order not found");
        }

        $conn->commit();

        echo json_encode([
            "success" => true,
            "message" => "Order marked as delivered and rider status updated to online"
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