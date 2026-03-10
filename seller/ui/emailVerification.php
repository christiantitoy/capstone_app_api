<?php
// /seller/ui/emailVerification.php

session_start();

// Get email, token, and error from URL parameters
$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';
$error = $_GET['error'] ?? '';

// If no email in URL, redirect to signup page
if (empty($email)) {
    header("Location: /seller/ui/signup.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Email Confirmation Sent - PalitOra</title>
  <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
  <link rel="stylesheet" href="../css/emailVerification.css?v=<?= time() ?>">
  <link rel="stylesheet" href="../css/error.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="confirmation-page">

  <!-- GLOBAL MODAL -->
  <div id="appModal" class="modal">
      <div class="modal-content">
          <span class="close-btn">&times;</span>
          <h3 id="modalTitle"></h3>
          <p id="modalMessage"></p>
      </div>
  </div>

  <div class="confirmation-container">
    <div class="icon-circle">✉️</div>

    <h1>Confirmation Email Sent!</h1>

    <p class="subtitle">
      We just sent a confirmation link to <span class="email-highlight"><?php echo htmlspecialchars($email); ?></span>.<br>
      Please check your inbox (and spam/junk folder) and click the verification link.
    </p>

    <a href="/seller/ui/login.php" class="btn-signin">Sign In Now</a>

    <div class="extra-info">
      Didn't receive the email? 
      <a href="/seller/backend/auth/resend-verification.php?email=<?php echo urlencode($email); ?>&token=<?php echo urlencode($token); ?>">Resend confirmation</a><br>
      Still having trouble? <a href="/seller/ui/support.php">Contact support</a>
    </div>
  </div>

  <script src="../js/reusables/showDialog.js?v=<?= time() ?>"></script>
  <script>
    // Pass PHP error to JavaScript
    const phpError = <?php echo json_encode($error ?: null); ?>;
    
    // Show modal if there's an error
    if(phpError) {
        showModal("error", phpError);
    }
  </script>

</body>
</html>