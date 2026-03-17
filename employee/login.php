<?php
header('Content-Type: application/json');
require_once '/var/www/html/connection/db_connection.php';

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'POST only']);
    $conn = null;
    exit;
}

// Get input (works for BOTH JSON and form-data)
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    $input = $_POST;
}

// Clean inputs
$email = trim($input['email'] ?? '');
$password = trim($input['password'] ?? '');

// Validate
if ($email === '' || $password === '') {
    echo json_encode(['status' => 'error', 'message' => 'Email and password required']);
    $conn = null;
    exit;
}

try {
    // Get user by email
    $stmt = $conn->prepare("
        SELECT id, full_name, email, seller_id, role, password, is_removed
        FROM employees
        WHERE email = :email AND status = 'active'
        LIMIT 1
    ");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check user + password
    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
        $conn = null;
        exit;
    }

    // Check removed
    if (!empty($user['is_removed'])) {
        echo json_encode(['status' => 'error', 'message' => 'Account removed']);
        $conn = null;
        exit;
    }

    // Update last login
    $conn->prepare("UPDATE employees SET last_login = NOW() WHERE id = :id")
         ->execute(['id' => $user['id']]);

    // Remove sensitive fields
    unset($user['password'], $user['is_removed']);

    // Success
    echo json_encode([
        'status' => 'success',
        'user' => $user
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server error']);
}

// 🔹 Close connection (final cleanup)
$conn = null;