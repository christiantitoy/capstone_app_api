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
    $conn->beginTransaction();

    try {
        // First, update the order delivery status
        $sql1 = "
            UPDATE order_deliveries
            SET status = 'completed',
                completed_at = NOW()
            WHERE id = ?
        ";

        $stmt1 = $conn->prepare($sql1);
        $success1 = $stmt1->execute([$delivery_id]);

        if (!$success1) {
            throw new Exception("Failed to update order delivery status");
        }

        // Check if delivery was found and updated
        if ($stmt1->rowCount() === 0) {
            throw new Exception("Delivery not found or already completed");
        }

        // Second, update the rider status to online
        $sql2 = "
            UPDATE riders
            SET status = 'online'
            WHERE id = ?
        ";

        $stmt2 = $conn->prepare($sql2);
        $success2 = $stmt2->execute([$rider_id]);

        if (!$success2) {
            throw new Exception("Failed to update rider status");
        }

        // Check if rider was found and updated
        if ($stmt2->rowCount() === 0) {
            throw new Exception("Rider not found");
        }

        // Commit transaction
        $conn->commit();

        echo json_encode([
            "success" => true,
            "message" => "Order marked as delivered and rider status updated to online"
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
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