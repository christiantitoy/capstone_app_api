<?php
header('Content-Type: application/json');

require '/var/www/html/connection/db_connection.php'; // Updated to match your path

try {
    // Handle both JSON and form-data input
    $order_id = 0;
    $status = '';

    // Check if it's JSON content type
    if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
        $data = json_decode(file_get_contents("php://input"), true);
        $order_id = isset($data['order_id']) ? intval($data['order_id']) : 0;
        $status = isset($data['status']) ? $data['status'] : '';
    } else {
        // Regular form POST
        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $status = isset($_POST['status']) ? $_POST['status'] : '';
    }

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

    // If locking, also update locked_at
    if ($status === 'locked') {
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
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>