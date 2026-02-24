<?php
header("Content-Type: application/json; charset=UTF-8");

// Database connection
require_once '/var/www/html/db_connection.php';

// Check if request is JSON or form data
$input = json_decode(file_get_contents('php://input'), true);

if ($input) {
    $userId = isset($input['user_id']) ? intval($input['user_id']) : 0;
    $newUsername = $input['username'] ?? null;
    $newEmail = $input['email'] ?? null;
} else {
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $newUsername = $_POST['username'] ?? null;
    $newEmail = $_POST['email'] ?? null;
}

if (!$userId) {
    echo json_encode([
        "status" => "error", 
        "message" => "Missing user ID"
    ]);
    exit;
}

try {
    // Prepare query dynamically based on inputs
    $updates = [];
    $params = [];

    if ($newUsername !== null && trim($newUsername) !== '') {
        $updates[] = "username = :username";
        $params[':username'] = trim($newUsername);
    }

    if ($newEmail !== null && trim($newEmail) !== '') {
        // Optional: Validate email format
        if (!filter_var(trim($newEmail), FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "status" => "error", 
                "message" => "Invalid email format"
            ]);
            exit;
        }
        $updates[] = "email = :email";
        $params[':email'] = trim($newEmail);
    }

    if (empty($updates)) {
        echo json_encode([
            "status" => "error", 
            "message" => "Nothing to update"
        ]);
        exit;
    }

    // Add user_id to params
    $params[':user_id'] = $userId;

    // Build the SQL query
    $sql = "UPDATE buyers SET " . implode(", ", $updates) . " WHERE id = :user_id";
    
    // Prepare and execute
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute($params)) {
        // Check if any row was actually updated
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                "status" => "success", 
                "message" => "Profile updated successfully"
            ]);
        } else {
            echo json_encode([
                "status" => "error", 
                "message" => "No changes made or user not found"
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error", 
            "message" => "Database update failed"
        ]);
    }
    
} catch (PDOException $e) {
    // Check for duplicate entry errors
    if ($e->errorInfo[1] == 23505) { // PostgreSQL unique violation code
        if (strpos($e->getMessage(), 'username') !== false) {
            echo json_encode([
                "status" => "error", 
                "message" => "Username already exists"
            ]);
        } elseif (strpos($e->getMessage(), 'email') !== false) {
            echo json_encode([
                "status" => "error", 
                "message" => "Email already exists"
            ]);
        } else {
            echo json_encode([
                "status" => "error", 
                "message" => "Duplicate entry"
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error", 
            "message" => "Database error: " . $e->getMessage()
        ]);
    }
}
?>