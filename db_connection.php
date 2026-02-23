<?php
$databaseUrl = getenv('DATABASE_URL');

if (!$databaseUrl) {
    echo json_encode([
        "status" => false,
        "message" => "DATABASE_URL env variable not found"
    ]);
    exit;
}

$conn = pg_connect($databaseUrl);

if (!$conn) {
    echo json_encode([
        "status" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

echo json_encode([
    "status" => true,
    "message" => "DB connected successfully"
]);
?>