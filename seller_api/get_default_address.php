<?php
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

    $sql = "SELECT id, buyer_id, recipient_name, phone_number, full_address,
                   gps_location, is_default, created_at, updated_at
            FROM buyer_addresses
            WHERE buyer_id = :buyer_id AND is_default = 1
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':buyer_id' => $buyer_id]);

    $address = $stmt->fetch(PDO::FETCH_ASSOC);

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