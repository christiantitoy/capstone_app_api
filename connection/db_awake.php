<?php
// keep_alive.php - Place in your /var/www/html/ directory
// USING RENDER ENVIRONMENT VARIABLE FOR SESSION POOLER

header('Content-Type: application/json');

// Get the session pooler connection string from Render environment
// Your DATABASE_URL should be set to: postgresql://postgres.emotbcoheinzrosajnha:YOUR-PASSWORD@aws-1-ap-southeast-1.pooler.supabase.com:5432/postgres
$database_url = getenv('DATABASE_URL');

if (!$database_url) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => 'DATABASE_URL environment variable not set'
    ]);
    exit;
}

try {
    // Parse the DATABASE_URL for PDO
    // Render's DATABASE_URL format: postgresql://user:password@host:port/database
    $db_parts = parse_url($database_url);
    
    $host = $db_parts['host'];
    $port = $db_parts['port'] ?? '5432';
    $user = $db_parts['user'];
    $password = $db_parts['pass'];
    $dbname = ltrim($db_parts['path'], '/');
    
    // Build DSN for PDO
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    
    // PDO options
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_PERSISTENT => false,
    ];
    
    // Create connection using environment variable
    $conn = new PDO($dsn, $user, $password, $options);
    
    // Simple query to keep connection alive
    $stmt = $conn->query("SELECT 1 as keep_alive, current_timestamp as ping_time");
    $result = $stmt->fetch();
    
    if ($result) {
        // You can verify it's session pooler by checking the port from the URL
        $is_session_pooler = ($port == '5432');
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Keep-alive successful',
            'timestamp' => date('Y-m-d H:i:s'),
            'database_time' => $result['ping_time'],
            'connection_info' => [
                'host' => $host,
                'port' => $port,
                'pooler_mode' => $is_session_pooler ? 'session (port 5432)' : 'unknown',
                'environment' => 'Render'
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Query failed']);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Session pooler connection failed',
        'error' => $e->getMessage(),
        'environment' => 'Render'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unexpected error',
        'error' => $e->getMessage()
    ]);
} finally {
    // Always close the connection
    if (isset($conn)) {
        $conn = null;
    }
}
?>