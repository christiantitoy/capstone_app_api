<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <!-- Font Awesome Icons (minimal) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/login.css?v=<?= time() ?>">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2>Admin<span>Hub</span></h2>
                <p>Sign in to your account</p>
            </div>

            <!-- Message area for feedback -->
            <div id="errorMessage" class="message error">
                <i class="fas fa-exclamation-circle"></i>
                <span id="errorText"></span>
            </div>
            <div id="successMessage" class="message success">
                <i class="fas fa-check-circle"></i>
                <span>Login successful! Redirecting...</span>
            </div>

            <form id="loginForm">
                <div class="input-group">
                    <label class="input-label" for="email">Email Address</label>
                    <input type="email" id="email" class="input-field" placeholder="admin@example.com" required>
                </div>

                <div class="input-group">
                    <label class="input-label" for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" class="input-field" placeholder="Enter your password" required>
                        <button type="button" class="toggle-password" id="togglePassword">
                            <i class="far fa-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>
        </div>
        <div class="login-footer">
            <p>© 2025 AdminHub • Secure Admin Portal</p>
        </div>
    </div>

    <script>
        (function() {
            // DOM elements
            const form = document.getElementById('loginForm');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.getElementById('togglePassword');
            const errorMsgDiv = document.getElementById('errorMessage');
            const errorTextSpan = document.getElementById('errorText');
            const successMsgDiv = document.getElementById('successMessage');

            // Helper: hide all messages
            function hideMessages() {
                errorMsgDiv.classList.remove('show');
                successMsgDiv.classList.remove('show');
            }

            // Helper: show error
            function showError(text) {
                hideMessages();
                errorTextSpan.innerText = text;
                errorMsgDiv.classList.add('show');
                // Auto hide after 3 seconds
                setTimeout(() => {
                    errorMsgDiv.classList.remove('show');
                }, 3000);
            }

            // Helper: show success
            function showSuccess() {
                hideMessages();
                successMsgDiv.classList.add('show');
            }

            // Toggle password visibility
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    const icon = toggleBtn.querySelector('i');
                    if (type === 'text') {
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    } else {
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    }
                });
            }

            // Check for error messages from URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');
            const msg = urlParams.get('msg');
            
            if (error === 'empty_fields') {
                showError('Please enter both email and password');
            } else if (error === 'invalid_credentials') {
                showError('Invalid email or password');
            } else if (msg === 'session_expired') {
                showError('Your session has expired. Please login again.');
            } else if (error === 'invalid_session') {
                showError('Session error. Please login again.');
            } else if (msg === 'logged_out') {
                showError('You have been logged out successfully.');
            }

            // Form submission
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const email = emailInput.value.trim();
                const password = passwordInput.value.trim();
                
                // Simple validation
                if (!email || !password) {
                    showError('Please enter both email and password');
                    return;
                }
                
                // Show loading state
                const submitBtn = form.querySelector('.login-btn');
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
                submitBtn.disabled = true;
                
                try {
                    // Send login request to backend
                    const response = await fetch('../backend/login_process.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showSuccess();
                        // Redirect to dashboard after success
                        setTimeout(() => {
                            window.location.href = result.redirect || 'dashboard.php';
                        }, 1500);
                    } else {
                        showError(result.message || 'Invalid email or password');
                        submitBtn.innerHTML = originalBtnText;
                        submitBtn.disabled = false;
                    }
                } catch (error) {
                    showError('Connection error. Please try again.');
                    submitBtn.innerHTML = originalBtnText;
                    submitBtn.disabled = false;
                }
            });
            
            // Clear error when typing
            emailInput.addEventListener('focus', () => {
                errorMsgDiv.classList.remove('show');
            });
            passwordInput.addEventListener('focus', () => {
                errorMsgDiv.classList.remove('show');
            });
        })();
    </script>
</body>
</html>