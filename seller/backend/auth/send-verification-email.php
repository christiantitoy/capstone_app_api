<?php
// send-verification-email.php (2026 version using Brevo API)

// Get email from URL
$email = $_GET['email'] ?? '';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: /seller/ui/signup.php");
    exit;
}

// Verification link
$verificationPage = "https://capstone-app-api-r1ux.onrender.com/seller/ui/verify-account.php?email=" . urlencode($email);

// Get Brevo API key from environment
$apiKey = getenv('BREVO_API_KEY');

// Prepare HTML email (same style as your original)
$htmlContent = "
<html>
<head>
<style>
body { font-family: Arial, sans-serif; line-height: 1.6; background: #f4f4f4; padding: 20px; }
.container { max-width: 500px; background: white; margin: auto; padding: 30px; border-radius: 8px; box-shadow: 0px 2px 10px rgba(0,0,0,0.1); text-align: center; }
.button { display: inline-block; padding: 12px 30px; background: #4a90e2; color: white !important; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
.link-box { word-break: break-all; background: #f5f5f5; padding: 10px; border-radius: 4px; font-size: 13px; }
</style>
</head>
<body>
<div class='container'>
<h2>Welcome to PalitOra!</h2>
<p>Click the button below to verify your email address:</p>
<a href='$verificationPage' class='button'>Verify Email</a>
<p>If the button does not work, copy and paste this link into your browser:</p>
<div class='link-box'>$verificationPage</div>
</div>
</body>
</html>
";

// Prepare JSON payload for Brevo API
$data = [
    "sender" => ["name" => "PalitOra", "email" => "a46687001@smtp-brevo.com"],
    "to" => [["email" => $email]],
    "subject" => "Verify Your PalitOra Account",
    "htmlContent" => $htmlContent
];

// Send email using cURL
$ch = curl_init("https://api.brevo.com/v3/smtp/email");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "accept: application/json",
    "content-type: application/json",
    "api-key: $apiKey"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

// Handle response
if ($error) {
    error_log("Email send failed for {$email}. Error: $error");
    echo "Error sending email: $error";
} else {
    error_log("Verification email successfully sent to: {$email}");
    header("Location: /seller/ui/emailVerification.php?email=" . urlencode($email));
    exit;
}
?>