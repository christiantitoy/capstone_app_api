<?php
// send-verification-email.php (2026-ready with debug)

// Enable error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get email from URL
$email = $_GET['email'] ?? '';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Invalid email. Please provide a valid email address.";
    exit;
}

// Verification link
$verificationPage = "https://capstone-app-api-r1ux.onrender.com/seller/ui/verify-account.php?email=" . urlencode($email);

// Get Brevo API key from environment
$apiKey = getenv('BREVO_API_KEY');
if (!$apiKey) {
    echo "BREVO_API_KEY not set in environment!";
    exit;
}

// Prepare HTML email
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
    "sender" => ["name" => "PalitOra", "email" => "a46687001@smtp-brevo.com"], // must be verified
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
    // cURL error
    error_log("Email send failed for {$email}. cURL error: $error");
    echo "<h3>Error sending email:</h3><p>$error</p>";
} else {
    // Decode Brevo API response
    $json = json_decode($response, true);
    echo "<pre>Brevo API response:\n";
    print_r($json);
    echo "</pre>";

    if (isset($json['messageId'])) {
        // Success
        error_log("Verification email successfully queued for: {$email}. Message ID: " . $json['messageId']);
        echo "<h3>Email queued successfully!</h3>";
        echo "<p>Check your inbox (or spam) for the verification email.</p>";
        echo "<p><a href='/seller/ui/emailVerification.php?email=" . urlencode($email) . "'>Continue</a></p>";
    } else {
        // Failed
        error_log("Email not sent for {$email}. API Response: " . $response);
        echo "<h3>Email not sent!</h3>";
        echo "<p>Check API response above for details.</p>";
    }
}
?>