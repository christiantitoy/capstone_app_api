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

    // Get JSON input instead of POST (more secure for mobile apps)
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Check if email and password are provided (supports both JSON and form data)
    $email = $input['email'] ?? $_POST['email'] ?? null;
    $password = $input['password'] ?? $_POST['password'] ?? null;
    
    if ($email && $password) {
        // Prepare SQL with all requested fields
        $sql = "SELECT id, full_name, email, seller_id, role, is_removed 
                FROM employees 
                WHERE email = :email AND password = :password AND status = 'active'";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $email, ':password' => $password]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // Check if employee is removed
            if ($result['is_removed']) {
                echo json_encode(['status' => 'error', 'message' => 'Account has been removed']);
                exit;
            }
            
            // Update last login timestamp
            $updateSql = "UPDATE employees SET last_login = NOW() WHERE id = :id";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->execute([':id' => $result['id']]);
            
            // Remove is_removed from response (client doesn't need it after check)
            unset($result['is_removed']);
            
            // Successful login
            echo json_encode([
                'status' => 'success', 
                'user' => $result,
                'message' => 'Login successful'
            ]);
        } else {
            // Invalid credentials - use generic message for security
            echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Email and password are required']);
    }
} catch (PDOException $e) {
    // Log error internally but don't expose details to client
    error_log('Login error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An error occurred during login']);
} finally {
    $conn = null;
}
?>