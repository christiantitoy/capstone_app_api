<?php
// /admin/backend/sellers/get_seller_details.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid seller ID'
    ]);
    exit;
}

$sellerId = (int) $_GET['id'];

try {
    // Get seller details with store information
    $sql = "
        SELECT 
            s.id,
            s.full_name,
            s.email,
            s.is_confirmed,
            s.created_at,
            s.updated_at,
            s.setup_shop,
            s.seller_plan,
            s.approval_status,
            s.seller_billing,
            st.store_name,
            st.category,
            st.description,
            st.contact_number,
            st.open_time,
            st.close_time,
            st.latitude,
            st.longitude,
            st.plus_code,
            st.logo_url,
            st.banner_url,
            st.owner_full_name,
            st.id_type,
            st.valid_id_files,
            st.store_photo_files,
            st.created_at as store_created_at
        FROM sellers s
        LEFT JOIN stores st ON s.id = st.seller_id
        WHERE s.id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$sellerId]);
    $seller = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$seller) {
        echo json_encode([
            'success' => false,
            'message' => 'Seller not found'
        ]);
        exit;
    }
    
    // Parse PostgreSQL arrays if they exist
    if (isset($seller['valid_id_files']) && is_string($seller['valid_id_files'])) {
        $seller['valid_id_files'] = parsePostgresArray($seller['valid_id_files']);
    }
    if (isset($seller['store_photo_files']) && is_string($seller['store_photo_files'])) {
        $seller['store_photo_files'] = parsePostgresArray($seller['store_photo_files']);
    }
    
    // Get employees
    $stmt = $conn->prepare("
        SELECT 
            id,
            full_name,
            email,
            role,
            status,
            created_at,
            updated_at,
            last_login,
            is_removed
        FROM employees
        WHERE seller_id = ? AND is_removed = false
        ORDER BY created_at DESC
    ");
    $stmt->execute([$sellerId]);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get product statistics
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) AS total_products,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved_products,
            SUM(CASE WHEN status = 'on_review' THEN 1 ELSE 0 END) AS pending_products,
            SUM(CASE WHEN status = 'on_hold' THEN 1 ELSE 0 END) AS on_hold_products,
            SUM(CASE WHEN status = 'removed' THEN 1 ELSE 0 END) AS removed_products,
            COALESCE(SUM(stock), 0) AS total_stock,
            COALESCE(SUM(sold), 0) AS total_sold,
            COALESCE(SUM(price * stock), 0) AS inventory_value
        FROM items
        WHERE seller_id = ?
    ");
    $stmt->execute([$sellerId]);
    $productStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get recent products
    $stmt = $conn->prepare("
        SELECT 
            id,
            product_name,
            category,
            price,
            stock,
            sold,
            status,
            main_image_url,
            created_at
        FROM items
        WHERE seller_id = ?
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$sellerId]);
    $recentProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get order statistics for this seller
    $stmt = $conn->prepare("
        SELECT 
            COUNT(DISTINCT o.id) AS total_orders,
            COALESCE(SUM(o.total_amount), 0) AS total_revenue,
            COUNT(DISTINCT o.buyer_id) AS unique_customers
        FROM orders o
        INNER JOIN order_items oi ON o.id = oi.order_id
        INNER JOIN items i ON oi.item_id = i.id
        WHERE i.seller_id = ?
    ");
    $stmt->execute([$sellerId]);
    $orderStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'seller' => $seller,
            'employees' => $employees,
            'product_stats' => [
                'total_products' => (int) $productStats['total_products'],
                'approved_products' => (int) $productStats['approved_products'],
                'pending_products' => (int) $productStats['pending_products'],
                'on_hold_products' => (int) $productStats['on_hold_products'],
                'removed_products' => (int) $productStats['removed_products'],
                'total_stock' => (int) $productStats['total_stock'],
                'total_sold' => (int) $productStats['total_sold'],
                'inventory_value' => (float) $productStats['inventory_value']
            ],
            'order_stats' => [
                'total_orders' => (int) $orderStats['total_orders'],
                'total_revenue' => (float) $orderStats['total_revenue'],
                'unique_customers' => (int) $orderStats['unique_customers']
            ],
            'recent_products' => $recentProducts
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

/**
 * Helper function to parse PostgreSQL array strings
 */
function parsePostgresArray($arrayString) {
    if (empty($arrayString) || $arrayString === '{}') {
        return [];
    }
    
    $arrayString = trim($arrayString, '{}');
    
    if (empty($arrayString)) {
        return [];
    }
    
    $result = [];
    $current = '';
    $inQuotes = false;
    
    for ($i = 0; $i < strlen($arrayString); $i++) {
        $char = $arrayString[$i];
        
        if ($char === '"' && ($i === 0 || $arrayString[$i - 1] !== '\\')) {
            $inQuotes = !$inQuotes;
        } elseif ($char === ',' && !$inQuotes) {
            $result[] = trim($current, '"');
            $current = '';
        } else {
            $current .= $char;
        }
    }
    
    if ($current !== '') {
        $result[] = trim($current, '"');
    }
    
    return $result;
}
?>