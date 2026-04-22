<?php
// /seller/backend/employees/add.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/seller/backend/session/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/connection/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /seller/ui/employees.php");
    exit;
}

$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$password  = $_POST['password'] ?? '';
$role      = $_POST['role'] ?? '';

// Validation
$errors = [];
if (empty($full_name)) $errors[] = "Full name is required";
if (empty($email)) $errors[] = "Email is required";
if (empty($password)) $errors[] = "Password is required";
if (!in_array($role, ['order_manager', 'product_manager'])) $errors[] = "Invalid role selected";

// Check if email already exists
if (empty($errors)) {
    $check_stmt = $conn->prepare("SELECT id FROM employees WHERE email = ? AND seller_id = ?");
    $check_stmt->execute([$email, $seller_id]);
    if ($check_stmt->fetch()) {
        $errors[] = "Email already exists";
    }
}

// If there are errors, redirect back with error message
if (!empty($errors)) {
    $error_message = urlencode(implode(", ", $errors));
    header("Location: /seller/ui/employees.php?error=" . $error_message);
    exit;
}

// Simple password hash
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $conn->prepare("
        INSERT INTO employees (full_name, email, password, seller_id, role, status)
        VALUES (?, ?, ?, ?, ?, 'active')
    ");
    $stmt->execute([$full_name, $email, $hashed_password, $seller_id, $role]);

    // Success → redirect back
    header("Location: /seller/ui/employees.php?success=Employee added successfully");
    exit;
} catch (PDOException $e) {
    // For any other database errors
    $error_message = urlencode("Database error: " . $e->getMessage());
    header("Location: /seller/ui/employees.php?error=" . $error_message);
    exit;
}

$conn = null;
?>