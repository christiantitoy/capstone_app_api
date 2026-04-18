<?php
// /seller/backend/employees/check_employee_limits.php
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
    
    // Define employee limits per plan
    $plan_limits = [
        'bronze' => 3,
        'silver' => 10,
        'gold' => null // unlimited
    ];

    $max_employees = $plan_limits[$seller_plan] ?? 3;

    // If gold plan, no limits - skip checks
    if ($seller_plan === 'gold') {
        $response['success'] = true;
        $response['message'] = 'Gold plan - unlimited employees';
        $response['data'] = [
            'plan' => 'gold',
            'max_employees' => 'unlimited',
            'current_active' => null,
            'employees_on_hold' => 0
        ];
        echo json_encode($response);
        exit;
    }

    // Get all active employees for this seller (excluding removed ones)
    $stmt = $conn->prepare("
        SELECT 
            id,
            full_name,
            email,
            role,
            status,
            created_at
        FROM public.employees
        WHERE seller_id = ? 
            AND is_removed = false
        ORDER BY created_at ASC
    ");
    $stmt->execute([$seller_id]);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_employees = count($employees);
    $active_employees = array_filter($employees, function($emp) {
        return $emp['status'] === 'active';
    });
    $active_count = count($active_employees);
    
    $employees_put_on_hold = 0;
    $employees_reactivated = 0;

    // If active employees exceed the limit
    if ($active_count > $max_employees) {
        // Sort employees by created_at (oldest first - they keep active status)
        // Newest employees will be put on hold
        
        $employees_by_date = $employees;
        usort($employees_by_date, function($a, $b) {
            return strtotime($a['created_at']) - strtotime($b['created_at']);
        });
        
        // Keep the first $max_employees as active, put the rest on hold
        $keep_active_ids = array_slice(array_column($employees_by_date, 'id'), 0, $max_employees);
        
        // Begin transaction
        $conn->beginTransaction();
        
        // Put excess employees on hold
        $updateStmt = $conn->prepare("
            UPDATE public.employees 
            SET status = 'on_hold', updated_at = NOW()
            WHERE seller_id = ? 
                AND status = 'active'
                AND id NOT IN (" . implode(',', array_fill(0, count($keep_active_ids), '?')) . ")
                AND is_removed = false
        ");
        
        $params = array_merge([$seller_id], $keep_active_ids);
        $updateStmt->execute($params);
        $employees_put_on_hold = $updateStmt->rowCount();
        
        $conn->commit();
        
        $response['message'] = "$employees_put_on_hold employee(s) put on hold due to plan limits.";
    } 
    // If active employees are under limit, check if any on_hold can be reactivated
    else if ($active_count < $max_employees) {
        $available_slots = $max_employees - $active_count;
        
        // Get employees on hold, oldest first
        $stmt = $conn->prepare("
            SELECT id, full_name
            FROM public.employees
            WHERE seller_id = ? 
                AND status = 'on_hold'
                AND is_removed = false
            ORDER BY created_at ASC
            LIMIT ?
        ");
        $stmt->execute([$seller_id, $available_slots]);
        $on_hold_employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($on_hold_employees)) {
            $conn->beginTransaction();
            
            $reactivate_ids = array_column($on_hold_employees, 'id');
            $updateStmt = $conn->prepare("
                UPDATE public.employees 
                SET status = 'active', updated_at = NOW()
                WHERE id IN (" . implode(',', array_fill(0, count($reactivate_ids), '?')) . ")
            ");
            $updateStmt->execute($reactivate_ids);
            $employees_reactivated = $updateStmt->rowCount();
            
            $conn->commit();
            
            $response['message'] = "$employees_reactivated employee(s) reactivated.";
        } else {
            $response['message'] = 'Employee limits are within plan allowance.';
        }
    } else {
        $response['message'] = 'Employee limits are within plan allowance.';
    }

    // Get updated counts
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'on_hold' THEN 1 ELSE 0 END) as on_hold,
            SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive
        FROM public.employees
        WHERE seller_id = ? AND is_removed = false
    ");
    $stmt->execute([$seller_id]);
    $counts = $stmt->fetch(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['data'] = [
        'plan' => $seller_plan,
        'max_employees' => $max_employees,
        'total_employees' => (int)$counts['total'],
        'active_employees' => (int)$counts['active'],
        'on_hold_employees' => (int)$counts['on_hold'],
        'inactive_employees' => (int)$counts['inactive'],
        'employees_put_on_hold' => $employees_put_on_hold,
        'employees_reactivated' => $employees_reactivated
    ];

} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    $response['message'] = $e->getMessage();
    error_log("Employee Limit Check Error: " . $e->getMessage());
} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    $response['message'] = 'Database error. Please try again later.';
    error_log("Employee Limit DB Error: " . $e->getMessage());
}

echo json_encode($response);
?>