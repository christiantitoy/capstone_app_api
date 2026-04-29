<?php
// /admin/backend/reports/get_buyer_reports.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once '/var/www/html/connection/db_connection.php';
require_once '/var/www/html/admin/backend/session/auth_admin.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];

    // GET - Fetch all reports with optional filters
    if ($method === 'GET') {
        $status = $_GET['status'] ?? null;
        $search = $_GET['search'] ?? null;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];

        if ($status && in_array($status, ['pending', 'reviewing', 'resolved', 'closed'])) {
            $where[] = "status = :status";
            $params[':status'] = $status;
        }

        if ($search) {
            $where[] = "(issue_type ILIKE :search OR CAST(delivery_id AS TEXT) LIKE :search2 OR CAST(buyer_id AS TEXT) LIKE :search3)";
            $params[':search'] = "%$search%";
            $params[':search2'] = "%$search%";
            $params[':search3'] = "%$search%";
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        // Get total count for pagination
        $countSql = "SELECT COUNT(*) as total FROM buyer_reports $whereClause";
        
        $countStmt = $conn->prepare($countSql);
        $countStmt->execute($params);
        $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        $totalPages = ceil($totalCount / $limit);

        // Fetch reports - only from buyer_reports table
        $sql = "SELECT 
                    id,
                    delivery_id,
                    buyer_id,
                    issue_type,
                    status,
                    created_at,
                    updated_at
                FROM buyer_reports
                $whereClause
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format the data
        $formattedReports = [];
        foreach ($reports as $row) {
            $formattedReports[] = [
                'id' => (int)$row['id'],
                'delivery_id' => (int)$row['delivery_id'],
                'buyer_id' => (int)$row['buyer_id'],
                'issue_type' => $row['issue_type'],
                'status' => $row['status'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at']
            ];
        }

        echo json_encode([
            'status' => 'success',
            'data' => [
                'reports' => $formattedReports,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_reports' => (int)$totalCount,
                    'per_page' => $limit
                ]
            ]
        ], JSON_PRETTY_PRINT);
        exit;
    }

    // POST - Update report status
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $reportId = $input['report_id'] ?? null;
        $newStatus = $input['status'] ?? null;

        if (!$reportId || !is_numeric($reportId)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Valid report_id is required']);
            exit;
        }

        $allowedStatuses = ['pending', 'reviewing', 'resolved', 'closed'];
        if (!$newStatus || !in_array($newStatus, $allowedStatuses)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Valid status is required']);
            exit;
        }

        $sql = "UPDATE buyer_reports SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':status' => $newStatus,
            ':id' => $reportId
        ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Report status updated successfully'
        ]);
        exit;
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>