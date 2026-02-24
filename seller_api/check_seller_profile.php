<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once '/var/www/html/connection/db_connection.php';

try {
    // Get POST data
    $buyer_id = $_POST['buyer_id'] ?? null;

    if (!$buyer_id) {
        echo json_encode(["status" => "error", "message" => "Buyer ID required"]);
        exit;
    }

    $buyer_id = intval($buyer_id);

    // Check if seller profile exists and get seller data WITH document info
    $sql = "SELECT 
                sp.id, 
                sp.fullname, 
                sp.is_approved,
                CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM seller_documents sd 
                        WHERE sd.seller_id = sp.id
                    ) THEN 1 
                    ELSE 0 
                END as has_documents,
                COALESCE(doc_count.document_count, 0) as document_count
            FROM seller_profiles sp
            LEFT JOIN (
                SELECT seller_id, COUNT(id) as document_count 
                FROM seller_documents 
                GROUP BY seller_id
            ) doc_count ON sp.id = doc_count.seller_id
            WHERE sp.buyer_id = :buyer_id";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([':buyer_id' => $buyer_id]);
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode([
            "status" => "success", 
            "message" => "Seller profile exists",
            "seller_id" => (int)$row['id'],
            "fullname" => $row['fullname'],
            "is_approved" => (int)$row['is_approved'],
            "has_documents" => (bool)$row['has_documents'],
            "document_count" => (int)$row['document_count']
        ]);
    } else {
        echo json_encode([
            "status" => "error", 
            "message" => "No seller profile found"
        ]);
    }

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}

$conn = null; // Close PDO connection
?>