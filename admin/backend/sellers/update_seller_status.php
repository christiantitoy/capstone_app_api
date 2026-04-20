<?php
// /admin/backend/sellers/update_seller_status.php
require_once '/var/www/html/connection/db_connection.php';
require_once '../session/auth_admin.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$sellerId = $input['seller_id'] ?? null;
$status = $input['status'] ?? null;
$reason = $input['reason'] ?? '';

if (!$sellerId || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if (!in_array($status, ['approved', 'rejected', 'pending'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    $conn->beginTransaction();
    
    // Update seller approval status
    $stmt = $conn->prepare("
        UPDATE sellers 
        SET approval_status = ?, 
            updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$status, $sellerId]);
    
    // Log the action (optional - create admin_logs table if needed)
    $adminId = $_SESSION['admin_id'] ?? null;
    if ($adminId) {
        $stmt = $conn->prepare("
            INSERT INTO admin_logs (admin_id, action, target_type, target_id, details, created_at)
            VALUES (?, ?, 'seller', ?, ?, NOW())
        ");
        $details = json_encode(['status' => $status, 'reason' => $reason]);
        $stmt->execute([$adminId, "update_seller_status", $sellerId, $details]);
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Seller status updated to {$status}"
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