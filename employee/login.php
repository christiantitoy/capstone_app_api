<?php
header('Content-Type: application/json');
require_once '/var/www/html/connection/db_connection.php';

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'POST request required'
    ]);
    $conn = null;
    exit;
}

// Get input (JSON or form)
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    $input = $_POST;
}

// Clean input
$email = trim($input['email'] ?? '');
$password = trim($input['password'] ?? '');

// Validate input
if ($email === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Email is required'
    ]);
    $conn = null;
    exit;
}

if ($password === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Password is required'
    ]);
    $conn = null;
    exit;
}

try {
    // 🔍 STEP 1: Check if email exists
    $stmt = $conn->prepare("
        SELECT id, full_name, email, seller_id, role, password, is_removed
        FROM employees
        WHERE email = :email
        LIMIT 1
    ");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Email not found'
        ]);
        $conn = null;
        exit;
    }

    // 🔍 STEP 2: Check password
    if (!password_verify($password, $user['password'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Incorrect password'
        ]);
        $conn = null;
        exit;
    }

    // 🔍 STEP 3: Check removed
    if (!empty($user['is_removed'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Account has been removed'
        ]);
        $conn = null;
        exit;
    }

    // ✅ Update last login
    $conn->prepare("UPDATE employees SET last_login = NOW() WHERE id = :id")
         ->execute(['id' => $user['id']]);

    // Remove sensitive data
    unset($user['password'], $user['is_removed']);

    // ✅ Success
    echo json_encode([
        'status' => 'success',
        'message' => 'Login successful',
        'user' => $user
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error'
    ]);
}

// Close connection
$conn = null;