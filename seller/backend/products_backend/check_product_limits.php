<?php
// /seller/backend/products_backend/check_product_limits.php
require_once '/var/www/html/connection/db_connection.php';
require_once __DIR__ . '/../session/auth.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    $seller_id = $_SESSION['seller_id'] ?? null;

    if (!$seller_id) {
        throw new Exception('Unauthorized. Please login again.');
    }

    // Get seller's plan from sellers table
    $stmt = $conn->prepare("
        SELECT 
            LOWER(seller_plan) as plan,
            seller_billing as billing
        FROM public.sellers
        WHERE id = ?
    ");
    $stmt->execute([$seller_id]);
    $seller = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$seller) {
        throw new Exception('Seller not found.');
    }

    $seller_plan = $seller['plan'];
    
    // Define product limits per plan
    $plan_limits = [
        'bronze' => 50,
        'silver' => 100,
        'gold' => null // unlimited
    ];

    $max_products = $plan_limits[$seller_plan] ?? 50;

    // If gold plan, no limits - skip checks
    if ($seller_plan === 'gold') {
        $response['success'] = true;
        $response['message'] = 'Gold plan - unlimited products';
        $response['data'] = [
            'plan' => 'gold',
            'max_products' => 'unlimited',
            'products_on_hold' => 0,
            'products_reactivated' => 0
        ];
        echo json_encode($response);
        exit;
    }

    // Get all products for this seller (excluding removed ones)
    $stmt = $conn->prepare("
        SELECT 
            id,
            product_name,
            status,
            created_at
        FROM public.items
        WHERE seller_id = ? 
            AND status != 'removed'
        ORDER BY created_at ASC
    ");
    $stmt->execute([$seller_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_products = count($products);
    $approved_products = array_filter($products, function($prod) {
        return $prod['status'] === 'approved';
    });
    $approved_count = count($approved_products);
    $on_hold_count = count(array_filter($products, function($prod) {
        return $prod['status'] === 'on_hold';
    }));
    
    $products_put_on_hold = 0;
    $products_reactivated = 0;

    // If approved products exceed the limit
    if ($approved_count > $max_products) {
        // Sort products by created_at (oldest first - they keep approved status)
        // Newest products will be put on hold
        
        $products_by_date = $products;
        usort($products_by_date, function($a, $b) {
            return strtotime($a['created_at']) - strtotime($b['created_at']);
        });
        
        // Keep the first $max_products as approved, put the rest on hold
        $keep_approved_ids = array_slice(array_column($products_by_date, 'id'), 0, $max_products);
        
        // Begin transaction
        $conn->beginTransaction();
        
        // Put excess products on hold
        if (!empty($keep_approved_ids)) {
            $placeholders = implode(',', array_fill(0, count($keep_approved_ids), '?'));
            $updateStmt = $conn->prepare("
                UPDATE public.items 
                SET status = 'on_hold', updated_at = NOW()
                WHERE seller_id = ? 
                    AND status = 'approved'
                    AND id NOT IN ($placeholders)
            ");
            
            $params = array_merge([$seller_id], $keep_approved_ids);
            $updateStmt->execute($params);
        } else {
            // If no products to keep approved, put all on hold
            $updateStmt = $conn->prepare("
                UPDATE public.items 
                SET status = 'on_hold', updated_at = NOW()
                WHERE seller_id = ? AND status = 'approved'
            ");
            $updateStmt->execute([$seller_id]);
        }
        
        $products_put_on_hold = $updateStmt->rowCount();
        
        $conn->commit();
        
        $response['message'] = "$products_put_on_hold product(s) put on hold due to plan limits.";
    } 
    // If approved products are under limit, check if any on_hold can be reactivated
    else if ($approved_count < $max_products && $on_hold_count > 0) {
        $available_slots = $max_products - $approved_count;
        
        // Get products on hold, oldest first
        $stmt = $conn->prepare("
            SELECT id, product_name
            FROM public.items
            WHERE seller_id = ? 
                AND status = 'on_hold'
            ORDER BY created_at ASC
            LIMIT ?
        ");
        $stmt->execute([$seller_id, $available_slots]);
        $on_hold_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($on_hold_products)) {
            $conn->beginTransaction();
            
            $reactivate_ids = array_column($on_hold_products, 'id');
            $placeholders = implode(',', array_fill(0, count($reactivate_ids), '?'));
            $updateStmt = $conn->prepare("
                UPDATE public.items 
                SET status = 'approved', updated_at = NOW()
                WHERE id IN ($placeholders)
            ");
            $updateStmt->execute($reactivate_ids);
            $products_reactivated = $updateStmt->rowCount();
            
            $conn->commit();
            
            $response['message'] = "$products_reactivated product(s) reactivated.";
        } else {
            $response['message'] = 'Product limits are within plan allowance.';
        }
    } else {
        $response['message'] = 'Product limits are within plan allowance.';
    }

    // Get updated counts
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'on_hold' THEN 1 ELSE 0 END) as on_hold,
            SUM(CASE WHEN status = 'removed' THEN 1 ELSE 0 END) as removed
        FROM public.items
        WHERE seller_id = ?
    ");
    $stmt->execute([$seller_id]);
    $counts = $stmt->fetch(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['data'] = [
        'plan' => $seller_plan,
        'max_products' => $max_products,
        'total_products' => (int)$counts['total'],
        'approved_products' => (int)$counts['approved'],
        'on_hold_products' => (int)$counts['on_hold'],
        'removed_products' => (int)$counts['removed'],
        'products_put_on_hold' => $products_put_on_hold,
        'products_reactivated' => $products_reactivated
    ];

} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    $response['message'] = $e->getMessage();
    error_log("Product Limit Check Error: " . $e->getMessage());
} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    $response['message'] = 'Database error. Please try again later.';
    error_log("Product Limit DB Error: " . $e->getMessage());
}

echo json_encode($response);
?>