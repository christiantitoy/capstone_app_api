<?php
// /seller/backend/auth/confirm-email.php

session_start();
require_once '/var/www/html/connection/db_connection.php';

// Get email and token from POST (since we changed to form POST)
$email = $_POST['email'] ?? '';
$token = $_POST['token'] ?? '';

if (empty($email) || empty($token)) {
    header("Location: /seller/ui/login.php?error=invalid_link");
    exit;
}

try {

    // Check if account exists with matching token
    $stmt = $conn->prepare("SELECT id, is_confirmed, token FROM sellers WHERE email = ?");
    $stmt->execute([$email]);

    $seller = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$seller) {
        header("Location: /seller/ui/login.php?error=account_not_found");
        exit;
    }

    // Verify token matches
    if ($seller['token'] !== $token) {
        error_log("Token mismatch for email: $email");
        header("Location: /seller/ui/login.php?error=invalid_token");
        exit;
    }

    // If already verified
    if ($seller['is_confirmed']) {
        header("Location: /seller/ui/login.php?verified=already");
        exit;
    }

    // Update verification and clear token
    $stmt = $conn->prepare("
        UPDATE sellers 
        SET is_confirmed = true, 
            token = NULL,
            updated_at = NOW()
        WHERE email = ? AND token = ?
    ");

    $stmt->execute([$email, $token]);

    // Check if update was successful
    if ($stmt->rowCount() > 0) {
        // Optional: Store success message in session
        $_SESSION['verification_success'] = true;
        header("Location: /seller/ui/login.php?verified=1");
    } else {
        header("Location: /seller/ui/login.php?error=verification_failed");
    }
    exit;

} catch (PDOException $e) {

    error_log("Verification error: " . $e->getMessage());

    header("Location: /seller/ui/login.php?error=verification_failed");
    exit;
}
?>