<?php
header('Content-Type: application/json');

require_once '/var/www/html/connection/db_connection.php';

try {
    // Check if search query is provided
    if (!isset($_GET['search_query']) || empty(trim($_GET['search_query']))) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Search query is required'
        ]);
        exit;
    }

    $search_query = trim($_GET['search_query']);
    
    // Prepare the SQL query to search for products matching the search string
    $sql = "SELECT 
                id,
                product_name,
                product_description,
                category,
                price,
                stock,
                main_image_url,
                status
            FROM items 
            WHERE status = 'approved' 
            AND product_name ILIKE :search_query
            ORDER BY 
                CASE 
                    WHEN product_name ILIKE :exact_match THEN 1
                    WHEN product_name ILIKE :starts_with THEN 2
                    ELSE 3
                END,
                product_name ASC
            LIMIT 20";
    
    $stmt = $conn->prepare($sql);
    
    // Prepare search patterns
    $search_pattern = '%' . $search_query . '%';
    $exact_pattern = $search_query;
    $starts_with_pattern = $search_query . '%';
    
    $stmt->execute([
        ':search_query' => $search_pattern,
        ':exact_match' => $exact_pattern,
        ':starts_with' => $starts_with_pattern
    ]);
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Extract just the product names for suggestions
    $suggestions = array_map(function($item) {
        return $item['product_name'];
    }, $results);
    
    echo json_encode([
        'status' => 'success',
        'query' => $search_query,
        'suggestions' => $suggestions,
        'count' => count($suggestions),
        'items' => $results // Full item details if needed
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