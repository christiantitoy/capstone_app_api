<?php
// /employee_api/reset_password.php

session_start();
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? null;
$newPassword = $input['new_password'] ?? null;

// Validate email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email address'
    ]);
    exit;
}

// Validate password
if (empty($newPassword)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a new password'
    ]);
    exit;
}

// Check password length (at least 8 characters)
if (strlen($newPassword) < 8) {
    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 8 characters long'
    ]);
    exit;
}

try {
    // Check if email exists in employees table
    $checkUserStmt = $conn->prepare("SELECT id, full_name FROM employees WHERE email = ? AND is_removed = FALSE");
    $checkUserStmt->execute([$email]);
    $user = $checkUserStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'Email not found. Please register first.'
        ]);
        exit;
    }
    
    // Check if OTP is verified for this email
    $otpStmt = $conn->prepare("
        SELECT id, is_verified 
        FROM password_resets 
        WHERE email = ? 
        AND is_verified = TRUE
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $otpStmt->execute([$email]);
    $verifiedOtp = $otpStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$verifiedOtp) {
        echo json_encode([
            'success' => false,
            'message' => 'Please verify your OTP first before resetting password'
        ]);
        exit;
    }
    
    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password in employees table
    $updateStmt = $conn->prepare("
        UPDATE employees 
        SET password = ? 
        WHERE email = ?
    ");
    $updateStmt->execute([$hashedPassword, $email]);
    
    // Delete all OTP records for this email after successful reset
    $deleteOtpStmt = $conn->prepare("
        DELETE FROM password_resets 
        WHERE email = ?
    ");
    $deleteOtpStmt->execute([$email]);
    
    // Return success
    echo json_encode([
        'success' => true,
        'message' => 'Password reset successfully! You can now login with your new password.',
        'email' => $email,
        'user_id' => $user['id']
    ]);
    
} catch (PDOException $e) {
    error_log("Reset password error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again later.'
    ]);
}
?>