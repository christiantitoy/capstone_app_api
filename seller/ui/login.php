<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PalitOra Seller Login</title>
    <link rel="stylesheet" href="../css/login.css?v=<?php echo uniqid(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">

    <div class="login-wrapper">
        <div class="login-inner">

            <!-- Left: Brand (no logo) -->
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

                <form>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" placeholder="you@example.com" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" placeholder="••••••••" required>
                    </div>

                    <div class="form-options">
                        <label class="remember-group">
                            <input type="checkbox" id="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="forgot-link">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn-login">
                        Sign In
                    </button>
                </form>

                <div class="signup-link">
                    Don't have an account? <a href="../ui/signup.php">Create one now</a>
                </div>
            </div>

        </div>
    </div>

</body>
</html>