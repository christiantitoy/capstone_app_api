<?php
header('Content-Type: application/json');
require 'db_connection.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid JSON data'
        ]);
        exit;
    }
    
    if (!isset($data['cart_item_id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'cart_item_id is required'
        ]);
        exit;
    }
    
    $cart_item_id = intval($data['cart_item_id']);
    
    $sql = "DELETE FROM cart_items WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cart_item_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Item removed from cart'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Item not found in cart'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to remove item'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>