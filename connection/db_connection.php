<?php
// header("Content-Type: application/json; charset=UTF-8");

// Get database credentials from Render environment variables
$host = getenv("DB_HOST");
$port = getenv("DB_PORT");
$dbname = getenv("DB_NAME");
$user = getenv("DB_USER");
$password = getenv("DB_PASS");

// Check if environment variables exist
if (!$host || !$port || !$dbname || !$user || !$password) {
    echo json_encode([
        "status" => false,
        "message" => "Database environment variables not set"
    ]);
    exit;
}

// Retry configuration
$maxRetries = 3;
$retryDelay = 1; // seconds
$attempt = 0;
$connected = false;

while (!$connected && $attempt < $maxRetries) {
    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
        
        $conn = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_TIMEOUT => 5 // 5 second timeout for connection attempts
        ]);
        
        $connected = true;
        
        // Optional success test (uncomment if needed)
        // echo json_encode([
        //     "status" => true,
        //     "message" => "Database connected successfully"
        // ]);
        
    } catch (PDOException $e) {
        $attempt++;
        
        if ($attempt >= $maxRetries) {
            // All retries failed - return error
            echo json_encode([
                "status" => false,
                "message" => "Database connection failed after {$maxRetries} attempts: " . $e->getMessage()
            ]);
            exit;
        }
        
        // Wait before retrying
        sleep($retryDelay);
    }
}