<?php
// /seller/backend/auth/send-verification-email.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '/var/www/html/vendor/autoload.php';

/*
|-------------------------------------------------------------------------- 
| Enable error logging
|-------------------------------------------------------------------------- 
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/*
|-------------------------------------------------------------------------- 
| Get email from URL
|-------------------------------------------------------------------------- 
*/
$email = $_GET['email'] ?? '';

if (empty($email)) {
    error_log("Verification email failed: No email provided.");
    header("Location: /seller/ui/signup.php");
    exit;
}

/*
|-------------------------------------------------------------------------- 
| Validate email
|-------------------------------------------------------------------------- 
*/
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    error_log("Verification email failed: Invalid email format -> " . $email);
    header("Location: /seller/ui/signup.php");
    exit;
}

/*
|-------------------------------------------------------------------------- 
| Verification Link
|-------------------------------------------------------------------------- 
*/
$verificationPage = "https://capstone-app-api-r1ux.onrender.com/seller/ui/verify-account.php?email=" . urlencode($email);

/*
|-------------------------------------------------------------------------- 
| Create Mailer
|-------------------------------------------------------------------------- 
*/
$mail = new PHPMailer(true);

try {

    /*
    |-------------------------------------------------------------------------- 
    | Brevo SMTP Configuration
    |-------------------------------------------------------------------------- 
    */
    $mail->isSMTP();
    $mail->Host       = 'smtp-relay.brevo.com';           // Brevo SMTP host
    $mail->SMTPAuth   = true;
    $mail->Username   = 'a46687001@smtp-brevo.com';   // Brevo email
    $mail->Password   = getenv('BREVO_SMTP_KEY');    // SMTP Key from Brevo
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // TLS encryption
    $mail->Port       = 587;                              // TLS port

    /*
    |-------------------------------------------------------------------------- 
    | Debug (set to 0 in production)
    |-------------------------------------------------------------------------- 
    */
    $mail->SMTPDebug = 2;

    /*
    |-------------------------------------------------------------------------- 
    | Timeout
    |-------------------------------------------------------------------------- 
    */
    $mail->Timeout = 30;

    /*
    |-------------------------------------------------------------------------- 
    | Email Sender
    |-------------------------------------------------------------------------- 
    */
    $mail->setFrom('a46687001@smtp-brevo.com', 'PalitOra');

    /*
    |-------------------------------------------------------------------------- 
    | Recipient
    |-------------------------------------------------------------------------- 
    */
    $mail->addAddress($email);

    /*
    |-------------------------------------------------------------------------- 
    | Email Content
    |-------------------------------------------------------------------------- 
    */
    $mail->isHTML(true);
    $mail->Subject = 'Verify Your PalitOra Account';

    $mail->Body = "
    <html>
    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                background: #f4f4f4;
                padding: 20px;
            }

            .container {
                max-width: 500px;
                background: white;
                margin: auto;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0px 2px 10px rgba(0,0,0,0.1);
                text-align: center;
            }

            .button {
                display: inline-block;
                padding: 12px 30px;
                background: #4a90e2;
                color: white !important;
                text-decoration: none;
                border-radius: 5px;
                margin: 20px 0;
                font-weight: bold;
            }

            .link-box {
                word-break: break-all;
                background: #f5f5f5;
                padding: 10px;
                border-radius: 4px;
                font-size: 13px;
            }

        </style>
    </head>

    <body>

        <div class='container'>

            <h2>Welcome to PalitOra!</h2>

            <p>Click the button below to verify your email address:</p>

            <a href='$verificationPage' class='button'>Verify Email</a>

            <p>If the button does not work, copy and paste this link into your browser:</p>

            <div class='link-box'>
                $verificationPage
            </div>

        </div>

    </body>
    </html>
    ";

    /*
    |-------------------------------------------------------------------------- 
    | Send Email
    |-------------------------------------------------------------------------- 
    */
    $mail->send();

    error_log("Verification email successfully sent to: " . $email);

} catch (Exception $e) {

    /*
    |-------------------------------------------------------------------------- 
    | Log error if email fails
    |-------------------------------------------------------------------------- 
    */
    error_log("Email send failed for {$email}. Error: " . $mail->ErrorInfo);

}

/*
|-------------------------------------------------------------------------- 
| Redirect to confirmation page
|-------------------------------------------------------------------------- 
*/
header("Location: /seller/ui/emailVerification.php?email=" . urlencode($email));
exit;
?>