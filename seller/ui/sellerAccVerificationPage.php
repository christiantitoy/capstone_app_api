<?php
// /seller/ui/sellerAccVerificationPage.php
session_start();

// ── Auth & pending guard ──────────────────────────────────────────────
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true ||
    !isset($_SESSION['seller_id']) || $_SESSION['seller_id'] <= 0) {
    header("Location: /seller/ui/login.php");
    exit;
}

// Inactivity timeout (30 minutes)
$timeout = 30 * 60;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: /seller/ui/login.php?msg=session_expired");
    exit;
}
$_SESSION['last_activity'] = time();

// Session fixation protection
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Must be pending to view this page
if (!isset($_SESSION['approval_status']) || $_SESSION['approval_status'] !== 'pending') {
    header("Location: /seller/ui/login.php");
    exit;
}

// Safe variables (mirrors auth.php exports)
$seller_id    = $_SESSION['seller_id'];
$seller_name  = $_SESSION['seller_name']  ?? 'Seller';
$seller_email = $_SESSION['seller_email'] ?? '';
$seller_plan  = $_SESSION['seller_plan']  ?? 'free';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Verification | Seller Dashboard</title>
    <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/logout.css?v=<?= time() ?>">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --dark: #2c3e50;
            --danger: #e74c3c;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .verification-container {
            max-width: 550px;
            width: 100%;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .verification-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .verification-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .verification-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .verification-icon i { font-size: 40px; }

        .verification-header h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .verification-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .verification-body { padding: 40px 30px; }

        .info-box {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid #f59e0b;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
        }

        .info-item:last-child { margin-bottom: 0; }

        .info-item i {
            width: 20px;
            color: #f59e0b;
            font-size: 16px;
        }

        .info-item .label {
            font-weight: 600;
            color: #2c3e50;
            min-width: 80px;
        }

        .info-item .value { color: #7f8c8d; }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fef3c7;
            color: #d97706;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .message-box {
            background: #e3f2fd;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .message-box p {
            color: #1976d2;
            line-height: 1.6;
            font-size: 14px;
        }

        .message-box i { margin-right: 8px; }

        .steps { margin: 25px 0; }

        .step {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 20px;
        }

        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
            color: white;
        }

        .step-number.done    { background: #27ae60; }
        .step-number.active  { background: #f59e0b; }
        .step-number.pending { background: #bdc3c7; }

        .step-content h4 {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .step-content p {
            font-size: 13px;
            color: #7f8c8d;
            line-height: 1.5;
        }

        .logout-btn-container {
            margin-top: 30px;
            text-align: center;
        }

        .logout-trigger {
            width: 100%;
            padding: 12px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .logout-trigger:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }

        .contact-support {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .contact-support p { font-size: 13px; color: #7f8c8d; }

        .contact-support a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .contact-support a:hover { text-decoration: underline; }

        @media (max-width: 768px) {
            .verification-body { padding: 30px 20px; }
        }
    </style>
</head>
<body>

<div class="verification-container">
    <div class="verification-card">

        <div class="verification-header">
            <div class="verification-icon">
                <i class="fas fa-clock"></i>
            </div>
            <h1>Account Under Review</h1>
            <p>Your seller account is being verified by our team</p>
        </div>

        <div class="verification-body">
            <div style="text-align: center;">
                <div class="status-badge">
                    <i class="fas fa-hourglass-half"></i>
                    <span>Pending Approval</span>
                </div>
            </div>

            <div class="info-box">
                <div class="info-item">
                    <i class="fas fa-user"></i>
                    <span class="label">Name:</span>
                    <span class="value"><?= htmlspecialchars($seller_name) ?></span>
                </div>
                <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <span class="label">Email:</span>
                    <span class="value"><?= htmlspecialchars($seller_email) ?></span>
                </div>
            </div>

            <div class="message-box">
                <p>
                    <i class="fas fa-info-circle"></i>
                    Thank you for setting up your shop! Your email has been verified and your shop
                    details have been submitted. Your account is now pending admin approval.
                    This usually takes 24–48 hours.
                </p>
            </div>

            <div class="steps">
                <!-- Step 1: Email verified -->
                <div class="step">
                    <div class="step-number done">✓</div>
                    <div class="step-content">
                        <h4>Email Verification</h4>
                        <p>Your email has been successfully verified.</p>
                    </div>
                </div>

                <!-- Step 2: Shop setup -->
                <div class="step">
                    <div class="step-number done">✓</div>
                    <div class="step-content">
                        <h4>Shop Setup</h4>
                        <p>Your shop information has been successfully submitted.</p>
                    </div>
                </div>

                <!-- Step 3: Admin review (active) -->
                <div class="step">
                    <div class="step-number active">
                        <i class="fas fa-spinner fa-spin" style="font-size: 12px;"></i>
                    </div>
                    <div class="step-content">
                        <h4>Admin Review</h4>
                        <p>Our team is reviewing your seller application. This usually takes 24–48 hours.</p>
                    </div>
                </div>

                <!-- Step 4: Start selling (pending) -->
                <div class="step">
                    <div class="step-number pending">4</div>
                    <div class="step-content">
                        <h4>Start Selling</h4>
                        <p>Once approved, you'll get full access to your dashboard and can start selling!</p>
                    </div>
                </div>
            </div>

            <div class="logout-btn-container">
                <button class="logout-trigger">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </button>
            </div>

            <div class="contact-support">
                <p>We'll notify you via email once your account is approved.</p>
            </div>
        </div>
    </div>
</div>

<!-- Logout Modal -->
<div class="modal-overlay" id="logoutModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Sign Out</h3>
            <button class="modal-close" id="closeModal">×</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to sign out?</p>
            <p class="text-secondary">You will need to log in again to access your account.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancelLogout">Cancel</button>
            <a href="/seller/backend/auth/logout.php" class="btn btn-danger">Sign Out</a>
        </div>
    </div>
</div>

<script src="/seller/js/logout.js"></script>

</body>
</html>