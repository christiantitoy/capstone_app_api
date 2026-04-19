<?php
// /employee_api/request_otp.php

session_start();
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? null;

// Validate email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid email address'
    ]);
    exit;
}

try {
    // Check if email exists in employees table
    $checkUserStmt = $conn->prepare("SELECT id, full_name FROM employees WHERE email = ? AND is_removed = FALSE");
    $checkUserStmt->execute([$email]);
    $user = $checkUserStmt->fetch(PDO::FETCH_ASSOC);
    
    // Return error if email not found
    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'Email not found. Please register first.'
        ]);
        exit;
    }
    
    // Check if there's a recent OTP request (within 2 minutes)
    $cooldownStmt = $conn->prepare("
        SELECT id, EXTRACT(EPOCH FROM (NOW() - last_request_at)) as seconds_since_last
        FROM password_resets 
        WHERE email = ? 
        AND created_at > NOW() - INTERVAL '2 minutes'
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $cooldownStmt->execute([$email]);
    $recentRequest = $cooldownStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($recentRequest && $recentRequest['seconds_since_last'] < 120) {
        $remainingSeconds = 120 - (int)$recentRequest['seconds_since_last'];
        echo json_encode([
            'success' => false,
            'message' => "Please wait {$remainingSeconds} seconds before requesting again"
        ]);
        exit;
    }
    
    // Generate 6-digit OTP
    $otpCode = sprintf("%06d", mt_rand(1, 999999));
    
    // Delete old unverified OTPs
    $deleteStmt = $conn->prepare("DELETE FROM password_resets WHERE email = ? AND is_verified = FALSE");
    $deleteStmt->execute([$email]);
    
    // Insert new OTP
    $insertStmt = $conn->prepare("
        INSERT INTO password_resets (email, otp_code, otp_expires_at, last_request_at, created_at)
        VALUES (?, ?, NOW() + INTERVAL '10 minutes', NOW(), NOW())
        RETURNING id
    ");
    $insertStmt->execute([$email, $otpCode]);
    $newRecord = $insertStmt->fetch(PDO::FETCH_ASSOC);
    
    // Send OTP email via Brevo
    $apiKey = getenv('BREVO_API_KEY');
    if (!$apiKey) {
        error_log("BREVO_API_KEY not set");
        echo json_encode([
            'success' => false,
            'message' => 'Email service error. Please try again later.'
        ]);
        exit;
    }
    
    // Email content
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
            <p>Hello {$user['full_name']},</p>
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
            ["email" => $email, "name" => $user['full_name']]
        ],
        "subject" => "Password Reset OTP - PalitOra",
        "htmlContent" => $htmlContent,
        "textContent" => "Your OTP code for password reset is: $otpCode\n\nThis code expires in 10 minutes."
    ];
    
    // Send email
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
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
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
    
    if ($httpCode === 201 || $httpCode === 200) {
        echo json_encode([
            'success' => true,
            'message' => 'OTP sent to your email',
            'id' => $newRecord['id'],
            'email' => $email
        ]);
    } else {
        error_log("Brevo API error: " . $response);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send OTP. Please try again.'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again later.'
    ]);
}
?>