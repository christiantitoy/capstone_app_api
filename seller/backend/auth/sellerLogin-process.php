<?php
// /seller/backend/auth/sellerLogin-process.php
session_start();

require_once '/var/www/html/connection/db_connection.php';

// Check if connection was successful
if (!isset($conn)) {
    die("Database connection failed");
}

// Redirect back to login page
$redirect = '/seller/ui/login.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $redirect");
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']) ? true : false;

$errors = [];

if (empty($email)) {
    $errors[] = "Email is required.";
}

if (empty($password)) {
    $errors[] = "Password is required.";
}

if (!empty($errors)) {
    $_SESSION['login_errors'] = $errors;
    $_SESSION['login_email'] = $email; // Remember email for re-fill
    header("Location: $redirect");
    exit;
}

try {
    // Get seller by email - include is_confirmed field
    $stmt = $conn->prepare("SELECT id, full_name, email, password, is_confirmed FROM sellers WHERE email = ?");
    $stmt->execute([$email]);
    $seller = $stmt->fetch();
    
    if ($seller && password_verify($password, $seller['password'])) {
        // Check if email is confirmed
        if ($seller['is_confirmed'] == 1) {
            // Email is confirmed - login successful
            $_SESSION['seller_id'] = $seller['id'];
            $_SESSION['seller_name'] = $seller['full_name'];
            $_SESSION['seller_email'] = $seller['email'];
            $_SESSION['logged_in'] = true;
            
            // Set remember me cookie if requested (30 days)
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expiry = time() + (86400 * 30); // 30 days
                
                setcookie('remember_token', $token, $expiry, '/', '', false, true);
                $_SESSION['remember_token'] = $token;
            }
            
            // Clear any old error messages
            unset($_SESSION['login_errors']);
            unset($_SESSION['login_email']);
            
            // Redirect to dashboard
            header("Location: /seller/ui/dashboard.php");
            exit;
        } else {
            // Email not confirmed - redirect to email verification page
            $_SESSION['login_errors'] = ["Please verify your email address first. A verification email has been sent to your inbox."];
            $_SESSION['login_email'] = $email;
            
            // Optional: Resend verification email here or provide link
            header("Location: /seller/ui/emailVerification.php?email=" . urlencode($email) . "&resend=true");
            exit;
        }
    } else {
        // Login failed - invalid credentials
        $_SESSION['login_errors'] = ["Invalid email or password."];
        $_SESSION['login_email'] = $email;
        header("Location: $redirect");
        exit;
    }
    
} catch (PDOException $e) {
    // Log error and show generic message
    error_log("Login error: " . $e->getMessage());
    $_SESSION['login_errors'] = ["An error occurred. Please try again later."];
    header("Location: $redirect");
    exit;
}
?>