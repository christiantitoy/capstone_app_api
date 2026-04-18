<?php
// /seller/backend/profile/get_seller_profile.php
require_once '/var/www/html/connection/db_connection.php';
require_once __DIR__ . '/../session/auth.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    $seller_id = $_SESSION['seller_id'] ?? null;

    if (!$seller_id) {
        throw new Exception('Unauthorized. Please login again.');
    }

    // Get seller data from sellers table
    $stmt = $conn->prepare("
        SELECT 
            id,
            full_name,
            email,
            is_confirmed,
            created_at,
            seller_plan,
            seller_billing,
            approval_status
        FROM public.sellers
        WHERE id = ?
    ");
    $stmt->execute([$seller_id]);
    $seller = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$seller) {
        throw new Exception('Seller not found.');
    }

    // Get store data from stores table
    $stmt2 = $conn->prepare("
        SELECT 
            store_name,
            category,
            description,
            contact_number,
            open_time,
            close_time,
            latitude,
            longitude,
            plus_code,
            logo_url,
            banner_url,
            owner_full_name,
            id_type,
            valid_id_files,
            store_photo_files,
            created_at,
            updated_at
        FROM public.stores
        WHERE seller_id = ?
    ");
    $stmt2->execute([$seller_id]);
    $store = $stmt2->fetch(PDO::FETCH_ASSOC);

    // Format time for display
    if ($store) {
        if ($store['open_time']) {
            $store['open_time_formatted'] = date('h:i A', strtotime($store['open_time']));
        }
        if ($store['close_time']) {
            $store['close_time_formatted'] = date('h:i A', strtotime($store['close_time']));
        }
        
        // Parse JSON arrays if stored as JSON strings
        if ($store['valid_id_files'] && is_string($store['valid_id_files'])) {
            $store['valid_id_files'] = json_decode($store['valid_id_files'], true) ?: [];
        }
        if ($store['store_photo_files'] && is_string($store['store_photo_files'])) {
            $store['store_photo_files'] = json_decode($store['store_photo_files'], true) ?: [];
        }
    }

    // Format dates
    $seller['created_at_formatted'] = date('M d, Y', strtotime($seller['created_at']));
    $seller['member_since'] = date('F Y', strtotime($seller['created_at']));

    // Plan display names
    $plan_display = [
        'Bronze' => 'Bronze Plan',
        'Silver' => 'Silver Plan',
        'Gold' => 'Gold Plan'
    ];
    $seller['plan_display'] = $plan_display[$seller['seller_plan']] ?? 'Bronze Plan';

    // Billing display
    $billing_display = [
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
        'lifetime' => 'Lifetime'
    ];
    $seller['billing_display'] = $billing_display[$seller['seller_billing']] ?? 'Lifetime';

    // Approval status display
    $status_display = [
        'pending' => ['text' => 'Pending Approval', 'class' => 'warning'],
        'approved' => ['text' => 'Approved', 'class' => 'success'],
        'rejected' => ['text' => 'Rejected', 'class' => 'danger']
    ];
    $seller['approval_status_display'] = $status_display[$seller['approval_status']] ?? ['text' => 'Pending', 'class' => 'warning'];

    $response['success'] = true;
    $response['data'] = [
        'seller' => $seller,
        'store' => $store ?: null
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Get Seller Profile Error: " . $e->getMessage());
} catch (PDOException $e) {
    $response['message'] = 'Database error. Please try again later.';
    error_log("Get Seller Profile DB Error: " . $e->getMessage());
}

echo json_encode($response);
?>