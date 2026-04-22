<?php
// /seller/backend/auth/sellerLogin-process.php
session_start();

require_once '/var/www/html/connection/db_connection.php';

if (!isset($conn)) {
    die("Database connection failed");
}

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
    $_SESSION['login_email'] = $email;
    header("Location: $redirect");
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id, full_name, email, password, is_confirmed, setup_shop, token, seller_plan, approval_status FROM sellers WHERE email = ?");
    $stmt->execute([$email]);
    $seller = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($seller && password_verify($password, $seller['password'])) {

        // ── STEP 1: Check email confirmation ──────────────────────────────────
        if ($seller['is_confirmed'] != 1) {
            $token = $seller['token'];

            if (empty($token)) {
                $token = bin2hex(random_bytes(32));
                $updateStmt = $conn->prepare("UPDATE sellers SET token = ?, resend_at = NOW() WHERE id = ?");
                $updateStmt->execute([$token, $seller['id']]);
            } else {
                $updateStmt = $conn->prepare("UPDATE sellers SET resend_at = NOW() WHERE id = ?");
                $updateStmt->execute([$seller['id']]);
            }

            $_SESSION['verification_token'] = $token;
            $_SESSION['verification_email'] = $email;
            unset($_SESSION['login_errors'], $_SESSION['login_email']);

            header("Location: /seller/backend/auth/send-verification-email.php?email=" . urlencode($email) . "&token=" . urlencode($token));
            exit;
        }

        // ── STEP 2: Check shop setup ──────────────────────────────────────────
        if ($seller['setup_shop'] != 1) {
            $_SESSION['seller_id']    = $seller['id'];
            $_SESSION['seller_name']  = $seller['full_name'];
            $_SESSION['seller_email'] = $seller['email'];
            $_SESSION['seller_plan']  = $seller['seller_plan'] ?? 'Bronze';
            $_SESSION['logged_in']    = true;
            unset($_SESSION['login_errors'], $_SESSION['login_email']);

            header("Location: /seller/ui/shop-form.php");
            exit;
        }

       // ── STEP 3: Check approval status ─────────────────────────────────────
        if ($seller['approval_status'] === 'pending') {
            $_SESSION['seller_id']       = $seller['id'];
            $_SESSION['seller_name']     = $seller['full_name'];
            $_SESSION['seller_email']    = $seller['email'];
            $_SESSION['approval_status'] = 'pending';
            $_SESSION['logged_in']       = true; // ← add this so auth.php passes
            unset($_SESSION['login_errors'], $_SESSION['login_email']);

            header("Location: /seller/ui/sellerAccVerificationPage.php");
            exit;
        }

        if ($seller['approval_status'] === 'rejected') {
            $_SESSION['login_errors'] = ["Your seller account has been rejected."];
            $_SESSION['login_email']  = $email;
            header("Location: $redirect");
            exit;
        }

        // ── STEP 4: All checks passed — log in and go to dashboard ────────────
        $_SESSION['seller_id']    = $seller['id'];
        $_SESSION['seller_name']  = $seller['full_name'];
        $_SESSION['seller_email'] = $seller['email'];
        $_SESSION['seller_plan']  = $seller['seller_plan'] ?? 'Bronze';
        $_SESSION['logged_in']    = true;

        if ($remember) {
            $token  = bin2hex(random_bytes(32));
            $expiry = time() + (86400 * 30);
            setcookie('remember_token', $token, $expiry, '/', '', false, true);
            $_SESSION['remember_token'] = $token;
        }

        unset($_SESSION['login_errors'], $_SESSION['login_email']);
        header("Location: /seller/ui/dashboard.php");
        exit;

    } else {
        $_SESSION['login_errors'] = ["Invalid email or password."];
        $_SESSION['login_email']  = $email;
        header("Location: $redirect");
        exit;
    }

} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    $_SESSION['login_errors'] = ["An error occurred. Please try again later."];
    header("Location: $redirect");
    exit;
}
?>