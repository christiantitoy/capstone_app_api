<?php
// File: get_addresses.php - Get all addresses for a buyer
header('Content-Type: application/json');

require_once '/var/www/html/connection/db_connection.php';

try {
    if (!isset($_GET['buyer_id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'buyer_id is required'
        ]);
        exit;
    }
    
    $buyer_id = intval($_GET['buyer_id']);
    
    $sql = "SELECT id, recipient_name, phone_number, barangay, street_address, 
                   is_default, created_at, updated_at 
            FROM buyer_addresses 
            WHERE buyer_id = :buyer_id
            ORDER BY is_default DESC, created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':buyer_id' => $buyer_id]);

    $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add city, province, zip_code for display to each address
    foreach ($addresses as &$address) {
        $address['city'] = 'Dumaguete City';
        $address['province'] = 'Negros Oriental';
        $address['zip_code'] = '6200';
    }

    echo json_encode([
        'status' => 'success',
        'addresses' => $addresses
    ]);

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