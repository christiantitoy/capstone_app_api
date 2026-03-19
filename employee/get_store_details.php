<?php
header('Content-Type: application/json');
require_once '/var/www/html/connection/db_connection.php';

// Only GET allowed
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'status' => 'error',
        'message' => 'GET request required'
    ]);
    exit;
}

// Get seller_id from query parameter
$seller_id = isset($_GET['seller_id']) ? filter_var($_GET['seller_id'], FILTER_VALIDATE_INT) : null;

// Validate seller_id
if (!$seller_id || $seller_id <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Valid seller_id is required'
    ]);
    exit;
}

try {
    // Fetch store details from stores table
    $stmt = $conn->prepare("
        SELECT 
            seller_id, 
            store_name, 
            category, 
            owner_full_name
        FROM stores 
        WHERE seller_id = :seller_id
        LIMIT 1
    ");
    
    $stmt->execute(['seller_id' => $seller_id]);
    $store = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$store) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Store not found for this seller'
        ]);
        exit;
    }

    // Success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Store details retrieved successfully',
        'store' => [
            'seller_id' => (int)$store['seller_id'],
            'store_name' => $store['store_name'],
            'category' => $store['category'],
            'owner_full_name' => $store['owner_full_name']
        ]
    ]);

} catch (PDOException $e) {
    // Log error for debugging (you might want to use error_log)
    error_log("Database error in get_store_details.php: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error occurred'
    ]);
}

// Close connection
$conn = null;
?>