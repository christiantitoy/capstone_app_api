<?php
// Get Neon connection string from Render environment variables
$databaseUrl = getenv("DATABASE_URL_NEON");

// Check if environment variable exists
if (!$databaseUrl) {
    echo json_encode([
        "status" => false,
        "message" => "Database environment variable not set"
    ]);
    exit;
}

// Parse the connection string
$parsed  = parse_url($databaseUrl);
$host    = $parsed['host'];
$port = 5432;
$dbname  = ltrim($parsed['path'], '/');
$user    = $parsed['user'];
$password = $parsed['pass'];

// Retry configuration
$maxRetries = 3;
$retryDelay = 1; // seconds
$attempt = 0;
$connected = false;

while (!$connected && $attempt < $maxRetries) {
    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

        $conn = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT         => false,
            PDO::ATTR_TIMEOUT            => 5,
            PDO::ATTR_EMULATE_PREPARES   => true,
        ]);

        $connected = true;

    } catch (PDOException $e) {
        $attempt++;

        if ($attempt >= $maxRetries) {
            echo json_encode([
                "status" => false,
                "message" => "Database connection failed after {$maxRetries} attempts: " . $e->getMessage()
            ]);
            exit;
        }

        sleep($retryDelay);
    }
}