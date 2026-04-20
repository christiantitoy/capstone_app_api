<?php
// /admin/backend/riders/update_rider_status.php
require_once '/var/www/html/connection/db_connection.php';
require_once '../session/auth_admin.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$riderId = $input['rider_id'] ?? null;
$verificationStatus = $input['verification_status'] ?? null;
$reason = $input['reason'] ?? '';

if (!$riderId || !$verificationStatus) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if (!in_array($verificationStatus, ['complete', 'rejected', 'pending', 'none', 'suspended'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid verification status']);
    exit;
}

try {
    $conn->beginTransaction();
    
    // Update rider verification status
    $stmt = $conn->prepare("
        UPDATE riders 
        SET verification_status = ?
        WHERE id = ?
    ");
    $stmt->execute([$verificationStatus, $riderId]);
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Rider verification status updated to {$verificationStatus}"
    ]);
    
} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>