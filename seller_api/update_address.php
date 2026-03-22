<?php
// File: update_address.php
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
    
    // Validate required fields - updated to match new schema
    $required_fields = ['address_id', 'buyer_id', 'recipient_name', 'phone_number', 'full_address', 'gps_location'];
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
    $full_address = trim($data['full_address']);
    $gps_location = trim($data['gps_location']);
    $is_default = isset($data['is_default']) ? intval($data['is_default']) : 0;
    
    // Validate phone number format
    if (!preg_match('/^09[0-9]{9}$/', $phone_number)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid Philippine mobile number format (09XXXXXXXXX)'
        ]);
        exit;
    }
    
    // Check if address exists and belongs to buyer
    $check_sql = "SELECT id FROM buyer_addresses WHERE id = :address_id AND buyer_id = :buyer_id";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->execute([
        ':address_id' => $address_id,
        ':buyer_id' => $buyer_id
    ]);

    if ($check_stmt->rowCount() === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Address not found or does not belong to user'
        ]);
        exit;
    }

    // Start transaction
    $conn->beginTransaction();

    try {
        // If setting as default, unset other defaults first
        if ($is_default) {
            $unset_sql = "UPDATE buyer_addresses SET is_default = 0 WHERE buyer_id = :buyer_id AND id != :address_id";
            $unset_stmt = $conn->prepare($unset_sql);
            $unset_stmt->execute([
                ':buyer_id' => $buyer_id,
                ':address_id' => $address_id
            ]);
        }

        // Update the address - updated fields to match new schema
        $update_sql = "UPDATE buyer_addresses
                       SET recipient_name = :recipient_name,
                           phone_number = :phone_number,
                           full_address = :full_address,
                           gps_location = :gps_location,
                           is_default = :is_default,
                           updated_at = CURRENT_TIMESTAMP
                       WHERE id = :address_id AND buyer_id = :buyer_id";

        $update_stmt = $conn->prepare($update_sql);
        $result = $update_stmt->execute([
            ':recipient_name' => $recipient_name,
            ':phone_number' => $phone_number,
            ':full_address' => $full_address,
            ':gps_location' => $gps_location,
            ':is_default' => $is_default,
            ':address_id' => $address_id,
            ':buyer_id' => $buyer_id
        ]);

        if ($result) {
            if ($update_stmt->rowCount() > 0) {
                $conn->commit();

                // Get updated address
                $get_sql = "SELECT * FROM buyer_addresses WHERE id = :address_id";
                $get_stmt = $conn->prepare($get_sql);
                $get_stmt->execute([':address_id' => $address_id]);
                $updated_address = $get_stmt->fetch(PDO::FETCH_ASSOC);

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Address updated successfully',
                    'address' => [
                        'id' => $updated_address['id'],
                        'buyer_id' => $updated_address['buyer_id'],
                        'recipient_name' => $updated_address['recipient_name'],
                        'phone_number' => $updated_address['phone_number'],
                        'full_address' => $updated_address['full_address'],
                        'gps_location' => $updated_address['gps_location'],
                        'is_default' => $updated_address['is_default'],
                        'city' => 'Dumaguete City',
                        'province' => 'Negros Oriental',
                        'zip_code' => '6200'
                    ]
                ]);
            } else {
                $conn->rollBack();
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No changes made to address'
                ]);
            }
        } else {
            $conn->rollBack();
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to update address'
            ]);
        }
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
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

$conn = null; // Close PDO connection
?>