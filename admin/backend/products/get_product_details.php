<?php
// /admin/backend/products/get_product_details.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid product ID'
    ]);
    exit;
}

$productId = (int) $_GET['id'];

try {
    // Get product details with seller information
    $sql = "
        SELECT 
            i.id,
            i.seller_id,
            i.product_name,
            i.product_description,
            i.category,
            i.price,
            i.stock,
            i.main_image_url,
            i.image_urls,
            i.status,
            i.has_variations,
            i.created_at,
            i.updated_at,
            i.employee_id,
            i.sold,
            s.full_name as seller_name,
            s.email as seller_email,
            s.seller_plan,
            s.approval_status,
            st.store_name,
            st.logo_url as store_logo
        FROM items i
        LEFT JOIN sellers s ON i.seller_id = s.id
        LEFT JOIN stores st ON s.id = st.seller_id
        WHERE i.id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo json_encode([
            'success' => false,
            'message' => 'Product not found'
        ]);
        exit;
    }
    
    // Parse image URLs (stored as comma-separated string)
    $product['image_urls_array'] = [];
    if (!empty($product['image_urls'])) {
        $product['image_urls_array'] = array_filter(explode(',', $product['image_urls']));
    }
    
    // Get product variants if has_variations is true
    $variants = [];
    if ($product['has_variations'] == 1) {
        $stmt = $conn->prepare("
            SELECT 
                id,
                options_json,
                options_json_value,
                price,
                stock,
                sku,
                image_urls,
                created_at
            FROM item_variants
            WHERE item_id = ?
            ORDER BY id
        ");
        $stmt->execute([$productId]);
        $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Parse variant image URLs
        foreach ($variants as &$variant) {
            $variant['image_urls_array'] = [];
            if (!empty($variant['image_urls'])) {
                $variant['image_urls_array'] = array_filter(explode(',', $variant['image_urls']));
            }
            
            // Parse options_json if it's a string
            if (isset($variant['options_json']) && is_string($variant['options_json'])) {
                $decoded = json_decode($variant['options_json'], true);
                if ($decoded) {
                    $variant['options'] = $decoded;
                }
            }
        }
    }
    
    // Get order statistics for this product using order_items
    $stmt = $conn->prepare("
        SELECT 
            COUNT(DISTINCT oi.order_id) AS times_ordered,
            COALESCE(SUM(oi.quantity), 0) AS total_quantity_sold
        FROM order_items oi
        WHERE oi.product_id = ?
    ");
    $stmt->execute([$productId]);
    $orderStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get recent orders containing this product
    $stmt = $conn->prepare("
        SELECT 
            o.id as order_id,
            o.buyer_id,
            o.created_at as order_date,
            o.status as order_status,
            oi.quantity,
            oi.unit_price as price_at_time,
            b.username as buyer_name
        FROM order_items oi
        INNER JOIN orders o ON oi.order_id = o.id
        LEFT JOIN buyers b ON o.buyer_id = b.id
        WHERE oi.product_id = ?
        ORDER BY o.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$productId]);
    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get employee info if assigned
    $employee = null;
    if ($product['employee_id']) {
        $stmt = $conn->prepare("
            SELECT 
                id,
                full_name,
                email,
                role,
                status
            FROM employees
            WHERE id = ?
        ");
        $stmt->execute([$product['employee_id']]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'product' => $product,
            'variants' => $variants,
            'order_stats' => [
                'times_ordered' => (int) $orderStats['times_ordered'],
                'total_quantity_sold' => (int) $orderStats['total_quantity_sold']
            ],
            'recent_orders' => $recentOrders,
            'employee' => $employee
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>