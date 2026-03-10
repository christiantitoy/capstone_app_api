<?php
// /seller/ui/verify-account.php

// Get email and token from URL
$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';

if (empty($email) || empty($token)) {
    header("Location: /seller/ui/signup.php");
    exit;
}

// Sanitize for display
$email = htmlspecialchars($email);
$token = htmlspecialchars($token);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verify Your Account - PalitOra</title>

<link rel="icon" type="image/png" href="/seller/image/app_icon.png">
<link rel="stylesheet" href="../css/emailVerification.css?v=<?= time() ?>">

<style>
.verify-container{
    text-align:center;
    max-width:500px;
    margin:50px auto;
    padding:40px;
    background:white;
    border-radius:10px;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
}

.verify-icon{
    font-size:60px;
    margin-bottom:20px;
}

.verify-btn{
    display:inline-block;
    padding:15px 40px;
    background:#4a90e2;
    color:white;
    text-decoration:none;
    border-radius:5px;
    font-size:16px;
    font-weight:bold;
    margin:20px 0;
    border: none;
    cursor: pointer;
}

.verify-btn:hover{
    background:#357abd;
}

.verify-form {
    margin-top: 20px;
}
</style>

</head>
<body>

<div class="verify-container">

<div class="verify-icon">🔐</div>

<h1>Verify Your Email</h1>

<p style="color:#666;margin-bottom:30px;">
Click the button below to confirm your email address:<br>
<strong style="color:#333;font-size:18px;"><?= $email ?></strong>
</p>

<form action="/seller/backend/auth/confirm-email.php" method="POST" class="verify-form">
    <input type="hidden" name="email" value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
    <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
    <button type="submit" class="verify-btn">Confirm Email</button>
</form>

<p style="color:#999;font-size:14px;margin-top:30px;">
This link will expire in 24 hours
</p>

</div>

</body>
</html>