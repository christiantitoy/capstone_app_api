<?php
header("Content-Type: application/json; charset=UTF-8");

// Get database URL from environment variable (set in Render.com)
$databaseUrl = getenv('DATABASE_URL');

if (!$databaseUrl) {
    echo json_encode([
        "status" => false,
        "message" => "DATABASE_URL environment variable not found"
    ]);
    exit;
}

try {
    // PDO can use the DATABASE_URL directly!
    // Render.com provides DATABASE_URL in format: postgresql://user:password@host:port/database
    $conn = new PDO($databaseUrl);
    
    // Set PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative array
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Optional: you can remove this echo in production
    echo json_encode([
        "status" => true,
        "message" => "DB connected successfully"
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "Database connection failed: " . $e->getMessage()
    ]);
    exit;
}
?>