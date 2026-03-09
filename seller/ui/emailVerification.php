<?php
// /seller/ui/emailVerification.php

// Get email from URL parameter
$email = $_GET['email'] ?? '';

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
  <link rel="stylesheet" href="../css/emailVerification.css?v=<?= time() ?>">
</head>
<body class="confirmation-page">

  <div class="confirmation-container">
    <div class="icon-circle">✉️</div>

    <h1>Confirmation Email Sent!</h1>

    <p class="subtitle">
      We just sent a confirmation link to <span class="email-highlight"><?php echo htmlspecialchars($email); ?></span>.<br>
      Please check your inbox (and spam/junk folder).
    </p>

    <a href="/seller/ui/login.php" class="btn-signin">Sign In Now</a>

    <div class="extra-info">
      Didn't receive the email? 
      <a href="../backend/auth/resend-verification.php?email=<?php echo urlencode($email); ?>">Resend confirmation</a><br>
      Still having trouble? <a href="/seller/ui/support.php">Contact support</a>
    </div>
  </div>

</body>
</html>