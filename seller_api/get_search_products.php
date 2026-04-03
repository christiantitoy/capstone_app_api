<?php
header('Content-Type: application/json');

require_once '/var/www/html/connection/db_connection.php';

try {
    if (!isset($_GET['search_query']) || empty(trim($_GET['search_query']))) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Search query is required'
        ]);
        exit;
    }

    $search_query = trim($_GET['search_query']);
    
    $sql = "SELECT 
                i.id,
                i.product_name AS title,
                i.price,
                i.main_image_url AS image_url,
                i.seller_id,
                s.store_name AS shop
            FROM items i
            LEFT JOIN stores s ON i.seller_id = s.seller_id
            WHERE i.status = 'approved' 
            AND i.product_name ILIKE :search_query
            ORDER BY 
                CASE 
                    WHEN i.product_name ILIKE :exact_match THEN 1
                    WHEN i.product_name ILIKE :starts_with THEN 2
                    ELSE 3
                END,
                i.product_name ASC
            LIMIT 50";
    
    $stmt = $conn->prepare($sql);
    
    $search_pattern = '%' . $search_query . '%';
    $exact_pattern = $search_query;
    $starts_with_pattern = $search_query . '%';
    
    $stmt->execute([
        ':search_query' => $search_pattern,
        ':exact_match' => $exact_pattern,
        ':starts_with' => $starts_with_pattern
    ]);
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'query' => $search_query,
        'products' => $results,
        'count' => count($results)
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

$conn = null;
?>