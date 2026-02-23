<?php
header('Content-Type: application/json');
require 'db_connection.php';

try {
    if (!isset($_POST['order_id']) || !isset($_POST['status'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
        exit;
    }

    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];

    // Allowed statuses
    $allowed_status = ['pending','packed','shipped','delivered','locked','assigned','reassinged','complete','cancelled'];
    if (!in_array($status, $allowed_status)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
        exit;
    }

    // If locking, also update locked_at
    if ($status === 'locked') {
        $stmt = $conn->prepare("UPDATE orders SET status = ?, locked_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $status, $order_id);
    } else {
        // For other statuses, just update status
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $order_id);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => "Order $order_id updated to $status"]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update order']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
