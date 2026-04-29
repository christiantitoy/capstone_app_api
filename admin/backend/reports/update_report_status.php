<?php
// /admin/backend/reports/update_report_status.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once '/var/www/html/connection/db_connection.php';
require_once '/var/www/html/admin/backend/session/auth_admin.php';

try {
    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Method not allowed'
        ]);
        exit;
    }

    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    
    $reportId = $input['report_id'] ?? null;
    $newStatus = $input['status'] ?? null;

    // Validate report_id
    if (!$reportId || !is_numeric($reportId)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Valid report_id is required'
        ]);
        exit;
    }

    // Validate status
    $allowedStatuses = ['pending', 'reviewing', 'resolved', 'closed'];
    if (!$newStatus || !in_array($newStatus, $allowedStatuses)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Valid status is required (pending, reviewing, resolved, closed)'
        ]);
        exit;
    }

    // Check if report exists
    $checkSql = "SELECT id, status FROM buyer_reports WHERE id = :id";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->execute([':id' => $reportId]);
    $existingReport = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$existingReport) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Report not found'
        ]);
        exit;
    }

    // Prevent updating to the same status
    if ($existingReport['status'] === $newStatus) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Report is already in ' . $newStatus . ' status'
        ]);
        exit;
    }

    // Update the report status
    $updateSql = "UPDATE buyer_reports 
                  SET status = :status, 
                      updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
    
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->execute([
        ':status' => $newStatus,
        ':id' => $reportId
    ]);

    // Check if update was successful
    if ($updateStmt->rowCount() > 0) {
        // Log the status change (you can expand this later)
        $logMessage = sprintf(
            "Report #%d status changed from '%s' to '%s' by admin #%d",
            $reportId,
            $existingReport['status'],
            $newStatus,
            $_SESSION['admin_id'] ?? 0
        );
        error_log($logMessage);

        echo json_encode([
            'status' => 'success',
            'message' => 'Report status updated successfully',
            'data' => [
                'report_id' => (int)$reportId,
                'old_status' => $existingReport['status'],
                'new_status' => $newStatus
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update report status'
        ]);
    }

} catch (PDOException $e) {
    error_log('Database error in update_report_status.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log('General error in update_report_status.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An unexpected error occurred'
    ]);
}
?>