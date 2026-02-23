<?php
header('Content-Type: application/json');
require 'db_connection.php';

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
        WHERE buyer_id = ? AND is_default = 1
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();

$address = $result->fetch_assoc();

if (!$address) {
    echo json_encode([
        'status' => 'success',
        'address' => null
    ]);
    exit;
}

// Optional display fields
$address['city'] = 'Dumaguete City';
$address['province'] = 'Negros Oriental';
$address['zip_code'] = '6200';

echo json_encode([
    'status' => 'success',
    'address' => $address
]);
