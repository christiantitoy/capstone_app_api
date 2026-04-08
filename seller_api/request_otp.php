<?php
// /backend/forgot_password/request_otp.php

session_start();
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? $_POST['email'] ?? null;

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid email address'
    ]);
    exit;
}

try {
    // Check if email exists in buyers table
    $checkUserStmt = $conn->prepare("SELECT id, username FROM buyers WHERE email = ?");
    $checkUserStmt->execute([$email]);
    $user = $checkUserStmt->fetch(PDO::FETCH_ASSOC);
    
    // For security, always return success even if email doesn't exist
    $emailExists = ($user !== false);
    
    if (!$emailExists) {
        // Still return success to prevent email enumeration
        echo json_encode([
            'success' => true,
            'message' => 'If an account exists with this email, you will receive an OTP'
        ]);
        exit;
    }
    
    // Check cooldown (2 minutes)
    $cooldownStmt = $conn->prepare("
        SELECT GREATEST(0, 120 - EXTRACT(EPOCH FROM (NOW() - MAX(last_request_at)))::INTEGER) as remaining_seconds
        FROM password_resets
        WHERE email = ? AND created_at > NOW() - INTERVAL '2 minutes'
    ");
    $cooldownStmt->execute([$email]);
    $cooldown = $cooldownStmt->fetch(PDO::FETCH_ASSOC);
    
    $remainingSeconds = $cooldown['remaining_seconds'] ?? 0;
    
    if ($remainingSeconds > 0) {
        echo json_encode([
            'success' => false,
            'message' => "Please wait {$remainingSeconds} seconds before requesting again",
            'remaining_seconds' => (int)$remainingSeconds
        ]);
        exit;
    }
    
    // Generate 6-digit OTP
    $otpCode = sprintf("%06d", mt_rand(1, 999999));
    
    // Delete any previous unverified OTPs for this email
    $deleteStmt = $conn->prepare("DELETE FROM password_resets WHERE email = ? AND is_verified = FALSE");
    $deleteStmt->execute([$email]);
    
    // Insert new OTP request
    $insertStmt = $conn->prepare("
        INSERT INTO password_resets (email, otp_code, otp_expires_at, last_request_at)
        VALUES (?, ?, NOW() + INTERVAL '10 minutes', NOW())
        RETURNING id
    ");
    $insertStmt->execute([$email, $otpCode]);
    $resetRecord = $insertStmt->fetch(PDO::FETCH_ASSOC);
    
    // Send OTP via Brevo
    $apiKey = getenv('BREVO_API_KEY');
    if (!$apiKey) {
        error_log("BREVO_API_KEY not set.");
        echo json_encode([
            'success' => false,
            'message' => 'Email service configuration error'
        ]);
        exit;
    }
    
    // Prepare HTML email with OTP
    $htmlContent = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; background: #f4f4f4; padding: 20px; }
            .container { max-width: 500px; background: white; margin: auto; padding: 30px; border-radius: 8px; box-shadow: 0px 2px 10px rgba(0,0,0,0.1); text-align: center; }
            .otp-code { font-size: 36px; font-weight: bold; color: #FF6A00; padding: 15px; background: #fff0e6; display: inline-block; letter-spacing: 8px; border-radius: 8px; margin: 20px 0; }
            .warning { color: #666; font-size: 12px; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>Password Reset Request</h2>
            <p>Hello {$user['username']},</p>
            <p>You requested to reset your password. Use the OTP code below:</p>
            <div class='otp-code'>{$otpCode}</div>
            <p>This code will expire in <strong>10 minutes</strong>.</p>
            <p>If you didn't request this, please ignore this email.</p>
            <div class='warning'>
                <p>Never share this OTP with anyone.</p>
                <p>This is an automated message, please do not reply.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Prepare Brevo payload
    $brevoData = [
        "sender" => [
            "name" => "PalitOra",
            "email" => "christiantitoy@gmail.com"
        ],
        "to" => [
            ["email" => $email, "name" => $user['username']]
        ],
        "subject" => "Password Reset OTP - PalitOra",
        "htmlContent" => $htmlContent,
        "textContent" => "Your OTP code for password reset is: $otpCode\n\nThis code expires in 10 minutes.\n\nNever share this OTP with anyone."
    ];
    
    // Send email using cURL
    $ch = curl_init("https://api.brevo.com/v3/smtp/email");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "accept: application/json",
        "content-type: application/json",
        "api-key: $apiKey"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($brevoData));
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        error_log("OTP email failed for {$email}: $error");
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send OTP. Please try again.'
        ]);
        exit;
    }
    
    $jsonResponse = json_decode($response, true);
    
    if (isset($jsonResponse['messageId'])) {
        // Return success with email (for step 2 to use)
        echo json_encode([
            'success' => true,
            'message' => 'OTP sent to your email',
            'email' => $email,
            'otp_expires_in' => 600 // 10 minutes in seconds
        ]);
        exit;
    } else {
        error_log("Brevo API error: " . $response);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send OTP. Please try again.'
        ]);
        exit;
    }
    
} catch (PDOException $e) {
    error_log("Request OTP error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again later.'
    ]);
    exit;
}
?>