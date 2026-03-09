<?php
session_start();

require_once '/var/www/html/connection/db_connection.php';

// Check if connection was successful
if (!isset($conn)) {
    die("Database connection failed");
}

$redirect = '/seller/backend/auth/signup.php';  

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
    $_SESSION['old_input'] = $_POST;  // Store old input in session
    $error_string = implode(' ', $errors);
    header("Location: $redirect?error=" . urlencode($error_string));
    exit;
}

if ($password !== $confirm_password) {
    $_SESSION['old_input'] = $_POST;
    header("Location: $redirect?error=" . urlencode("Passwords do not match"));
    exit;
}

if (strlen($password) < 8) {
    $_SESSION['old_input'] = $_POST;
    header("Location: $redirect?error=" . urlencode("Password must be at least 8 characters long"));
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['old_input'] = $_POST;
    header("Location: $redirect?error=" . urlencode("Invalid email format"));
    exit;
}

try {
    // Check if email exists
    $stmt = $conn->prepare("SELECT 1 FROM sellers WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['old_input'] = $_POST;
        header("Location: $redirect?error=" . urlencode("This email is already registered"));
        exit;
    }

    // Create account
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO sellers (full_name, email, password)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$full_name, $email, $hashed]);

    // Success - clear old input
    unset($_SESSION['old_input']);
    header("Location: $redirect?success=" . urlencode("Account created successfully! You can now log in."));
    exit;

} catch (PDOException $e) {
    $_SESSION['old_input'] = $_POST;
    $msg = "Database error. Please try again later.";
    error_log("Signup error: " . $e->getMessage());
    header("Location: $redirect?error=" . urlencode($msg));
    exit;
}
?>