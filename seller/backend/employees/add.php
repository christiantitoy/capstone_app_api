<?php
// add.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/seller/backend/session/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/connection/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method";
    header("Location: /seller/ui/employees.php");
    exit;
}

$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$password  = $_POST['password'] ?? '';
$role      = $_POST['role'] ?? '';

// Validate required fields
if (empty($full_name) || empty($email) || empty($password) || !in_array($role, ['order_manager', 'product_manager'])) {
    $_SESSION['error'] = "Missing required fields. Please fill in all fields.";
    header("Location: /seller/ui/employees.php");
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email format.";
    header("Location: /seller/ui/employees.php");
    exit;
}

// Validate password length
if (strlen($password) < 6) {
    $_SESSION['error'] = "Password must be at least 6 characters long.";
    header("Location: /seller/ui/employees.php");
    exit;
}

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $conn->prepare("
        INSERT INTO employees (full_name, email, password, seller_id, role, status)
        VALUES (?, ?, ?, ?, ?, 'active')
    ");
    $stmt->execute([$full_name, $email, $hashed_password, $seller_id, $role]);

    // Success → redirect back with success message
    $_SESSION['success'] = "Employee added successfully!";
    header("Location: /seller/ui/employees.php?success=1");
    exit;
    
} catch (PDOException $e) {
    if ($e->getCode() == 23505) { // unique violation (PostgreSQL)
        $_SESSION['error'] = "Email already exists. Please use a different email address.";
    } else {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }
    header("Location: /seller/ui/employees.php");
    exit;
}

$conn = null;