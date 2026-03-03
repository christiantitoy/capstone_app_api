<?php
header("Content-Type: application/json");

require_once "/var/www/html/connection/db_connection.php"; // Updated to match your path

try {
    // Handle both JSON and form-data input
    $rider_id = null;
    $status = null;

    // Check if it's JSON content type
    if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
        $data = json_decode(file_get_contents("php://input"), true);
        $rider_id = $data['rider_id'] ?? null;
        $status = $data['status'] ?? null;
    } else {
        // Regular form POST
        $rider_id = $_POST['rider_id'] ?? null;
        $status = $_POST['status'] ?? null;
    }

    if (!$rider_id || !$status) {
        echo json_encode([
            "status" => "error",
            "message" => "Missing parameters"
        ]);
        exit;
    }

    // Optional: Validate status values if needed
    $allowed_statuses = ['online', 'offline', 'delivering']; // Adjust as needed
    if (!in_array($status, $allowed_statuses)) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid status value"
        ]);
        exit;
    }

    // Prepare and execute update query using PDO
    $stmt = $conn->prepare("UPDATE riders SET status = ? WHERE id = ?");
    $success = $stmt->execute([$status, $rider_id]);

    if ($success) {
        // Check if any row was actually updated
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                "status" => "success",
                "message" => "Rider status updated"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Rider not found or status unchanged"
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to update rider status"
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
        "message" => "Error: " . $e->getMessage()
    ]);
}

// No need to explicitly close the connection with PDO
?>