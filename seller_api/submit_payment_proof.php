<?php
header('Content-Type: application/json');
require_once '/var/www/html/connection/db_connection.php';

try {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid JSON payload"
        ]);
        exit;
    }
    
    // Validate required fields
    $required = ['order_id', 'buyer_id', 'gcash_number', 'proof_image_url', 'amount'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing field: $field"
            ]);
            exit;
        }
    }
    
    $order_id = intval($data['order_id']);
    $buyer_id = intval($data['buyer_id']);
    $gcash_number = $data['gcash_number'];
    $proof_image_url = $data['proof_image_url'];
    $amount = floatval($data['amount']);
    
    // Validate GCash number format (starts with 09 and 11 digits)
    if (!preg_match('/^09[0-9]{9}$/', $gcash_number)) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid GCash number. Must start with 09 and be 11 digits."
        ]);
        exit;
    }
    
    // Check if order exists and belongs to buyer
    $checkOrder = $conn->prepare("SELECT id, total_amount FROM orders WHERE id = ? AND buyer_id = ?");
    $checkOrder->execute([$order_id, $buyer_id]);
    $order = $checkOrder->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode([
            "status" => "error",
            "message" => "Order not found or does not belong to this buyer"
        ]);
        exit;
    }
    
    // Verify amount matches order total
    if (abs($order['total_amount'] - $amount) > 0.01) {
        echo json_encode([
            "status" => "error",
            "message" => "Amount does not match order total"
        ]);
        exit;
    }
    
    // Check if proof already exists for this order
    $checkProof = $conn->prepare("SELECT id, status FROM payment_proofs WHERE order_id = ?");
    $checkProof->execute([$order_id]);
    $existingProof = $checkProof->fetch(PDO::FETCH_ASSOC);
    
    if ($existingProof) {
        // Update existing proof if still pending
        if ($existingProof['status'] === 'pending') {
            $sql = "UPDATE payment_proofs 
                    SET gcash_number = ?, proof_image_url = ?, amount = ?, submitted_at = CURRENT_TIMESTAMP 
                    WHERE order_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$gcash_number, $proof_image_url, $amount, $order_id]);
            
            echo json_encode([
                "status" => "success",
                "message" => "Payment proof updated successfully",
                "proof_id" => $existingProof['id']
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Payment proof already " . $existingProof['status'] . ". Cannot resubmit."
            ]);
        }
        exit;
    }
    
    // Insert new payment proof
    $sql = "INSERT INTO payment_proofs (order_id, buyer_id, gcash_number, proof_image_url, amount, status) 
            VALUES (?, ?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$order_id, $buyer_id, $gcash_number, $proof_image_url, $amount]);
    
    $proof_id = $conn->lastInsertId();
    
    echo json_encode([
        "status" => "success",
        "message" => "Payment proof submitted successfully",
        "proof_id" => $proof_id,
        "order_id" => $order_id
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}

$conn = null;
?>