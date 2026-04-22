<?php
// /seller/backend/plan/update_to_bronze_plan.php
require_once '/var/www/html/connection/db_connection.php';
require_once __DIR__ . '/../session/auth.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $seller_id = $_SESSION['seller_id'] ?? null;

    if (!$seller_id) {
        throw new Exception('Unauthorized. Please login again.');
    }

    $conn->beginTransaction();

    // 1. Check if seller already has a plan record in sellers_plan
    $checkStmt = $conn->prepare("SELECT id FROM public.sellers_plan WHERE seller_id = ?");
    $checkStmt->execute([$seller_id]);
    $existingPlan = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingPlan) {
        // Update existing plan to Bronze lifetime (active immediately)
        $updateStmt = $conn->prepare("
            UPDATE public.sellers_plan 
            SET plan = 'bronze', 
                billing = 'lifetime', 
                status = 'active',
                start_date = NOW(),
                end_date = NULL,
                updated_at = NOW()
            WHERE seller_id = ?
        ");
        $updateStmt->execute([$seller_id]);
        $seller_plan_id = $existingPlan['id'];
    } else {
        // Insert new Bronze lifetime plan (active immediately)
        $insertStmt = $conn->prepare("
            INSERT INTO public.sellers_plan 
            (seller_id, plan, billing, status, start_date, end_date, created_at, updated_at)
            VALUES (?, 'bronze', 'lifetime', 'active', NOW(), NULL, NOW(), NOW())
        ");
        $insertStmt->execute([$seller_id]);
        $seller_plan_id = $conn->lastInsertId();
    }

    // 2. Update sellers table with Bronze plan
    $updateSellerStmt = $conn->prepare("
        UPDATE public.sellers 
        SET seller_plan = 'Bronze',
            seller_billing = 'lifetime',
            updated_at = NOW()
        WHERE id = ?
    ");
    $updateSellerStmt->execute([$seller_id]);

    // 3. No need to insert into seller_plan_payments since it's free

    $conn->commit();

    $response = [
        'success' => true,
        'message' => 'Your plan has been updated to Bronze (Free) successfully!'
    ];

} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    $response['message'] = $e->getMessage();
    error_log("Update to Bronze Error: " . $e->getMessage());
} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    $response['message'] = 'Database error. Please try again later.';
    error_log("Update to Bronze DB Error: " . $e->getMessage());
}

echo json_encode($response);
exit;
?>