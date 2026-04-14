<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once '/var/www/html/connection/db_connection.php';

try {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid JSON payload"
        ]);
        exit;
    }
    
    // Validate required fields
    $required = ['rider_id', 'earning_ids', 'gcash_number', 'proof_image_url', 'amount'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            echo json_encode([
                "success" => false,
                "message" => "Missing field: $field"
            ]);
            exit;
        }
    }
    
    $rider_id = intval($data['rider_id']);
    $gcash_number = $data['gcash_number'];
    $proof_image_url = $data['proof_image_url'];
    $amount = floatval($data['amount']);
    
    // Parse earning_ids (can be array or comma-separated string)
    if (is_array($data['earning_ids'])) {
        $earning_ids = array_map('intval', $data['earning_ids']);
    } else {
        $earning_ids = array_map('intval', explode(',', $data['earning_ids']));
    }
    
    // Remove duplicates and empty values
    $earning_ids = array_unique(array_filter($earning_ids));
    
    if (empty($earning_ids)) {
        echo json_encode([
            "success" => false,
            "message" => "No valid earning IDs provided"
        ]);
        exit;
    }
    
    // Validate GCash number format (starts with 09 and 11 digits)
    if (!preg_match('/^09[0-9]{9}$/', $gcash_number)) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid GCash number. Must start with 09 and be 11 digits."
        ]);
        exit;
    }
    
    // Check if rider exists
    $checkRider = $conn->prepare("SELECT id FROM riders WHERE id = ?");
    $checkRider->execute([$rider_id]);
    
    if (!$checkRider->fetch()) {
        echo json_encode([
            "success" => false,
            "message" => "Rider not found"
        ]);
        exit;
    }
    
    // Verify all earnings belong to this rider and are not yet remitted
    $placeholders = implode(',', array_fill(0, count($earning_ids), '?'));
    $checkEarnings = $conn->prepare("
        SELECT 
            id, 
            rider_id, 
            is_remitted,
            (SELECT subtotal + platform_fee FROM orders WHERE id = order_id) as cod_amount
        FROM rider_earnings 
        WHERE id IN ($placeholders)
    ");
    $checkEarnings->execute($earning_ids);
    $earnings = $checkEarnings->fetchAll(PDO::FETCH_ASSOC);
    
    // Verify count matches
    if (count($earnings) !== count($earning_ids)) {
        echo json_encode([
            "success" => false,
            "message" => "One or more earnings not found"
        ]);
        exit;
    }
    
    // Calculate total COD amount and verify ownership/status
    $total_cod = 0;
    foreach ($earnings as $earning) {
        if ($earning['rider_id'] != $rider_id) {
            echo json_encode([
                "success" => false,
                "message" => "Earning #{$earning['id']} does not belong to this rider"
            ]);
            exit;
        }
        
        if ($earning['is_remitted'] == true) {
            echo json_encode([
                "success" => false,
                "message" => "Earning #{$earning['id']} is already remitted"
            ]);
            exit;
        }
        
        $total_cod += floatval($earning['cod_amount']);
    }
    
    // Verify amount matches total COD (within 0.01 tolerance)
    if (abs($total_cod - $amount) > 0.01) {
        echo json_encode([
            "success" => false,
            "message" => "Amount does not match total COD amount. Expected: $total_cod"
        ]);
        exit;
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    try {
        // Insert remit proof
        $sql = "INSERT INTO remit_proofs (rider_id, earning_ids, gcash_number, amount, proof_image_url, status) 
                VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($sql);
        
        // Convert array to PostgreSQL array format
        $earning_ids_pg = '{' . implode(',', $earning_ids) . '}';
        $stmt->execute([$rider_id, $earning_ids_pg, $gcash_number, $amount, $proof_image_url]);
        
        $proof_id = $conn->lastInsertId();
        
        // Update rider_earnings to mark as remitted (paid by rider)
        $updateEarnings = $conn->prepare("
            UPDATE rider_earnings 
            SET is_remitted = true 
            WHERE id IN ($placeholders)
        ");
        $updateEarnings->execute($earning_ids);
        
        $conn->commit();
        
        echo json_encode([
            "success" => true,
            "message" => "Remittance proof submitted successfully. Waiting for admin confirmation.",
            "proof_id" => $proof_id,
            "earning_ids" => $earning_ids,
            "amount" => $amount,
            "status" => "pending"
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}

$conn = null;
?>