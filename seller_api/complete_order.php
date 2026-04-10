<?php
header('Content-Type: application/json');

require_once '/var/www/html/connection/db_connection.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid JSON data'
        ]);
        exit;
    }
    
    if (!isset($data['order_id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'order_id is required'
        ]);
        exit;
    }
    
    $order_id = intval($data['order_id']);
    
    // Check if order exists and is delivered
    $check_sql = "SELECT status FROM orders WHERE id = :order_id";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->execute([':order_id' => $order_id]);
    $order = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Order not found'
        ]);
        exit;
    }
    
    // Only allow completing delivered orders
    if ($order['status'] !== 'delivered') {
        echo json_encode([
            'status' => 'error',
            'message' => 'Only delivered orders can be marked as complete'
        ]);
        exit;
    }
    
    // Update order status to complete
    $update_sql = "UPDATE orders 
                   SET status = 'complete',
                       updated_at = CURRENT_TIMESTAMP
                   WHERE id = :order_id";
    
    $update_stmt = $conn->prepare($update_sql);
    $result = $update_stmt->execute([':order_id' => $order_id]);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Order marked as complete',
            'order_id' => $order_id
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to complete order'
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

$conn = null;
?>