<?php
// /seller/ui/sellerAccVerificationPage.php
session_start();

// ── Auth guard ─────────────────────────────────────────────────────
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

// Fetch current approval status from database
require_once '/var/www/html/connection/db_connection.php';

$seller_id = $_SESSION['seller_id'];
$current_status = 'pending';
$rejection_reason = '';

try {
    $stmt = $conn->prepare("
        SELECT approval_status, rejection_reason 
        FROM public.sellers 
        WHERE id = ?
    ");
    $stmt->execute([$seller_id]);
    $seller = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($seller) {
        $current_status = $seller['approval_status'] ?? 'pending';
        $rejection_reason = $seller['rejection_reason'] ?? '';
        
        // Update session with current status
        $_SESSION['approval_status'] = $current_status;
    }
} catch (Exception $e) {
    error_log("Error fetching seller status: " . $e->getMessage());
}

// Safe variables
$seller_name  = $_SESSION['seller_name']  ?? 'Seller';
$seller_email = $_SESSION['seller_email'] ?? '';

// Determine page state
$isPending  = $current_status === 'pending';
$isRejected = $current_status === 'rejected';
$isApproved = $current_status === 'approved';

// If approved, redirect to dashboard
if ($isApproved) {
    header("Location: /seller/ui/dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Verification | Seller Dashboard</title>
    <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #667eea;
            --primary-dark: #764ba2;
            --success: #27ae60;
            --warning: #f59e0b;
            --danger: #e74c3c;
            --dark: #2c3e50;
            --gray: #7f8c8d;
            --light: #f8f9fa;
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
            padding: 40px 30px;
            text-align: center;
            color: white;
            transition: background 0.3s;
        }

        .verification-header.pending {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .verification-header.rejected {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
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
            background: var(--light);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid var(--warning);
        }

        .info-box.rejected {
            border-left-color: var(--danger);
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
            color: var(--warning);
            font-size: 16px;
        }

        .info-box.rejected .info-item i {
            color: var(--danger);
        }

        .info-item .label {
            font-weight: 600;
            color: var(--dark);
            min-width: 80px;
        }

        .info-item .value { color: var(--gray); }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .status-badge.pending {
            background: #fef3c7;
            color: #d97706;
        }

        .status-badge.rejected {
            background: #fee2e2;
            color: #dc2626;
        }

        .message-box {
            background: #e3f2fd;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .message-box.rejected {
            background: #fee2e2;
        }

        .message-box p {
            color: #1976d2;
            line-height: 1.6;
            font-size: 14px;
        }

        .message-box.rejected p {
            color: #991b1b;
        }

        .message-box i { margin-right: 8px; }

        .rejection-reason-box {
            background: #fef3c7;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid var(--danger);
        }

        .rejection-reason-box h4 {
            font-size: 14px;
            font-weight: 600;
            color: var(--danger);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .rejection-reason-box p {
            color: var(--dark);
            line-height: 1.6;
            font-size: 14px;
        }

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

        .step-number.done    { background: var(--success); }
        .step-number.active  { background: var(--warning); }
        .step-number.pending { background: #bdc3c7; }
        .step-number.rejected { background: var(--danger); }

        .step-content h4 {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .step-content p {
            font-size: 13px;
            color: var(--gray);
            line-height: 1.5;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 30px;
        }

        .btn {
            width: 100%;
            padding: 12px;
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
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }

        .contact-support {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .contact-support p { font-size: 13px; color: var(--gray); }

        .contact-support a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .contact-support a:hover { text-decoration: underline; }

        /* Modal Styles */
        .verification-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            justify-content: center;
            align-items: center;
        }

        .verification-modal-overlay.show {
            display: flex;
        }

        .verification-modal-content {
            background: white;
            border-radius: 16px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .verification-modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .verification-modal-header h3 {
            font-size: 18px;
            color: var(--dark);
            margin: 0;
        }

        .verification-modal-close {
            font-size: 24px;
            cursor: pointer;
            color: var(--gray);
            background: none;
            border: none;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .verification-modal-close:hover { color: var(--dark); }

        .verification-modal-body {
            padding: 25px;
        }

        .verification-modal-body p {
            margin-bottom: 10px;
            color: var(--dark);
        }

        .verification-text-secondary {
            font-size: 13px;
            color: var(--gray);
        }

        .verification-modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .verification-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .verification-btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .verification-btn-secondary:hover { background: #7f8c8d; }

        .verification-btn-danger {
            background: var(--danger);
            color: white;
            text-decoration: none;
            display: inline-block;
        }

        .verification-btn-danger:hover { background: #c0392b; }

        @media (max-width: 768px) {
            .verification-body { padding: 30px 20px; }
        }
    </style>
</head>
<body>

<div class="verification-container">
    <div class="verification-card">

        <div class="verification-header <?= $isRejected ? 'rejected' : 'pending' ?>">
            <div class="verification-icon">
                <?php if ($isRejected): ?>
                    <i class="fas fa-times-circle"></i>
                <?php else: ?>
                    <i class="fas fa-clock"></i>
                <?php endif; ?>
            </div>
            <h1><?= $isRejected ? 'Application Rejected' : 'Account Under Review' ?></h1>
            <p><?= $isRejected ? 'Your seller application was not approved' : 'Your seller account is being verified by our team' ?></p>
        </div>

        <div class="verification-body">
            <div style="text-align: center;">
                <div class="status-badge <?= $isRejected ? 'rejected' : 'pending' ?>">
                    <i class="fas fa-<?= $isRejected ? 'times-circle' : 'hourglass-half' ?>"></i>
                    <span><?= $isRejected ? 'Rejected' : 'Pending Approval' ?></span>
                </div>
            </div>

            <div class="info-box <?= $isRejected ? 'rejected' : '' ?>">
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

            <?php if ($isRejected && $rejection_reason): ?>
                <div class="rejection-reason-box">
                    <h4><i class="fas fa-exclamation-triangle"></i> Rejection Reason</h4>
                    <p><?= htmlspecialchars($rejection_reason) ?></p>
                </div>
            <?php endif; ?>

            <div class="message-box <?= $isRejected ? 'rejected' : '' ?>">
                <p>
                    <i class="fas fa-info-circle"></i>
                    <?php if ($isRejected): ?>
                        Your seller application has been rejected. Please review the reason above.
                        You may contact support for more information or reapply with corrected information.
                    <?php else: ?>
                        Thank you for setting up your shop! Your email has been verified and your shop
                        details have been submitted. Your account is now pending admin approval.
                        This usually takes 24–48 hours.
                    <?php endif; ?>
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

                <!-- Step 3: Admin review -->
                <div class="step">
                    <div class="step-number <?= $isRejected ? 'rejected' : 'active' ?>">
                        <?php if ($isRejected): ?>
                            <i class="fas fa-times"></i>
                        <?php else: ?>
                            <i class="fas fa-spinner fa-spin" style="font-size: 12px;"></i>
                        <?php endif; ?>
                    </div>
                    <div class="step-content">
                        <h4>Admin Review</h4>
                        <p>
                            <?php if ($isRejected): ?>
                                Your application was reviewed and not approved.
                            <?php else: ?>
                                Our team is reviewing your seller application. This usually takes 24–48 hours.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <!-- Step 4: Start selling -->
                <div class="step">
                    <div class="step-number <?= $isRejected ? 'rejected' : 'pending' ?>">
                        <?= $isRejected ? '<i class="fas fa-times"></i>' : '4' ?>
                    </div>
                    <div class="step-content">
                        <h4>Start Selling</h4>
                        <p>
                            <?php if ($isRejected): ?>
                                Unable to proceed. Please contact support for assistance.
                            <?php else: ?>
                                Once approved, you'll get full access to your dashboard and can start selling!
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <?php if ($isRejected): ?>
                    <a href="/seller/ui/shop-form.php" class="btn btn-primary">
                        <i class="fas fa-redo-alt"></i> Reapply / Edit Shop
                    </a>
                <?php endif; ?>
                <button class="btn btn-danger verification-logout-trigger">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </button>
            </div>

            <div class="contact-support">
                <?php if ($isRejected): ?>
                    <p>Questions about the rejection? <a href="#">Contact Support</a></p>
                <?php else: ?>
                    <p>We'll notify you via email once your account is approved.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Logout Modal -->
<div class="verification-modal-overlay" id="verificationLogoutModal">
    <div class="verification-modal-content">
        <div class="verification-modal-header">
            <h3>Sign Out</h3>
            <button class="verification-modal-close" id="verificationCloseModal">×</button>
        </div>
        <div class="verification-modal-body">
            <p>Are you sure you want to sign out?</p>
            <p class="verification-text-secondary">You will need to log in again to access your account.</p>
        </div>
        <div class="verification-modal-footer">
            <button class="verification-btn verification-btn-secondary" id="verificationCancelLogout">Cancel</button>
            <a href="/seller/backend/auth/logout.php" class="verification-btn verification-btn-danger">Sign Out</a>
        </div>
    </div>
</div>

<script>
    // Logout Modal Functionality
    const modal = document.getElementById('verificationLogoutModal');
    const logoutTrigger = document.querySelector('.verification-logout-trigger');
    const closeModal = document.getElementById('verificationCloseModal');
    const cancelLogout = document.getElementById('verificationCancelLogout');

    if (logoutTrigger) {
        logoutTrigger.addEventListener('click', function() {
            modal.classList.add('show');
        });
    }

    if (closeModal) {
        closeModal.addEventListener('click', function() {
            modal.classList.remove('show');
        });
    }

    if (cancelLogout) {
        cancelLogout.addEventListener('click', function() {
            modal.classList.remove('show');
        });
    }

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.classList.remove('show');
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('show')) {
            modal.classList.remove('show');
        }
    });
</script>

</body>
</html>