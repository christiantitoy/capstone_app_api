<?php
// /seller/backend/auth/sellerSignup-process.php

session_start();
require_once '/var/www/html/connection/db_connection.php';

// Check DB connection
if (!isset($conn)) {
    die("Database connection failed");
}

$redirect = '/seller/ui/signup.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $redirect");
    exit;
}

// Get inputs
$full_name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$terms = $_POST['terms'] ?? '';

$errors = [];

// Required fields
if (empty($full_name)) $errors[] = "Full name is required.";
if (empty($email)) $errors[] = "Email is required.";
if (empty($password)) $errors[] = "Password is required.";
if (empty($confirm_password)) $errors[] = "Please confirm your password.";

// Terms check
if (!$terms) {
    $errors[] = "You must agree to the Terms of Service and Privacy Policy.";
}

// Show errors if any
if (!empty($errors)) {
    $_SESSION['old_input'] = $_POST;
    $error_string = implode(' ', $errors);
    header("Location: $redirect?error=" . urlencode($error_string));
    exit;
}

// Password match
if ($password !== $confirm_password) {
    $_SESSION['old_input'] = $_POST;
    header("Location: $redirect?error=" . urlencode("Passwords do not match"));
    exit;
}

// Password length
if (strlen($password) < 8) {
    $_SESSION['old_input'] = $_POST;
    header("Location: $redirect?error=" . urlencode("Password must be at least 8 characters long"));
    exit;
}

// Email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['old_input'] = $_POST;
    header("Location: $redirect?error=" . urlencode("Invalid email format"));
    exit;
}

try {

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM sellers WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        $_SESSION['old_input'] = $_POST;
        header("Location: $redirect?error=" . urlencode("This email is already registered"));
        exit;
    }

    // Generate token
    $token = bin2hex(random_bytes(32));

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert seller with token
    $stmt = $conn->prepare("
        INSERT INTO sellers (full_name, email, password, token, resend_at)
        VALUES (?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $full_name,
        $email,
        $hashedPassword,
        $token
    ]);

    // Clear form session
    unset($_SESSION['old_input']);

    // Store token in session for verification
    $_SESSION['verification_token'] = $token;
    $_SESSION['verification_email'] = $email;

    // Redirect to email sender with token instead of just email
    header("Location: /seller/backend/auth/send-verification-email.php?email=" . urlencode($email) . "&token=" . urlencode($token));
    exit;

} catch (PDOException $e) {

    $_SESSION['old_input'] = $_POST;

    $msg = "Database error. Please try again later.";

    error_log("Signup error: " . $e->getMessage());

    header("Location: $redirect?error=" . urlencode($msg));
    exit;
}
?>