<?php
// /seller/ui/verify-account.php

$email = $_GET['email'] ?? '';

if (empty($email)) {
    header("Location: /seller/ui/signup.php");
    exit;
}

$email = htmlspecialchars($email);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verify Your Account - PalitOra</title>

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
}

.verify-btn:hover{
    background:#357abd;
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

<a href="/seller/backend/auth/confirm-email.php?email=<?= urlencode($email) ?>" class="verify-btn">
Confirm Email
</a>

<p style="color:#999;font-size:14px;margin-top:30px;">
This link will expire in 24 hours
</p>

</div>

</body>
</html>