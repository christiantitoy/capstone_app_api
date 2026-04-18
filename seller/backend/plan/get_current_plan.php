<?php
// /seller/backend/plan/get_current_plan.php
require_once '/var/www/html/connection/db_connection.php';
require_once __DIR__ . '/../session/auth.php';

header('Content-Type: application/json');

$response = ['success' => false, 'data' => null];

try {
    $seller_id = $_SESSION['seller_id'] ?? null;

    if (!$seller_id) {
        throw new Exception('Unauthorized');
    }

    // Get seller's current plan from sellers_plan table
    $stmt = $conn->prepare("
        SELECT 
            sp.plan,
            sp.billing,
            sp.status,
            sp.start_date,
            sp.end_date
        FROM public.sellers_plan sp
        WHERE sp.seller_id = ?
        ORDER BY sp.created_at DESC 
        LIMIT 1
    ");
    
    $stmt->execute([$seller_id]);
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no plan in sellers_plan, fallback to sellers table
    if (!$plan) {
        $stmt2 = $conn->prepare("
            SELECT 
                LOWER(seller_plan) as plan,
                COALESCE(seller_billing, 'lifetime') as billing,
                'active' as status,
                created_at as start_date,
                NULL as end_date
            FROM public.sellers
            WHERE id = ?
        ");
        $stmt2->execute([$seller_id]);
        $plan = $stmt2->fetch(PDO::FETCH_ASSOC);
    }

    if ($plan) {
        // Ensure plan is lowercase for consistency
        $plan['plan'] = strtolower($plan['plan']);
        
        // Format dates
        if ($plan['start_date']) {
            $plan['start_date_formatted'] = date('M d, Y', strtotime($plan['start_date']));
        }
        if ($plan['end_date']) {
            $plan['end_date_formatted'] = date('M d, Y', strtotime($plan['end_date']));
            
            // Add days remaining calculation
            $end_timestamp = strtotime($plan['end_date']);
            $now = time();
            if ($end_timestamp > $now) {
                $plan['days_remaining'] = ceil(($end_timestamp - $now) / 86400);
            } else {
                $plan['days_remaining'] = 0;
                // Optionally mark as expired if past end date
                if ($plan['status'] === 'active') {
                    $plan['status'] = 'expired';
                }
            }
        }
        
        // Add plan description
        $descriptions = [
            'bronze' => 'Free forever · 3 employees · Up to 50 products',
            'silver' => '10 employees · 100 products · Featured products',
            'gold' => 'Unlimited employees · Unlimited products · Priority search'
        ];
        $plan['description'] = $descriptions[$plan['plan']] ?? $descriptions['bronze'];
        
        $response['data'] = $plan;
        $response['success'] = true;
    } else {
        // Default bronze plan if no record exists
        $response['data'] = [
            'plan' => 'bronze',
            'billing' => 'lifetime',
            'status' => 'active',
            'start_date' => date('Y-m-d H:i:s'),
            'end_date' => null,
            'start_date_formatted' => date('M d, Y'),
            'description' => 'Free forever · 3 employees · Up to 50 products'
        ];
        $response['success'] = true;
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Get Plan Error: " . $e->getMessage());
} catch (PDOException $e) {
    $response['message'] = 'Database error. Please try again later.';
    error_log("Get Plan DB Error: " . $e->getMessage());
}

echo json_encode($response);
?>