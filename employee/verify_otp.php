<?php
// /employee_api/verify_otp.php

session_start();
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? null;
$otpCode = $input['otp_code'] ?? null;

// Validate input
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid email address'
    ]);
    exit;
}

if (empty($otpCode) || !preg_match('/^\d{6}$/', $otpCode)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid 6-digit OTP code'
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
    
    // Find the OTP record
    $otpStmt = $conn->prepare("
        SELECT id, otp_code, otp_expires_at, is_verified 
        FROM password_resets 
        WHERE email = ? 
        AND is_verified = FALSE
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $otpStmt->execute([$email]);
    $otpRecord = $otpStmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if OTP exists
    if (!$otpRecord) {
        echo json_encode([
            'success' => false,
            'message' => 'No OTP request found. Please request a new OTP.'
        ]);
        exit;
    }
    
    // Check if OTP is expired
    $currentTime = new DateTime();
    $expiresAt = new DateTime($otpRecord['otp_expires_at']);
    
    if ($currentTime > $expiresAt) {
        echo json_encode([
            'success' => false,
            'message' => 'OTP has expired. Please request a new OTP.'
        ]);
        exit;
    }
    
    // Verify OTP code
    if ($otpRecord['otp_code'] !== $otpCode) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid OTP code. Please try again.'
        ]);
        exit;
    }
    
    // Mark OTP as verified
    $updateStmt = $conn->prepare("
        UPDATE password_resets 
        SET is_verified = TRUE 
        WHERE id = ?
    ");
    $updateStmt->execute([$otpRecord['id']]);
    
    // Return success
    echo json_encode([
        'success' => true,
        'message' => 'OTP verified successfully!',
        'email' => $email,
        'user_id' => $user['id']
    ]);
    
} catch (PDOException $e) {
    error_log("Verify OTP error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again later.'
    ]);
}
?>