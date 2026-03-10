<?php
// header("Content-Type: application/json; charset=UTF-8");

$databaseUrl = getenv('DATABASE_URL');

if (!$databaseUrl) {
    echo json_encode([
        "status" => false,
        "message" => "DATABASE_URL not found"
    ]);
    exit;
}

try {
    $db = parse_url($databaseUrl);

    $host = $db['host'];
    $port = $db['port'];
    $user = $db['user'];
    $pass = $db['pass'];
    $dbname = ltrim($db['path'], '/');

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

    $conn = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // echo json_encode([
    //     "status" => true,
    //     "message" => "Database connected successfully, finally"
    // ]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "Database connection failed: " . $e->getMessage()
    ]);
}
?>