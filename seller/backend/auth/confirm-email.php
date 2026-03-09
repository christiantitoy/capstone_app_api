<?php
// /seller/backend/auth/confirm-email.php

session_start();
require_once '/var/www/html/connection/db_connection.php';

$email = $_GET['email'] ?? '';

if (empty($email)) {
    header("Location: /seller/ui/login.php?error=invalid_link");
    exit;
}

try {

    // Check if account exists
    $stmt = $conn->prepare("SELECT is_confirmed FROM sellers WHERE email = ?");
    $stmt->execute([$email]);

    $seller = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$seller) {
        header("Location: /seller/ui/login.php?error=account_not_found");
        exit;
    }

    // If already verified
    if ($seller['is_confirmed']) {
        header("Location: /seller/ui/login.php?verified=already");
        exit;
    }

    // Update verification
    $stmt = $conn->prepare("
        UPDATE sellers 
        SET is_confirmed = true 
        WHERE email = ?
    ");

    $stmt->execute([$email]);

    header("Location: /seller/ui/login.php?verified=1");
    exit;

} catch (PDOException $e) {

    error_log("Verification error: " . $e->getMessage());

    header("Location: /seller/ui/login.php?error=verification_failed");
    exit;
}
?>