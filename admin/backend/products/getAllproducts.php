<?php
// /admin/backend/products/getAllproducts.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

try {
    // Query to get all products with count
    $sql = "SELECT id, seller_id, product_name, product_description, category, price, stock, main_image_url, image_urls, status, has_variations, employee_id, created_at, updated_at FROM public.items ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count of products
    $countSql = "SELECT COUNT(*) as total FROM public.items";
    $countStmt = $conn->prepare($countSql);
    $countStmt->execute();
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo json_encode([
        'success' => true,
        'data' => $products,
        'total_count' => (int)$totalCount
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} finally {
    $conn = null; // Close the database connection
}
?>