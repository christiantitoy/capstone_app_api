<?php
header('Content-Type: application/json');

require_once '/var/www/html/connection/db_connection.php';

// Check if connection exists
if (!isset($conn) || $conn === null) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

try {
    // Only accept POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
        exit;
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Check if email and password are provided
    $email = $input['email'] ?? $_POST['email'] ?? null;
    $password = $input['password'] ?? $_POST['password'] ?? null;
    
    if ($email && $password) {
        // Get user by email only (not password)
        $sql = "SELECT id, full_name, email, seller_id, role, is_removed, password 
                FROM employees 
                WHERE email = :email AND status = 'active'";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && password_verify($password, $result['password'])) {
            // Check if employee is removed
            if ($result['is_removed']) {
                echo json_encode(['status' => 'error', 'message' => 'Account has been removed']);
                exit;
            }
            
            // Update last login timestamp
            $updateSql = "UPDATE employees SET last_login = NOW() WHERE id = :id";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->execute([':id' => $result['id']]);
            
            // Remove sensitive data before sending
            unset($result['password']);
            unset($result['is_removed']);
            
            // Successful login
            echo json_encode([
                'status' => 'success', 
                'user' => $result,
                'message' => 'Login successful'
            ]);
        } else {
            // Invalid credentials
            echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Email and password are required']);
    }
} catch (PDOException $e) {
    error_log('Login error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An error occurred during login']);
}

$conn = null;
?>