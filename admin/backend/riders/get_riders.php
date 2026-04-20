<?php
// /admin/backend/riders/get_riders.php
require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

try {
    // Get rider statistics
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) AS total_riders,
            SUM(CASE WHEN verification_status = 'complete' THEN 1 ELSE 0 END) AS approved_riders,
            SUM(CASE WHEN verification_status = 'pending' THEN 1 ELSE 0 END) AS pending_riders,
            SUM(CASE WHEN verification_status = 'none' THEN 1 ELSE 0 END) AS unverified_riders,
            SUM(CASE WHEN verification_status = 'rejected' THEN 1 ELSE 0 END) AS rejected_riders,
            SUM(CASE WHEN status = 'online' THEN 1 ELSE 0 END) AS online_riders,
            SUM(CASE WHEN status = 'delivering' THEN 1 ELSE 0 END) AS delivering_riders,
            SUM(CASE WHEN status = 'offline' THEN 1 ELSE 0 END) AS offline_riders
        FROM riders
    ");
    $stmt->execute();
    $statistics = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get all riders with their details
    $sql = "
        SELECT 
            r.id,
            r.username,
            r.email,
            r.status,
            r.verification_status,
            r.created_at
        FROM riders r
        ORDER BY r.id DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $riders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'statistics' => [
            'total' => (int) $statistics['total_riders'],
            'approved' => (int) $statistics['approved_riders'],
            'pending' => (int) $statistics['pending_riders'],
            'unverified' => (int) $statistics['unverified_riders'],
            'rejected' => (int) $statistics['rejected_riders'],
            'online' => (int) $statistics['online_riders'],
            'delivering' => (int) $statistics['delivering_riders'],
            'offline' => (int) $statistics['offline_riders']
        ],
        'data' => $riders
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