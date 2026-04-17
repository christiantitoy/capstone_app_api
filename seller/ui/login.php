<?php
session_start();
$errors = $_SESSION['login_errors'] ?? [];
$old_email = $_SESSION['login_email'] ?? '';
$success = $_GET['success'] ?? '';

// Clear session data after retrieving
unset($_SESSION['login_errors']);
unset($_SESSION['login_email']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PalitOra Seller Login</title>
    <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
    <link rel="stylesheet" href="../css/login.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/error.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">

    <div class="login-wrapper">
        <div class="login-inner">

            <!-- Left: Brand -->
            <div class="brand-section">
                <div class="brand-title">
                    <a href="../index.php">Palit<span>Ora</span></a>
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
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                placeholder="examplepassword123" 
                                required
                            >
                            <button type="button" class="password-toggle" id="togglePassword" aria-label="Toggle password visibility">
                                <i class="fa-regular fa-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
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
                    Don't have an account? <a href="../ui/signup.php">Create one now</a>
                </div>
            </div>

        </div>
    </div>

    <!-- GLOBAL MODAL -->
    <div id="appModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3 id="modalTitle"></h3>
            <p id="modalMessage"></p>
        </div>
    </div>

    <script>
    const phpError = <?= json_encode($error) ?>;
    const phpSuccess = <?= json_encode($success) ?>;
    </script>

    <script src="../js/reusables/showDialog.js?v=<?= time() ?>"></script>
    <script>
    // Password visibility toggle
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('togglePasswordIcon');

    if (togglePassword && passwordInput && toggleIcon) {
        togglePassword.addEventListener('click', function() {
            // Toggle the type attribute
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle the icon
            if (type === 'password') {
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            } else {
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            }
            
            // Keep focus on password field
            passwordInput.focus();
        });

        // Optional: Hide toggle button when input is empty
        passwordInput.addEventListener('input', function() {
            // You can add logic here if you want to hide/show the toggle based on input value
        });
    }
    </script>

</body>
</html>