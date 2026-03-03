<?php
header("Content-Type: application/json");

require_once '/var/www/html/connection/db_connection.php';

try {

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
        ) VALUES (
            :order_id,
            :rider_id,
            'assigned',
            NOW()
        )
    ");

    $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $stmt->bindParam(':rider_id', $rider_id, PDO::PARAM_INT);

    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Order delivery record created"
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "success" => false,
        "message" => "Failed to create order delivery",
        "error" => $e->getMessage() // remove in production
    ]);

}

$conn = null; // Close connection