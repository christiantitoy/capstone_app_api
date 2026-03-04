<?php
header("Content-Type: application/json; charset=UTF-8");

// 🔹 Supabase connection string
$databaseUrl = "postgresql://postgres:4Oypga8lInmFLBb5@db.emotbcoheinzrosajnha.supabase.co:5432/postgres";

try {
    // Parse the URL into components
    $db = parse_url($databaseUrl);

    $host = $db['host'];
    $port = $db['port'];
    $user = $db['user'];
    $pass = $db['pass'];
    $dbname = ltrim($db['path'], '/');

    // 🔹 PDO DSN with SSL required
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

    // 🔹 Create PDO connection
    $conn = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,       // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // Fetch associative arrays
        PDO::ATTR_EMULATE_PREPARES => false                // Use native prepared statements
    ]);

    // Optional: Test connection
    
    echo json_encode([
        "status" => true,
        "message" => "Database connected successfully!"
    ]);
    

} catch (PDOException $e) {
    // 🔴 Connection failed
    echo json_encode([
        "status" => false,
        "message" => "Database connection failed: " . $e->getMessage()
    ]);
    exit;
}
?>