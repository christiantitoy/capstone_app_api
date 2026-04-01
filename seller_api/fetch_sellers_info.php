<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '/var/www/html/connection/db_connection.php';

try {
    // Check if connection is established (PDO throws exception if connection fails)
    if (!$conn) {
        throw new Exception("DATABASE CONNECTION FAILED");
    }

    $sql = "SELECT seller_id, store_name, category, open_time, logo_url FROM stores";
    $stmt = $conn->query($sql);

    $stores = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $stores[] = [
            'seller_id' => $row['seller_id'],
            'store_name' => $row['store_name'],
            'category' => $row['category'],
            'open_time' => $row['open_time'],
            'logo_url' => $row['logo_url']
        ];
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'YEHEY NA FETCHED NA!',
        'data' => [
            'stores' => $stores
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