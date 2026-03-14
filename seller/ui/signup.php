<?php
// signup.php

// Start session only if needed for flash messages / old input
session_start();

// Get flash data (success / error / old form values)
$success = $_GET['success'] ?? '';
$error   = $_GET['error']   ?? '';
$old     = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Join PalitOra as a seller in Dumaguete">
    <title>Create Seller Account - PalitOra</title>
    
    <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
    <link rel="stylesheet" href="../css/signup.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/error.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Password toggle styles */
        .password-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-input-wrapper input {
            width: 100%;
            padding-right: 40px; /* Make room for the toggle button */
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .password-toggle:hover {
            color: #333;
        }

        .password-toggle:focus {
            outline: none;
            color: #000;
        }
    </style>
</head>
<body class="signup-page">

    <div class="signup-container">

        <div class="signup-left">
            <div class="brand-header">
                <h2>Join PalitOra Sellers</h2>
            </div>
            <p class="brand-intro">
                Reach more customers in Dumaguete — grow easily and locally.
            </p>
            <ul class="benefits-list">
                <li class="benefit-item">
                    <i class="fas fa-users benefit-icon"></i>
                    <span>More local customers</span>
                </li>
                <li class="benefit-item">
                    <i class="fas fa-chart-line benefit-icon"></i>
                    <span>Increased revenue online</span>
                </li>
                <li class="benefit-item">
                    <i class="fas fa-store benefit-icon"></i>
                    <span>Better visibility in Dumaguete</span>
                </li>
            </ul>
        </div>

        <div class="signup-right">
            <div class="form-header">
                <h2>Create Seller Account</h2>
                <p>Just a few details to get started</p>
            </div>

            <form method="POST" action="../backend/auth/sellerSignup-process.php" novalidate>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            placeholder="Juan Dela Cruz"
                            value="<?= htmlspecialchars($old['name'] ?? '') ?>" 
                            required
                            autofocus
                        >
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="yourname@example.com"
                            value="<?= htmlspecialchars($old['email'] ?? '') ?>" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                placeholder="At least 8 characters"
                                minlength="8" 
                                required
                            >
                            <button type="button" class="password-toggle" id="togglePassword" aria-label="Toggle password visibility">
                                <i class="fa-regular fa-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                placeholder="At least 8 characters"
                                minlength="8" 
                                required
                            >
                            <button type="button" class="password-toggle" id="toggleConfirmPassword" aria-label="Toggle confirm password visibility">
                                <i class="fa-regular fa-eye" id="toggleConfirmPasswordIcon"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="terms-agreement">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">
                        I agree to the 
                        <a href="#">Terms of Service</a> and 
                        <a href="#">Privacy Policy</a>
                    </label>
                </div>

                <button type="submit" class="btn-signup">
                    <i class="fas fa-user-plus"></i>
                    Create Account
                </button>

                <p class="login-link">
                    Already have an account? 
                    <a href="login.php">Log In</a>
                </p>
            </form>
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
    
    // Password visibility toggle for main password
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('togglePasswordIcon');

    if (togglePassword) {
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
            
            // Optional: Add a little animation or focus
            passwordInput.focus();
        });
    }

    // Password visibility toggle for confirm password
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const toggleConfirmIcon = document.getElementById('toggleConfirmPasswordIcon');

    if (toggleConfirmPassword) {
        toggleConfirmPassword.addEventListener('click', function() {
            // Toggle the type attribute
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            
            // Toggle the icon
            if (type === 'password') {
                toggleConfirmIcon.classList.remove('fa-eye-slash');
                toggleConfirmIcon.classList.add('fa-eye');
            } else {
                toggleConfirmIcon.classList.remove('fa-eye');
                toggleConfirmIcon.classList.add('fa-eye-slash');
            }
            
            // Optional: Add a little animation or focus
            confirmPasswordInput.focus();
        });
    }
    </script>

    <script src="../js/reusables/showDialog.js?v=<?= time() ?>"></script>

</body>
</html>