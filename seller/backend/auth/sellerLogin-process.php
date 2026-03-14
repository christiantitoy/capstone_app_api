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
    // Get seller by email - include is_confirmed, setup_shop, AND seller_plan fields
    $stmt = $conn->prepare("SELECT id, full_name, email, password, is_confirmed, setup_shop, token, seller_plan FROM sellers WHERE email = ?");
    $stmt->execute([$email]);
    $seller = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($seller && password_verify($password, $seller['password'])) {
        // Check if email is confirmed
        if ($seller['is_confirmed'] == 1) {
            // Email is confirmed - check shop setup status
            if ($seller['setup_shop'] == 1) {
                // Shop is set up - login successful and redirect to dashboard
                $_SESSION['seller_id'] = $seller['id'];
                $_SESSION['seller_name'] = $seller['full_name'];
                $_SESSION['seller_email'] = $seller['email'];
                $_SESSION['seller_plan'] = $seller['seller_plan'] ?? 'free'; // Store plan with default
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
                // Email confirmed but shop not set up - redirect to shop info page
                $_SESSION['seller_id'] = $seller['id'];
                $_SESSION['seller_name'] = $seller['full_name'];
                $_SESSION['seller_email'] = $seller['email'];
                $_SESSION['seller_plan'] = $seller['seller_plan'] ?? 'free'; // Store plan with default
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
                
                // Redirect to shop setup page
                header("Location: /seller/ui/shop-form.php");
                exit;
            }
        } else {
            // Email not confirmed - check if token exists or generate new one
            $token = $seller['token'];
            
            // If no token exists, generate a new one and update the database
            if (empty($token)) {
                $token = bin2hex(random_bytes(32));
                
                // Update seller with new token and resend_at timestamp
                $updateStmt = $conn->prepare("UPDATE sellers SET token = ?, resend_at = NOW() WHERE id = ?");
                $updateStmt->execute([$token, $seller['id']]);
            } else {
                // Update resend_at timestamp for existing token
                $updateStmt = $conn->prepare("UPDATE sellers SET resend_at = NOW() WHERE id = ?");
                $updateStmt->execute([$seller['id']]);
            }
            
            // Store token in session for verification
            $_SESSION['verification_token'] = $token;
            $_SESSION['verification_email'] = $email;
            
            // Clear any old error messages
            unset($_SESSION['login_errors']);
            unset($_SESSION['login_email']);
            
            // Redirect to email sender with token (matches send-verification-email.php expectations)
            header("Location: /seller/backend/auth/send-verification-email.php?email=" . urlencode($email) . "&token=" . urlencode($token));
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