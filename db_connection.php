<?php
// Get database URL from Render environment
$databaseUrl = getenv('DATABASE_URL');

// Connect to PostgreSQL
$conn = pg_connect($databaseUrl);

if (!$conn) {
    echo json_encode([
        "status" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}
?>