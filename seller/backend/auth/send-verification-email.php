<?php
// send-verification-email.php (production version)

session_start();

// Get email and token from URL
$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';

if (empty($email) || empty($token) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: /seller/ui/signup.php");
    exit;
}

// Verification link with token instead of just email
$verificationPage = "https://capstone-app-api-r1ux.onrender.com/seller/ui/verify-account.php?email=" . urlencode($email) . "&token=" . urlencode($token);

// Get Brevo API key from environment
$apiKey = getenv('BREVO_API_KEY');
if (!$apiKey) {
    error_log("BREVO_API_KEY not set.");
    header("Location: /seller/ui/signup.php");
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

// Prepare JSON payload
$data = [
    "sender" => [
        "name" => "PalitOra",
        "email" => "christiantitoy@gmail.com"
    ],
    "to" => [
        ["email" => $email]
    ],
    "subject" => "Verify Your PalitOra Account",
    "htmlContent" => $htmlContent,
    "textContent" => "Verify your account: $verificationPage"
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

// Handle result
if ($error) {
    error_log("Email send failed for {$email}: $error");
    header("Location: /seller/ui/signup.php");
    exit;
}

$json = json_decode($response, true);

if (isset($json['messageId'])) {
    // Update resend_at timestamp in database
    try {
        require_once '/var/www/html/connection/db_connection.php';
        
        $stmt = $conn->prepare("UPDATE sellers SET resend_at = NOW() WHERE email = ? AND token = ?");
        $stmt->execute([$email, $token]);
        
    } catch (PDOException $e) {
        error_log("Failed to update resend_at: " . $e->getMessage());
        // Continue even if update fails - email was still sent
    }
    
    // Success → redirect to verification page with token
    header("Location: /seller/ui/emailVerification.php?email=" . urlencode($email) . "&token=" . urlencode($token));
    exit;
} else {
    error_log("Brevo API error: " . $response);
    header("Location: /seller/ui/signup.php");
    exit;
}
?>