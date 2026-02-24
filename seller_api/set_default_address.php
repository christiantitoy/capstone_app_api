<?php
// File: set_default_address.php
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
        // First, unset all defaults for this buyer
        $unset_sql = "UPDATE buyer_addresses SET is_default = 0 WHERE buyer_id = :buyer_id";
        $unset_stmt = $conn->prepare($unset_sql);
        $unset_stmt->execute([':buyer_id' => $buyer_id]);

        // Then set the selected address as default
        $set_sql = "UPDATE buyer_addresses SET is_default = 1, updated_at = CURRENT_TIMESTAMP
                    WHERE id = :address_id AND buyer_id = :buyer_id";
        $set_stmt = $conn->prepare($set_sql);
        $set_stmt->execute([
            ':address_id' => $address_id,
            ':buyer_id' => $buyer_id
        ]);

        if ($set_stmt->rowCount() > 0) {
            $conn->commit();
            echo json_encode([
                'status' => 'success',
                'message' => 'Address set as default successfully'
            ]);
        } else {
            $conn->rollBack();
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to set address as default'
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