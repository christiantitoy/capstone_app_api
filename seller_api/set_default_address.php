<?php
// File: set_default_address.php
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
    
    if (!isset($data['address_id']) || !isset($data['buyer_id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'address_id and buyer_id are required'
        ]);
        exit;
    }
    
    $address_id = intval($data['address_id']);
    $buyer_id = intval($data['buyer_id']);
    
    // Check if address exists and belongs to buyer
    $check_sql = "SELECT id FROM buyer_addresses WHERE id = ? AND buyer_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $address_id, $buyer_id);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Address not found or does not belong to user'
        ]);
        exit;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First, unset all defaults for this buyer
        $unset_sql = "UPDATE buyer_addresses SET is_default = 0 WHERE buyer_id = ?";
        $unset_stmt = $conn->prepare($unset_sql);
        $unset_stmt->bind_param("i", $buyer_id);
        $unset_stmt->execute();
        
        // Then set the selected address as default
        $set_sql = "UPDATE buyer_addresses SET is_default = 1, updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ? AND buyer_id = ?";
        $set_stmt = $conn->prepare($set_sql);
        $set_stmt->bind_param("ii", $address_id, $buyer_id);
        $set_stmt->execute();
        
        if ($set_stmt->affected_rows > 0) {
            $conn->commit();
            echo json_encode([
                'status' => 'success',
                'message' => 'Address set as default successfully'
            ]);
        } else {
            $conn->rollback();
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to set address as default'
            ]);
        }
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>