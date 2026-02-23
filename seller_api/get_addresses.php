<?php
// File: get_addresses.php - Get all addresses for a buyer
header('Content-Type: application/json');
require 'db_connection.php';

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
            WHERE buyer_id = ? 
            ORDER BY is_default DESC, created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $buyer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $addresses = [];
    while ($row = $result->fetch_assoc()) {
        // Add city, province, zip_code for display
        $row['city'] = 'Dumaguete City';
        $row['province'] = 'Negros Oriental';
        $row['zip_code'] = '6200';
        $addresses[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'addresses' => $addresses
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>