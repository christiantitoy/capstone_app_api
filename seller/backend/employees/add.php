<?php
require_once __DIR__ . '/var/www/html/seller/backends/session/auth.php';
require_once __DIR__ . '/var/www/html/connection/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /seller/ui/employees.php");
    exit;
}

$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$password  = $_POST['password'] ?? '';
$role      = $_POST['role'] ?? '';

if (empty($full_name) || empty($email) || empty($password) || !in_array($role, ['order_manager', 'product_manager'])) {
    // In real project → use session flash message + redirect back with error
    die("Missing required fields");
}

// Simple password hash (use password_hash in real project!)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $conn->prepare("
        INSERT INTO employees (full_name, email, password, seller_id, role, status)
        VALUES (?, ?, ?, ?, ?, 'active')
    ");
    $stmt->execute([$full_name, $email, $hashed_password, $seller_id, $role]);

    // Success → redirect back
    header("Location: ../../ui/employees.php?success=1");
    exit;
} catch (PDOException $e) {
    if ($e->getCode() == 23505) { // unique violation
        die("Email already exists");
    }
    die("Error: " . $e->getMessage());
}