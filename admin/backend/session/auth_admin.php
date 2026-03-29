<?php
// /admin/backend/session/auth.php
// Must be included VERY FIRST — no whitespace before <?php

session_start();

// 1. Basic auth check
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../ui/login.php");
    exit;
}

// 2. Simple inactivity timeout (30 minutes)
$timeout = 30 * 60; // seconds
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: ../login.php?msg=session_expired");
    exit;
}
$_SESSION['last_activity'] = time();

// 3. Basic session fixation protection
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// 4. Export safe variables
$admin_id    = $_SESSION['admin_id']   ?? null;
$admin_email = $_SESSION['admin_email'] ?? '';
$admin_name  = $_SESSION['admin_name'] ?? 'Admin';

// 5. Paranoid check: critical data must exist
if ($admin_id === null || $admin_id <= 0) {
    session_destroy();
    header("Location: ../login.php?error=invalid_session");
    exit;
}
?>