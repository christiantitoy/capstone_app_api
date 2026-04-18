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

    // Get seller's current plan
    $stmt = $conn->prepare("
        SELECT 
            plan,
            billing,
            status,
            start_date,
            end_date
        FROM public.sellers_plan 
        WHERE seller_id = ?
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    
    $stmt->execute([$seller_id]);
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($plan) {
        // Format dates
        if ($plan['start_date']) {
            $plan['start_date_formatted'] = date('M d, Y', strtotime($plan['start_date']));
        }
        if ($plan['end_date']) {
            $plan['end_date_formatted'] = date('M d, Y', strtotime($plan['end_date']));
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
}

echo json_encode($response);
?>