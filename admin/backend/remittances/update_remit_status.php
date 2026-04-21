<?php
// /admin/backend/remittances/update_remit_status.php
require_once '/var/www/html/connection/db_connection.php';
require_once '../session/auth_admin.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$remitId = $input['remit_id'] ?? null;
$status = $input['status'] ?? null;
$reason = $input['reason'] ?? null;

if (!$remitId || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if (!in_array($status, ['confirmed', 'rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

// Require reason when rejecting
if ($status === 'rejected' && empty($reason)) {
    echo json_encode(['success' => false, 'message' => 'Rejection reason is required']);
    exit;
}

try {
    $conn->beginTransaction();
    
    // Get remit proof to get earning IDs
    $stmt = $conn->prepare("SELECT earning_ids FROM remit_proofs WHERE id = ?");
    $stmt->execute([$remitId]);
    $remitProof = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$remitProof) {
        throw new Exception('Remit proof not found');
    }
    
    if ($status === 'rejected') {
        // Update with rejection reason
        $stmt = $conn->prepare("
            UPDATE remit_proofs 
            SET status = ?, 
                rejection_reason = ?
            WHERE id = ?
        ");
        $stmt->execute([$status, $reason, $remitId]);
    } else {
        // Update status and mark earnings as remitted
        $stmt = $conn->prepare("
            UPDATE remit_proofs 
            SET status = ?, 
                rejection_reason = NULL
            WHERE id = ?
        ");
        $stmt->execute([$status, $remitId]);
        
        // Mark earnings as remitted
        $earningIds = explode(',', trim($remitProof['earning_ids'], '{}'));
        if (!empty($earningIds)) {
            $placeholders = implode(',', array_fill(0, count($earningIds), '?'));
            $updateSql = "UPDATE rider_earnings SET is_remitted = true WHERE id IN ($placeholders)";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->execute($earningIds);
        }
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Remittance {$status} successfully!"
    ]);
    
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>