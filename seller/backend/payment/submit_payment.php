<?php
// /seller/backend/payment/submit_payment.php
require_once '/var/www/html/connection/db_connection.php';
require_once __DIR__ . '/../session/auth.php';
require_once '/var/www/html/vendor/autoload.php';

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Api\Exception\ApiError;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$response = ['success' => false, 'message' => ''];

try {
    $seller_id = $_SESSION['seller_id'] ?? null;

    if (!$seller_id) {
        throw new Exception('Unauthorized. Please login again.');
    }

    // Get form data
    $plan         = strtolower(trim($_POST['plan'] ?? ''));
    $billing      = strtolower(trim($_POST['billing'] ?? 'monthly'));
    $amount       = floatval($_POST['amount'] ?? 0);
    $gcash_number = trim($_POST['gcash_number'] ?? '');

    if (empty($plan) || empty($gcash_number) || $amount <= 0) {
        throw new Exception('Missing required fields.');
    }

    // Validate inputs
    $valid_plans   = ['bronze', 'silver', 'gold'];
    $valid_billing = ['monthly', 'yearly', 'lifetime'];

    if (!in_array($plan, $valid_plans)) {
        throw new Exception('Invalid plan selected.');
    }
    if (!in_array($billing, $valid_billing)) {
        throw new Exception('Invalid billing period.');
    }

    // === 1. Upload Proof Image to Cloudinary ===
    if (!isset($_FILES['proof_image']) || $_FILES['proof_image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Payment proof image is required.');
    }

    $file = $_FILES['proof_image'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
        throw new Exception('Invalid image format. Only JPG, PNG, and WebP allowed.');
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('Image must be less than 5MB.');
    }

    // Initialize Cloudinary
    Configuration::instance(getenv('CLOUDINARY_URL'));

    $uploadApi = new UploadApi();
    $publicId = 'payment_proof_' . $seller_id . '_' . time();

    $result = $uploadApi->upload($file['tmp_name'], [
        'folder'        => 'capstone_app_images/payment_proofs',
        'public_id'     => $publicId,
        'overwrite'     => true,
        'resource_type' => 'image',
        'quality'       => 'auto',
        'fetch_format'  => 'auto'
    ]);

    $proof_image_url = $result['secure_url'];

    // === 2. Insert into seller_plan_payments ===
    $stmt = $conn->prepare("
        INSERT INTO public.seller_plan_payments 
        (seller_id, seller_plan_id, gcash_number, amount, proof_image_url, status)
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");

    $plan_map = ['bronze' => 1, 'silver' => 2, 'gold' => 3];
    $seller_plan_id = $plan_map[$plan] ?? 1;

    $stmt->execute([
        $seller_id,
        $seller_plan_id,
        $gcash_number,
        $amount,
        $proof_image_url
    ]);

    $payment_id = $conn->lastInsertId();

    // === 3. Insert / Update sellers_plan with correct start_date & end_date ===
    $sql = "
        INSERT INTO public.sellers_plan 
        (seller_id, plan, billing, status, start_date, end_date)
        VALUES (?, ?, ?, 'pending', NOW(), 
            CASE 
                WHEN ? = 'lifetime' THEN NULL
                WHEN ? = 'yearly'   THEN NOW() + INTERVAL '1 year'
                ELSE NOW() + INTERVAL '1 month'
            END
        )
        ON CONFLICT (seller_id) 
        DO UPDATE SET 
            plan = EXCLUDED.plan,
            billing = EXCLUDED.billing,
            status = 'pending',
            start_date = NOW(),
            end_date = EXCLUDED.end_date,
            updated_at = NOW()
    ";

    $stmt2 = $conn->prepare($sql);
    $stmt2->execute([$seller_id, $plan, $billing, $billing, $billing]);

    $response = [
        'success'    => true,
        'message'    => 'Payment proof submitted successfully. Your plan will be activated after admin verification.',
        'payment_id' => $payment_id
    ];

} catch (ApiError $e) {
    $response['message'] = 'Cloudinary upload failed: ' . $e->getMessage();
    error_log("Cloudinary Error: " . $e->getMessage());
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Payment Submit Error: " . $e->getMessage());
} catch (PDOException $e) {
    $response['message'] = 'Database error. Please try again later.';
    error_log("Payment DB Error: " . $e->getMessage());
}

echo json_encode($response);
exit;
?>