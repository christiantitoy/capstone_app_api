<?php
// /admin/backend/riders/get_rider_details.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid rider ID'
    ]);
    exit;
}

$riderId = (int) $_GET['id'];

try {
    // Get rider details
    $sql = "
        SELECT 
            r.id,
            r.username,
            r.email,
            r.status,
            r.verification_status,
            r.created_at
        FROM riders r
        WHERE r.id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$riderId]);
    $rider = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rider) {
        echo json_encode([
            'success' => false,
            'message' => 'Rider not found'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'data' => $rider
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>