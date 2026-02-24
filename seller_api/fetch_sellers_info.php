<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '/var/www/html/connection/db_connection.php';

try {
    // Check if connection is established (PDO throws exception if connection fails)
    if (!$conn) {
        throw new Exception("DATABASE CONNECTION FAILED");
    }

    $sql = "SELECT id, shop_name, business_address, shop_category FROM seller_profiles";
    $stmt = $conn->query($sql);

    $shops = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $shops[] = [
            'id' => $row['id'],
            'shop_name' => $row['shop_name'],
            'business_address' => $row['business_address'],
            'shop_category' => $row['shop_category']
        ];
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'YEHEY NA FETCHED NA!',
        'data' => [
            'shops' => $shops
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    $conn = null; // Close PDO connection
}
?>