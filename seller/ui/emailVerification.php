<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Email Confirmation Sent</title>
  <style>
    :root {
      --primary: #3498db;
      --primary-orange: #e67e22;
      --primary-dark: #2980b9;
      --success: #2ecc71;
      --danger: #e74c3c;
      --dark: #1e293b;
      --gray: #64748b;
      --light: #f1f5f9;
      --bg: #f8fafc;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body.confirmation-page {
      font-family: system-ui, -apple-system, sans-serif;
      background: var(--bg);
      min-height: 100vh;
      padding: 2rem;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .confirmation-container {
      width: 100%;
      max-width: 580px;
      background: white;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 10px 40px rgba(0,0,0,0.1);
      text-align: center;
      padding: 4rem 2.5rem;
    }

    .icon-circle {
      width: 90px;
      height: 90px;
      background: rgba(46, 204, 113, 0.12);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.8rem;
      font-size: 3.2rem;
    }

    h1 {
      font-size: 2.1rem;
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 1rem;
    }

    .subtitle {
      color: var(--gray);
      font-size: 1.05rem;
      line-height: 1.55;
      margin-bottom: 2.5rem;
    }

    .email-highlight {
      color: var(--primary);
      font-weight: 600;
    }

    .btn-signin {
      display: inline-block;
      padding: 1rem 2.2rem;
      background: linear-gradient(to right, var(--primary-orange), #d35400);
      color: white;
      text-decoration: none;
      font-size: 1.05rem;
      font-weight: 600;
      border-radius: 10px;
      transition: all 0.2s;
      margin-top: 1rem;
    }

    .btn-signin:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(230,126,34,0.3);
    }

    .extra-info {
      margin-top: 2.5rem;
      color: var(--gray);
      font-size: 0.95rem;
      line-height: 1.6;
    }

    .extra-info a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
    }

    .extra-info a:hover {
      text-decoration: underline;
    }

    @media (max-width: 640px) {
      body.confirmation-page {
        padding: 1.5rem;
      }
      .confirmation-container {
        padding: 3.5rem 1.8rem;
      }
      h1 {
        font-size: 1.8rem;
      }
    }
  </style>
</head>
<body class="confirmation-page">

  <div class="confirmation-container">
    <div class="icon-circle">✉️</div>

    <h1>Confirmation Email Sent!</h1>

    <p class="subtitle">
      We just sent a confirmation link to <span class="email-highlight">your.email@example.com</span>.<br>
      Please check your inbox (and spam/junk folder).
    </p>

    <a href="login.php" class="btn-signin">Sign In Now</a>

    <div class="extra-info">
      Didn't receive the email? 
      <a href="#">Resend confirmation</a><br>
      Still having trouble? <a href="support.php">Contact support</a>
    </div>
  </div>

</body>
</html>