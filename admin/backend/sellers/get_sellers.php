<?php
// /admin/backend/sellers/get_sellers.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

try {
    // Get seller statistics by approval status
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) AS total_sellers,
            SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) AS approved_sellers,
            SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) AS pending_sellers,
            SUM(CASE WHEN approval_status = 'rejected' THEN 1 ELSE 0 END) AS rejected_sellers,
            SUM(CASE WHEN is_confirmed = true THEN 1 ELSE 0 END) AS confirmed_sellers,
            SUM(CASE WHEN is_confirmed = false THEN 1 ELSE 0 END) AS unconfirmed_sellers,
            SUM(CASE WHEN setup_shop = true THEN 1 ELSE 0 END) AS shops_setup
        FROM sellers
    ");
    $stmt->execute();
    $statistics = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get sellers by plan
    $stmt = $conn->prepare("
        SELECT 
            seller_plan, 
            COUNT(*) AS count 
        FROM sellers 
        GROUP BY seller_plan
    ");
    $stmt->execute();
    $sellersByPlan = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $planCounts = [];
    foreach ($sellersByPlan as $row) {
        $planCounts[$row['seller_plan']] = (int) $row['count'];
    }

    // Get sellers by billing type
    $stmt = $conn->prepare("
        SELECT 
            seller_billing, 
            COUNT(*) AS count 
        FROM sellers 
        GROUP BY seller_billing
    ");
    $stmt->execute();
    $sellersByBilling = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $billingCounts = [];
    foreach ($sellersByBilling as $row) {
        $billingCounts[$row['seller_billing']] = (int) $row['count'];
    }

    // Get detailed seller list with store information
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
            st.logo_url,
            st.banner_url,
            st.owner_full_name,
            st.id_type,
            st.valid_id_files,
            st.store_photo_files,
            CASE 
                WHEN s.approval_status = 'pending' AND st.id IS NOT NULL THEN 1
                ELSE 0
            END AS has_pending_requirements
        FROM sellers s
        LEFT JOIN stores st ON s.id = st.seller_id
        ORDER BY s.created_at DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $sellers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process arrays for JSON response
    foreach ($sellers as &$seller) {
        // Convert PostgreSQL array strings to PHP arrays if needed
        if (isset($seller['valid_id_files']) && is_string($seller['valid_id_files'])) {
            $seller['valid_id_files'] = parsePostgresArray($seller['valid_id_files']);
        }
        if (isset($seller['store_photo_files']) && is_string($seller['store_photo_files'])) {
            $seller['store_photo_files'] = parsePostgresArray($seller['store_photo_files']);
        }
    }

    echo json_encode([
        'success' => true,
        'statistics' => [
            'total' => (int) $statistics['total_sellers'],
            'approved' => (int) $statistics['approved_sellers'],
            'pending' => (int) $statistics['pending_sellers'],
            'rejected' => (int) $statistics['rejected_sellers'],
            'confirmed' => (int) $statistics['confirmed_sellers'],
            'unconfirmed' => (int) $statistics['unconfirmed_sellers'],
            'shops_setup' => (int) $statistics['shops_setup']
        ],
        'by_plan' => $planCounts,
        'by_billing' => $billingCounts,
        'data' => $sellers
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
    
    // Remove curly braces
    $arrayString = trim($arrayString, '{}');
    
    if (empty($arrayString)) {
        return [];
    }
    
    // Split by comma, handling quoted values
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