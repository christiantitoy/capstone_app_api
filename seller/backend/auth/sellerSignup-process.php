<?php
session_start();

require_once '/var/www/html/connection/db_connection.php';

$redirect = 'sellerSignup.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $redirect");
    exit;
}

$full_name        = trim($_POST['name'] ?? '');
$email            = trim($_POST['email'] ?? '');
$password         = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$errors = [];

if (empty($full_name))        $errors[] = "Full name is required.";
if (empty($email))            $errors[] = "Email is required.";
if (empty($password))         $errors[] = "Password is required.";
if (empty($confirm_password)) $errors[] = "Please confirm your password.";

if (!empty($errors)) {
    $_SESSION['form_data'] = $_POST;
    header("Location: $redirect?" . http_build_query(['error' => implode(' ', $errors)]));
    exit;
}

if ($password !== $confirm_password) {
    $_SESSION['form_data'] = $_POST;
    header("Location: $redirect?error=Passwords+do+not+match");
    exit;
}

if (strlen($password) < 8) {
    $_SESSION['form_data'] = $_POST;
    header("Location: $redirect?error=Password+must+be+at+least+8+characters+long");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['form_data'] = $_POST;
    header("Location: $redirect?error=Invalid+email+format");
    exit;
}

try {
    // Check if email exists
    $stmt = $pdo->prepare("SELECT 1 FROM sellers WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['form_data'] = $_POST;
        header("Location: $redirect?error=This+email+is+already+registered");
        exit;
    }

    // Create account
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO sellers (full_name, email, password)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$full_name, $email, $hashed]);

    // Success
    header("Location: $redirect?success=Account+created+successfully!+You+can+now+log+in.");
    exit;

} catch (PDOException $e) {
    $_SESSION['form_data'] = $_POST;
    $msg = "Database error. Please try again later.";
    // In production: log $e->getMessage() instead of showing to user
    header("Location: $redirect?error=" . urlencode($msg));
    exit;
}