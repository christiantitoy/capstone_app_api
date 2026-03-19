<?php
header('Content-Type: application/json');
require_once '/var/www/html/connection/db_connection.php';

// Only POST/DELETE allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'POST or DELETE request required'
    ]);
    exit;
}

// Get input
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    $input = $_POST;
}

// Get parameters
$item_id = isset($input['item_id']) ? filter_var($input['item_id'], FILTER_VALIDATE_INT) : null;
$seller_id = isset($input['seller_id']) ? filter_var($input['seller_id'], FILTER_VALIDATE_INT) : null;

// Validate
if (!$item_id || $item_id <= 0 || !$seller_id || $seller_id <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Valid item_id and seller_id are required'
    ]);
    exit;
}

try {
    // Verify ownership and delete permanently
    $stmt = $conn->prepare("
        DELETE FROM items 
        WHERE id = :item_id AND seller_id = :seller_id
    ");
    
    $stmt->execute([
        'item_id' => $item_id,
        'seller_id' => $seller_id
    ]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Item permanently deleted successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Item not found or does not belong to this seller'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Database error in delete_item.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error occurred'
    ]);
}

$conn = null;
?>