<?php
header("Content-Type: application/json");

require_once "/var/www/html/connection/db_connection.php"; // Updated to match your path

try {
    // Handle both JSON and form-data input
    $delivery_id = null;

    // Check if it's JSON content type
    if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
        $data = json_decode(file_get_contents("php://input"), true);
        $delivery_id = $data['delivery_id'] ?? null;
    } else {
        // Regular form POST
        $delivery_id = $_POST['delivery_id'] ?? null;
    }

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
    $success = $stmt->execute([$delivery_id]);

    if ($success) {
        // Check if any row was actually updated
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                "success" => true,
                "message" => "Order picked up successfully"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Delivery not found or already picked up"
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Failed to update status"
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}

// No need to explicitly close the connection with PDO
?>