<?php
// /seller/ui/sellerAccVerificationPage.php
session_start();

// Check if seller is logged in and has pending approval
if (!isset($_SESSION['seller_id']) || !isset($_SESSION['approval_status']) || $_SESSION['approval_status'] !== 'pending') {
    header("Location: /seller/ui/login.php");
    exit;
}

// Get seller information
$seller_id = $_SESSION['seller_id'];
$seller_name = $_SESSION['seller_name'] ?? 'Seller';
$seller_email = $_SESSION['seller_email'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Verification | Seller Dashboard</title>
    <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
    <link rel="stylesheet" href="../css/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/error.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/logout.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Additional styles for verification page */
        .verification-content {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 200px);
            padding: 40px 20px;
        }

        .verification-card {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
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

        .verification-icon i {
            font-size: 40px;
        }

        .verification-header h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .verification-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .verification-body {
            padding: 40px 30px;
        }

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

        .info-item:last-child {
            margin-bottom: 0;
        }

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

        .info-item .value {
            color: #7f8c8d;
        }

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

        .status-badge i {
            font-size: 14px;
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

        .message-box i {
            margin-right: 8px;
        }

        .steps {
            margin: 25px 0;
        }

        .step {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 20px;
        }

        .step-number {
            width: 30px;
            height: 30px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }

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

        .contact-support {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .contact-support p {
            font-size: 13px;
            color: #7f8c8d;
        }

        .contact-support a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .contact-support a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .verification-body {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Seller<span>Dashboard</span></h2>
        </div>
        <nav class="sidebar-nav">
            <a href="#" class="nav-item" style="opacity: 0.5; cursor: not-allowed;">
                <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
            </a>
            <a href="#" class="nav-item" style="opacity: 0.5; cursor: not-allowed;">
                <i class="fas fa-box"></i><span>Products</span>
            </a>
            <a href="#" class="nav-item" style="opacity: 0.5; cursor: not-allowed;">
                <i class="fas fa-shopping-cart"></i><span>Orders</span>
            </a>
            <a href="#" class="nav-item" style="opacity: 0.5; cursor: not-allowed;">
                <i class="fas fa-users"></i><span>Employees</span>
            </a>
            <a href="#" class="nav-item" style="opacity: 0.5; cursor: not-allowed;">
                <i class="fas fa-cog"></i><span>Settings</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="avatar"><?= strtoupper(substr($seller_name, 0, 1)) ?></div>
                <div>
                    <h4><?= htmlspecialchars($seller_name) ?></h4>
                    <p>Pending Approval</p>
                </div>
            </div>
            <button class="logout-btn" id="logoutBtn">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </div>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <div class="header-left">
                <h1>Account Verification</h1>
                <p>Please wait while we verify your seller account</p>
                <a href="#" class="plan-badge" style="background: #f59e0b; cursor: default;">
                    <i class="fas fa-clock"></i>
                    Pending Approval
                </a>
            </div>
            <div class="header-right">
                <div class="date-display"><?= date('F j, Y') ?></div>
            </div>
        </header>

        <div class="verification-content">
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
                        <i class="fas fa-info-circle"></i>
                        <p>Thank you for registering as a seller! Your account has been successfully verified via email and is now waiting for admin approval. This process ensures the quality and security of our marketplace.</p>
                    </div>

                    <div class="steps">
                        <div class="step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h4>Email Verification ✓</h4>
                                <p>Your email has been successfully verified.</p>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h4>Admin Review <i class="fas fa-spinner fa-spin" style="margin-left: 5px; font-size: 12px;"></i></h4>
                                <p>Our team is reviewing your seller application. This usually takes 24-48 hours.</p>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h4>Start Selling</h4>
                                <p>Once approved, you can set up your shop and start selling!</p>
                            </div>
                        </div>
                    </div>

                    <div class="contact-support">
                        <p>Need assistance? <a href="mailto:support@example.com">Contact Support</a></p>
                        <p style="margin-top: 10px; font-size: 12px;">We'll notify you via email once your account is approved.</p>
                    </div>
                </div>
            </div>
        </div>

        <footer class="main-footer">
            <p>© <?= date('Y') ?> Seller Dashboard. All rights reserved.</p>
            <div class="footer-links">
                <a href="#">Privacy Policy</a> •
                <a href="#">Terms of Service</a> •
                <a href="#">Help Center</a>
            </div>
        </footer>
    </main>
</div>

<!-- Logout Modal (using existing logout.js which handles this) -->
<div id="logoutModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirm Logout</h3>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to logout?</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-cancel" id="cancelLogoutBtn">Cancel</button>
            <button class="btn btn-logout" id="confirmLogoutBtn">Logout</button>
        </div>
    </div>
</div>

<script src="/seller/js/logout.js"></script>

</body>
</html>