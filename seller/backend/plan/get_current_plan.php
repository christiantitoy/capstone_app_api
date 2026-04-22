<?php
// /seller/backend/plan/get_current_plan.php

require_once '/var/www/html/connection/db_connection.php';
require_once __DIR__ . '/../session/auth.php';

header('Content-Type: application/json');

$response = ['success' => false, 'data' => null];

try {

    // seller_id already provided by auth.php
    if (!$seller_id) {
        throw new Exception('Unauthorized');
    }

    // ─────────────────────────────────────────────
    // 1. Get OFFICIAL Plan (Source of Truth)
    // ─────────────────────────────────────────────
    $stmt = $conn->prepare("
        SELECT 
            LOWER(seller_plan) as official_plan,
            COALESCE(seller_billing, 'lifetime') as official_billing
        FROM public.sellers
        WHERE id = ?
    ");
    $stmt->execute([$seller_id]);
    $official = $stmt->fetch(PDO::FETCH_ASSOC);


    // ─────────────────────────────────────────────
    // 2. Get SUBSCRIBED Plan
    // ─────────────────────────────────────────────
    $stmt2 = $conn->prepare("
        SELECT 
            sp.plan as subscribed_plan,
            sp.billing as subscribed_billing,
            sp.status as subscribed_status,
            sp.start_date,
            sp.end_date
        FROM public.sellers_plan sp
        WHERE sp.seller_id = ?
          AND sp.status IN ('active', 'pending')
        ORDER BY sp.created_at DESC 
        LIMIT 1
    ");
    $stmt2->execute([$seller_id]);
    $subscribed = $stmt2->fetch(PDO::FETCH_ASSOC);


    // ─────────────────────────────────────────────
    // 3. Check Rejected Plan
    // ─────────────────────────────────────────────
    $rejected = null;

    if (!$subscribed) {
        $stmt3 = $conn->prepare("
            SELECT status
            FROM public.sellers_plan
            WHERE seller_id = ?
              AND status = 'rejected'
            ORDER BY created_at DESC
            LIMIT 1
        ");

        $stmt3->execute([$seller_id]);
        $rejected = $stmt3->fetch(PDO::FETCH_ASSOC);
    }


    // ─────────────────────────────────────────────
    // 4. Build Response
    // ─────────────────────────────────────────────
    $planData = [];

    // Official Plan
    if ($official) {
        $planData['official_plan'] = $official['official_plan'];
        $planData['official_billing'] = $official['official_billing'];
    } else {
        $planData['official_plan'] = 'bronze';
        $planData['official_billing'] = 'lifetime';
    }


    // ─────────────────────────────────────────────
    // 5. Subscribed Plan
    // ─────────────────────────────────────────────
    if ($subscribed && in_array($subscribed['subscribed_status'], ['active', 'pending'])) {

        $planData['subscribed_plan'] = strtolower($subscribed['subscribed_plan']);
        $planData['subscribed_billing'] = $subscribed['subscribed_billing'];
        $planData['subscribed_status'] = $subscribed['subscribed_status'];
        $planData['start_date'] = $subscribed['start_date'];
        $planData['end_date'] = $subscribed['end_date'];

        if ($subscribed['start_date']) {
            $planData['start_date_formatted'] = 
                date('M d, Y', strtotime($subscribed['start_date']));
        }

        if ($subscribed['end_date']) {
            $planData['end_date_formatted'] = 
                date('M d, Y', strtotime($subscribed['end_date']));
        }

    } else {

        // fallback to official plan
        $planData['subscribed_plan'] = $planData['official_plan'];
        $planData['subscribed_billing'] = $planData['official_billing'];
        $planData['subscribed_status'] = 'active';
        $planData['start_date'] = date('Y-m-d H:i:s');
        $planData['end_date'] = null;
        $planData['start_date_formatted'] = date('M d, Y');

        if ($rejected) {
            $planData['had_rejection'] = true;
        }
    }


    // ─────────────────────────────────────────────
    // 6. Plan Descriptions
    // ─────────────────────────────────────────────
    $descriptions = [
        'bronze' => 'Free forever · 3 employees · Up to 50 products',
        'silver' => '10 employees · 100 products · Featured products',
        'gold' => 'Unlimited employees · Unlimited products · Priority search'
    ];

    $planData['description'] =
        $descriptions[$planData['subscribed_plan']]
        ?? $descriptions['bronze'];


    $response['success'] = true;
    $response['data'] = $planData;

} catch (Exception $e) {

    $response['message'] = $e->getMessage();
    error_log("Get Plan Error: " . $e->getMessage());

} catch (PDOException $e) {

    $response['message'] = 'Database error';
    error_log("Get Plan DB Error: " . $e->getMessage());
}

echo json_encode($response);
?>