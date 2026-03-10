<?php
// /seller/backend/auth/resend-verification.php

session_start();
require_once '/var/www/html/connection/db_connection.php';

// Get email and token from URL
$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';

if (empty($email) || empty($token)) {
    header("Location: /seller/ui/signup.php");
    exit;
}

try {
    // Check if account exists and get resend_at
    $stmt = $conn->prepare("SELECT id, is_confirmed, token, resend_at FROM sellers WHERE email = ? AND token = ?");
    $stmt->execute([$email, $token]);
    $seller = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$seller) {
        header("Location: /seller/ui/signup.php?error=invalid_account");
        exit;
    }

    // Check if already verified
    if ($seller['is_confirmed']) {
        header("Location: /seller/ui/login.php?verified=already");
        exit;
    }

    // Check cooldown period (5 minutes)
    if ($seller['resend_at']) {
        $lastResend = strtotime($seller['resend_at']);
        $now = time();
        $timeDiff = $now - $lastResend;
        
        // If less than 5 minutes (300 seconds) have passed
        if ($timeDiff < 300) {
            $minutesLeft = ceil((300 - $timeDiff) / 60);
            $error = "Please wait $minutesLeft minute(s) before requesting another verification email.";
            header("Location: /seller/ui/emailVerification.php?email=" . urlencode($email) . "&token=" . urlencode($token) . "&error=" . urlencode($error));
            exit;
        }
    }

    // If we get here, we can resend the email
    // Redirect to send-verification-email.php which will update resend_at
    header("Location: /seller/backend/auth/send-verification-email.php?email=" . urlencode($email) . "&token=" . urlencode($token));
    exit;

} catch (PDOException $e) {
    error_log("Resend verification error: " . $e->getMessage());
    header("Location: /seller/ui/signup.php?error=system_error");
    exit;
}
?>