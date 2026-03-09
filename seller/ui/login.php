<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PalitOra Seller Login</title>
    <link rel="stylesheet" href="../css/login.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">

    <?php
    session_start();
    $errors = $_SESSION['login_errors'] ?? [];
    $old_email = $_SESSION['login_email'] ?? '';
    $success = $_GET['success'] ?? '';
    
    // Clear session data after retrieving
    unset($_SESSION['login_errors']);
    unset($_SESSION['login_email']);
    ?>

    <div class="login-wrapper">
        <div class="login-inner">

            <!-- Left: Brand -->
            <div class="brand-section">
                <div class="brand-title">
                    Palit<span>Ora</span>
                </div>
                <div class="brand-description">
                    Start selling in Dumaguete City — simple, fast, and powerful.<br>
                    Join local sellers and grow your business in just a few steps.
                </div>
            </div>

            <!-- Right: Form -->
            <div class="form-section">
                <h2 class="welcome-title">Welcome back</h2>
                <p class="welcome-subtitle">Sign in to access your seller dashboard</p>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <ul class="error-list">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="../backend/auth/sellerLogin-process.php">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="you@example.com" 
                            value="<?php echo htmlspecialchars($old_email); ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="••••••••" 
                            required
                        >
                    </div>

                    <div class="form-options">
                        <label class="remember-group">
                            <input type="checkbox" id="remember" name="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="forgot-link">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn-login">
                        Sign In
                    </button>
                </form>

                <div class="signup-link">
                    Don't have an account? <a href="signup.php">Create one now</a>
                </div>
            </div>

        </div>
    </div>

    <!-- Optional: Add JavaScript validation -->
    <script src="../backend/auth/js/sellerLogin-process.js?v=<?= time() ?>"></script>

</body>
</html>