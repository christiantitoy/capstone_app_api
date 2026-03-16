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

try {

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

    $conn = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_PERSISTENT => false
    ]);

    // Optional success test
    // echo json_encode([
    //     "status" => true,
    //     "message" => "Database connected successfully"
    // ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => "Database connection failed: " . $e->getMessage()
    ]);

}
?>