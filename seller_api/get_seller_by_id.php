<?php
header('Content-Type: application/json');

require_once '/var/www/html/connection/db_connection.php';

// Check if connection exists
if (!isset($conn) || $conn === null) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

try {
    // Get GET parameter
    $sellerId = isset($_GET['seller_id']) ? intval($_GET['seller_id']) : null;
    
    // Validate parameter
    if ($sellerId === null) {
        echo json_encode(['status' => 'error', 'message' => 'Missing seller_id parameter']);
        exit;
    }
    
    // Query to fetch seller information
    $sql = "
        SELECT 
            seller_id,
            store_name,
            category,
            description,
            contact_number,
            open_time,
            close_time,
            latitude,
            longitude,
            plus_code,
            logo_url,
            banner_url,
            owner_full_name,
            id_type,
            valid_id_files,
            store_photo_files,
            created_at,
            updated_at,
            id
        FROM stores
        WHERE seller_id = :sellerId
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([':sellerId' => $sellerId]);
    
    $seller = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Format the response
    if ($seller) {
        // Format time fields to remove seconds if needed
        if ($seller['open_time']) {
            $seller['open_time'] = date('H:i', strtotime($seller['open_time']));
        }
        if ($seller['close_time']) {
            $seller['close_time'] = date('H:i', strtotime($seller['close_time']));
        }
        
        // Convert numeric fields to appropriate types
        $seller['seller_id'] = intval($seller['seller_id']);
        $seller['id'] = intval($seller['id']);
        $seller['latitude'] = $seller['latitude'] ? floatval($seller['latitude']) : null;
        $seller['longitude'] = $seller['longitude'] ? floatval($seller['longitude']) : null;
        
        echo json_encode([
            'status' => 'success',
            'seller' => $seller
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Seller not found'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn = null;
?>