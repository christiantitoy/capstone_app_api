<?php
// File: save_address.php
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
    
    // Validate required fields
    $required_fields = ['buyer_id', 'recipient_name', 'phone_number', 'barangay', 'street_address'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            echo json_encode([
                'status' => 'error',
                'message' => $field . ' is required'
            ]);
            exit;
        }
    }
    
    // Sanitize and validate input
    $buyer_id = intval($data['buyer_id']);
    $recipient_name = trim($data['recipient_name']);
    $phone_number = trim($data['phone_number']);
    $barangay = trim($data['barangay']);
    $street_address = trim($data['street_address']);
    $is_default = isset($data['is_default']) ? intval($data['is_default']) : 0;
    
    // Validate buyer exists
    $check_buyer = $conn->prepare("SELECT id FROM buyers WHERE id = :buyer_id");
    $check_buyer->execute([':buyer_id' => $buyer_id]);

    if ($check_buyer->rowCount() === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Buyer not found'
        ]);
        exit;
    }

    // Validate phone number format (Philippines mobile: 09XXXXXXXXX)
    if (!preg_match('/^09[0-9]{9}$/', $phone_number)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid Philippine mobile number format (09XXXXXXXXX)'
        ]);
        exit;
    }

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

    // Start transaction in case we need to update defaults
    $conn->beginTransaction();

    try {
        // If setting as default, update other addresses to not default
        if ($is_default) {
            $update_defaults = $conn->prepare("UPDATE buyer_addresses SET is_default = 0 WHERE buyer_id = :buyer_id");
            $update_defaults->execute([':buyer_id' => $buyer_id]);
        }

        // Insert new address
        $sql = "INSERT INTO buyer_addresses
                (buyer_id, recipient_name, phone_number, barangay, street_address, is_default)
                VALUES (:buyer_id, :recipient_name, :phone_number, :barangay, :street_address, :is_default)";

        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            ':buyer_id' => $buyer_id,
            ':recipient_name' => $recipient_name,
            ':phone_number' => $phone_number,
            ':barangay' => $barangay,
            ':street_address' => $street_address,
            ':is_default' => $is_default
        ]);

        if ($result) {
            $address_id = $conn->lastInsertId();

            // Commit transaction
            $conn->commit();

            // Return success with the created address data
            echo json_encode([
                'status' => 'success',
                'message' => 'Address saved successfully',
                'address_id' => $address_id,
                'address' => [
                    'id' => $address_id,
                    'buyer_id' => $buyer_id,
                    'recipient_name' => $recipient_name,
                    'phone_number' => $phone_number,
                    'barangay' => $barangay,
                    'street_address' => $street_address,
                    'is_default' => $is_default,
                    'city' => 'Dumaguete City',
                    'province' => 'Negros Oriental',
                    'zip_code' => '6200'
                ]
            ]);
        } else {
            $conn->rollBack();
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to save address'
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