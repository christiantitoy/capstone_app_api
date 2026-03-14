<?php
// /seller/backend/employees/delete-employee.php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/connection/db_connection.php';

// Set header to return JSON
header('Content-Type: application/json');

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access. Please log in as a seller.'
    ]);
    exit;
}

$seller_id = $_SESSION['user_id'];

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
    $check_stmt = $conn->prepare("
        SELECT id, full_name, is_removed 
        FROM employees 
        WHERE id = ? AND seller_id = ?
    ");
    $check_stmt->execute([$employee_id, $seller_id]);
    $employee = $check_stmt->fetch();
    
    if (!$employee) {
        echo json_encode([
            'success' => false,
            'message' => 'Employee not found or you do not have permission to delete this employee.'
        ]);
        exit;
    }
    
    // Check if already removed
    if ($employee['is_removed'] == 1) {
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
        // Optional: Log the action
        $log_stmt = $conn->prepare("
            INSERT INTO seller_activity_log (seller_id, action, details, created_at)
            VALUES (?, 'delete_employee', ?, NOW())
        ");
        $log_stmt->execute([$seller_id, 'Removed employee: ' . $employee['full_name']]);
        
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