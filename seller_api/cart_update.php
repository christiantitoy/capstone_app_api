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
    
    if (!isset($data['cart_item_id']) || !isset($data['quantity'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'cart_item_id and quantity are required'
        ]);
        exit;
    }
    
    $cart_item_id = intval($data['cart_item_id']);
    $quantity = intval($data['quantity']);
    
    if ($quantity <= 0) {
        // If quantity is 0 or negative, remove the item
        $delete_sql = "DELETE FROM cart_items WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $cart_item_id);
        
        if ($delete_stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Item removed from cart'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to remove item'
            ]);
        }
    } else {
        // Update quantity
        $update_sql = "UPDATE cart_items 
                      SET quantity = ?, 
                          updated_at = CURRENT_TIMESTAMP 
                      WHERE id = ?";
        
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $quantity, $cart_item_id);
        
        if ($update_stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Cart updated',
                'new_quantity' => $quantity
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to update cart'
            ]);
        }
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>