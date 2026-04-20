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
    // Get seller details with store information - ADDED rejection_reason
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
            s.rejection_reason,
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
    
    // Get product count only
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total_products
        FROM items
        WHERE seller_id = ?
    ");
    $stmt->execute([$sellerId]);
    $productCount = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get order statistics for this seller using sold_items
    $stmt = $conn->prepare("
        SELECT 
            COUNT(DISTINCT si.orders_id) AS total_orders,
            COALESCE(SUM(oi.unit_price * oi.quantity), 0) AS total_revenue,
            COUNT(DISTINCT o.buyer_id) AS unique_customers
        FROM sold_items si
        INNER JOIN order_items oi ON si.order_items_id = oi.id
        INNER JOIN orders o ON si.orders_id = o.id
        INNER JOIN items i ON oi.product_id = i.id
        WHERE i.seller_id = ?
    ");
    $stmt->execute([$sellerId]);
    $orderStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If no sold_items found, fallback to order_items only
    if ($orderStats['total_orders'] == 0) {
        $stmt = $conn->prepare("
            SELECT 
                COUNT(DISTINCT oi.order_id) AS total_orders,
                COALESCE(SUM(oi.unit_price * oi.quantity), 0) AS total_revenue,
                COUNT(DISTINCT o.buyer_id) AS unique_customers
            FROM order_items oi
            INNER JOIN items i ON oi.product_id = i.id
            INNER JOIN orders o ON oi.order_id = o.id
            WHERE i.seller_id = ?
        ");
        $stmt->execute([$sellerId]);
        $orderStats = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'seller' => $seller,
            'employees' => $employees,
            'product_stats' => [
                'total_products' => (int) $productCount['total_products']
            ],
            'order_stats' => [
                'total_orders' => (int) ($orderStats['total_orders'] ?? 0),
                'total_revenue' => (float) ($orderStats['total_revenue'] ?? 0),
                'unique_customers' => (int) ($orderStats['unique_customers'] ?? 0)
            ]
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