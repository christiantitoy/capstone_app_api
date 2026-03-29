<?php
// /admin/backend/login_process.php
session_start();
require_once '/var/www/html/connection/db_connection.php';

// Set JSON response header
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please enter both email and password']);
    exit;
}

try {
    // Query admin from database using PDO
    $sql = "SELECT id, email, password FROM admin WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':email' => $email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        // Verify password
        if (password_verify($password, $row['password'])) {
            // Set session variables
            $_SESSION['logged_in'] = true;
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_email'] = $row['email'];
            $_SESSION['admin_name'] = 'Admin';
            $_SESSION['last_activity'] = time();
            $_SESSION['initiated'] = true;
            
            echo json_encode([
                'success' => true, 
                'message' => 'Login successful',
                'redirect' => 'dashboard.php'
            ]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>