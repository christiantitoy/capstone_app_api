<?php
// /seller/backend/employees/delete-employee.php

// Include auth first to ensure user is logged in and get seller_id
require_once $_SERVER['DOCUMENT_ROOT'] . '/seller/backend/session/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/connection/db_connection.php';

// Set header to return JSON
header('Content-Type: application/json');

// Check if employee_id was provided
if (!isset($_POST['employee_id']) || empty($_POST['employee_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Employee ID is required.'
    ]);
    exit;
}

$employee_id = filter_var($_POST['employee_id'], FILTER_VALIDATE_INT);

if (!$employee_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid employee ID.'
    ]);
    exit;
}

try {
    // First, verify that this employee belongs to the logged-in seller
    // $seller_id is already set by auth.php
    $check_stmt = $conn->prepare("
        SELECT id, full_name, is_removed 
        FROM employees 
        WHERE id = ? AND seller_id = ?
    ");
    $check_stmt->execute([$employee_id, $seller_id]);
    $employee = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$employee) {
        echo json_encode([
            'success' => false,
            'message' => 'Employee not found or you do not have permission to delete this employee.'
        ]);
        exit;
    }
    
    // Check if already removed (comparing with TRUE)
    if (isset($employee['is_removed']) && $employee['is_removed'] == true) {
        echo json_encode([
            'success' => false,
            'message' => 'This employee has already been removed.'
        ]);
        exit;
    }
    
    // Soft delete - set is_removed to TRUE
    $delete_stmt = $conn->prepare("
        UPDATE employees 
        SET is_removed = TRUE, 
            status = 'inactive',
            updated_at = NOW()
        WHERE id = ? AND seller_id = ?
    ");
    
    $result = $delete_stmt->execute([$employee_id, $seller_id]);
    
    if ($result) {
        // Optional: Log the action (if you have this table)
        // You can remove this if you don't have the seller_activity_log table
        try {
            $log_stmt = $conn->prepare("
                INSERT INTO seller_activity_log (seller_id, action, details, created_at)
                VALUES (?, 'delete_employee', ?, NOW())
            ");
            $log_stmt->execute([$seller_id, 'Removed employee: ' . $employee['full_name']]);
        } catch (PDOException $e) {
            // Log table might not exist, just continue
            error_log("Activity log error (non-critical): " . $e->getMessage());
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Employee removed successfully.',
            'employee_name' => $employee['full_name']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to remove employee. Please try again.'
        ]);
    }
    
} catch (PDOException $e) {
    // Log error for debugging
    error_log("Delete employee error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again later.'
    ]);
}
?>