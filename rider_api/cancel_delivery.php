<?php
header("Content-Type: application/json");

require_once "/var/www/html/connection/db_connection.php"; // Updated to match your path

try {
    // Handle both JSON and form-data input
    $delivery_id = null;
    $rider_id = null;

    // Check if it's JSON content type
    if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
        $data = json_decode(file_get_contents("php://input"), true);
        $delivery_id = $data['delivery_id'] ?? null;
        $rider_id = $data['rider_id'] ?? null;
    } else {
        // Regular form POST
        $delivery_id = $_POST['delivery_id'] ?? null;
        $rider_id = $_POST['rider_id'] ?? null;
    }

    if (!$delivery_id || !$rider_id) {
        echo json_encode([
            "success" => false,
            "message" => "Missing delivery_id or rider_id"
        ]);
        exit;
    }

    // Start transaction to ensure both queries succeed
    $conn->beginTransaction();

    try {
        // 1️⃣ Cancel the delivery
        $sqlOrder = "UPDATE order_deliveries
                     SET status = 'cancelled', cancelled_at = NOW()
                     WHERE id = ?";
        $stmtOrder = $conn->prepare($sqlOrder);
        $stmtOrder->execute([$delivery_id]);

        // Check if delivery was found and updated
        if ($stmtOrder->rowCount() === 0) {
            throw new Exception("Delivery not found or already cancelled");
        }

        // 2️⃣ Update rider status to online
        $sqlRider = "UPDATE riders SET status = 'online' WHERE id = ?";
        $stmtRider = $conn->prepare($sqlRider);
        $stmtRider->execute([$rider_id]);

        // Check if rider was found and updated
        if ($stmtRider->rowCount() === 0) {
            throw new Exception("Rider not found");
        }

        // Commit both updates
        $conn->commit();

        echo json_encode([
            "success" => true,
            "message" => "Order cancelled and rider status updated to online"
        ]);

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollBack();
        throw $e; // Re-throw to be caught by outer catch
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

// No need to explicitly close the connection with PDO
?>