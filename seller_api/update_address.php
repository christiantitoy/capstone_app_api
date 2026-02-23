<?php
// File: update_address.php
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
    
    // Validate required fields
    $required_fields = ['address_id', 'buyer_id', 'recipient_name', 'phone_number', 'barangay', 'street_address'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            echo json_encode([
                'status' => 'error',
                'message' => $field . ' is required'
            ]);
            exit;
        }
    }
    
    $address_id = intval($data['address_id']);
    $buyer_id = intval($data['buyer_id']);
    $recipient_name = trim($data['recipient_name']);
    $phone_number = trim($data['phone_number']);
    $barangay = trim($data['barangay']);
    $street_address = trim($data['street_address']);
    $is_default = isset($data['is_default']) ? intval($data['is_default']) : 0;
    
    // Validate barangay from allowed list
    $allowed_barangays = [
        'Bagacay', 'Bajumpandan', 'Balugo', 'Banilad', 'Bantayan', 'Batinguel', 'Bunao',
        'Cadawinonan', 'Calindagan', 'Camanjac', 'Candau-ay', 'Cantil-e', 'Darong',
        'Junob', 'Looc', 'Mangnao', 'Motong', 'Piapi', 'Poblacion 1', 'Poblacion 2',
        'Poblacion 3', 'Poblacion 4', 'Poblacion 5', 'Poblacion 6', 'Poblacion 7',
        'Poblacion 8', 'Tabuctubig', 'Taclobo', 'Talay'
    ];
    
    if (!in_array($barangay, $allowed_barangays)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid barangay selected'
        ]);
        exit;
    }
    
    // Validate phone number format
    if (!preg_match('/^09[0-9]{9}$/', $phone_number)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid Philippine mobile number format (09XXXXXXXXX)'
        ]);
        exit;
    }
    
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
        // If setting as default, unset other defaults first
        if ($is_default) {
            $unset_sql = "UPDATE buyer_addresses SET is_default = 0 WHERE buyer_id = ? AND id != ?";
            $unset_stmt = $conn->prepare($unset_sql);
            $unset_stmt->bind_param("ii", $buyer_id, $address_id);
            $unset_stmt->execute();
        }
        
        // Update the address
        $update_sql = "UPDATE buyer_addresses 
                       SET recipient_name = ?, phone_number = ?, barangay = ?, 
                           street_address = ?, is_default = ?, updated_at = CURRENT_TIMESTAMP
                       WHERE id = ? AND buyer_id = ?";
        
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param(
            "ssssiii", 
            $recipient_name, 
            $phone_number, 
            $barangay, 
            $street_address, 
            $is_default,
            $address_id,
            $buyer_id
        );
        
        if ($update_stmt->execute()) {
            if ($update_stmt->affected_rows > 0) {
                $conn->commit();
                
                // Get updated address
                $get_sql = "SELECT * FROM buyer_addresses WHERE id = ?";
                $get_stmt = $conn->prepare($get_sql);
                $get_stmt->bind_param("i", $address_id);
                $get_stmt->execute();
                $result = $get_stmt->get_result();
                $updated_address = $result->fetch_assoc();
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Address updated successfully',
                    'address' => [
                        'id' => $updated_address['id'],
                        'buyer_id' => $updated_address['buyer_id'],
                        'recipient_name' => $updated_address['recipient_name'],
                        'phone_number' => $updated_address['phone_number'],
                        'barangay' => $updated_address['barangay'],
                        'street_address' => $updated_address['street_address'],
                        'is_default' => $updated_address['is_default'],
                        'city' => 'Dumaguete City',
                        'province' => 'Negros Oriental',
                        'zip_code' => '6200'
                    ]
                ]);
            } else {
                $conn->rollback();
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No changes made to address'
                ]);
            }
        } else {
            $conn->rollback();
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to update address: ' . $update_stmt->error
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