<?php
header('Content-Type: application/json');

require '/var/www/html/connection/db_connection.php';

// ✅ Enable error logging
error_log("=== Update Order Status API Called ===");

try {
    // Handle both JSON and form-data input
    $order_id = 0;
    $status = '';
    $rider_id = 0;

    // Check if it's JSON content type
    if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
        $data = json_decode(file_get_contents("php://input"), true);
        $order_id = isset($data['order_id']) ? intval($data['order_id']) : 0;
        $status = isset($data['status']) ? $data['status'] : '';
        $rider_id = isset($data['rider_id']) ? intval($data['rider_id']) : 0;
    } else {
        // Regular form POST
        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $status = isset($_POST['status']) ? $_POST['status'] : '';
        $rider_id = isset($_POST['rider_id']) ? intval($_POST['rider_id']) : 0;
    }

    // ✅ Log received parameters
    error_log("Received - order_id: $order_id, status: $status, rider_id: $rider_id");

    if (!$order_id || !$status) {
        echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
        exit;
    }

    // Allowed statuses
    $allowed_status = ['pending', 'packed', 'shipped', 'delivered', 'locked', 'assigned', 'reassinged', 'complete', 'cancelled'];
    if (!in_array($status, $allowed_status)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
        exit;
    }

    // Check rider status BEFORE locking
    if ($status === 'locked') {
        
        error_log("Processing LOCK request for rider_id: $rider_id");
        
        // Require rider_id when locking
        if (!$rider_id) {
            error_log("ERROR: Missing rider_id");
            echo json_encode([
                'status' => 'error', 
                'message' => 'Rider ID is required to lock an order'
            ]);
            exit;
        }

        // Check rider's current status
        $riderStmt = $conn->prepare("SELECT id, status FROM riders WHERE id = :rider_id");
        $riderStmt->execute([':rider_id' => $rider_id]);
        $rider = $riderStmt->fetch(PDO::FETCH_ASSOC);

        if (!$rider) {
            error_log("ERROR: Rider not found - ID: $rider_id");
            echo json_encode([
                'status' => 'error',
                'message' => 'Rider not found'
            ]);
            exit;
        }

        // ✅ Log rider's current status
        error_log("Rider found - ID: {$rider['id']}, Status: {$rider['status']}");

        // Check if rider is busy
        if ($rider['status'] === 'busy') {
            error_log("BLOCKED: Rider is busy");
            echo json_encode([
                'status' => 'error',
                'message' => 'You are currently on a delivery. Complete it before accepting new orders.'
            ]);
            exit;
        }

        // Check if rider is offline
        if ($rider['status'] === 'offline') {
            error_log("BLOCKED: Rider is offline");
            echo json_encode([
                'status' => 'error',
                'message' => 'You are offline. Please go online to accept orders.'
            ]);
            exit;
        }

        error_log("Rider status OK - Proceeding to lock order");
        
        // All good - lock the order
        $stmt = $conn->prepare("UPDATE orders SET status = ?, locked_at = NOW() WHERE id = ?");
        $success = $stmt->execute([$status, $order_id]);
        
    } else {
        // For other statuses, just update status
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $success = $stmt->execute([$status, $order_id]);
    }

    if ($success && $stmt->rowCount() > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => "Order $order_id updated to $status"
        ]);
    } elseif ($success && $stmt->rowCount() === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => "Order not found or status unchanged"
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update order'
        ]);
    }

} catch (PDOException $e) {
    error_log("PDO Exception: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General Exception: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>