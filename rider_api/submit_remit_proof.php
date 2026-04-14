<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

require_once '/var/www/html/connection/db_connection.php';
require_once '/var/www/html/vendor/autoload.php';

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Api\Exception\ApiError;

try {
    // Configure Cloudinary
    Configuration::instance(getenv('CLOUDINARY_URL'));
    
    if (!getenv('CLOUDINARY_URL')) {
        throw new Exception('CLOUDINARY_URL environment variable is not set.');
    }
    
    // Get form data
    $rider_id = $_POST['rider_id'] ?? null;
    $earning_ids = $_POST['earning_ids'] ?? null;
    $gcash_number = $_POST['gcash_number'] ?? null;
    $amount = $_POST['amount'] ?? null;
    
    // Validate required fields
    if (!$rider_id || !$earning_ids || !$gcash_number || !$amount) {
        echo json_encode([
            "success" => false,
            "message" => "Missing required fields"
        ]);
        exit;
    }
    
    // Check if image was uploaded
    if (!isset($_FILES['proof_image']) || $_FILES['proof_image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode([
            "success" => false,
            "message" => "Proof image is required"
        ]);
        exit;
    }
    
    $rider_id = intval($rider_id);
    $gcash_number = trim($gcash_number);
    $amount = floatval($amount);
    
    // Parse earning_ids (can be comma-separated string)
    if (is_string($earning_ids)) {
        $earning_ids = array_map('intval', explode(',', $earning_ids));
    } else {
        $earning_ids = array_map('intval', $earning_ids);
    }
    
    $earning_ids = array_unique(array_filter($earning_ids));
    
    if (empty($earning_ids)) {
        echo json_encode([
            "success" => false,
            "message" => "No valid earning IDs provided"
        ]);
        exit;
    }
    
    // Validate GCash number format
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
            re.id, 
            re.rider_id, 
            re.is_remitted,
            (o.subtotal + o.platform_fee) as cod_amount
        FROM rider_earnings re
        INNER JOIN orders o ON re.order_id = o.id
        WHERE re.id IN ($placeholders)
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
    
    // Verify amount matches total COD
    if (abs($total_cod - $amount) > 0.01) {
        echo json_encode([
            "success" => false,
            "message" => "Amount does not match total COD amount. Expected: $total_cod"
        ]);
        exit;
    }
    
    // Upload image to Cloudinary
    $file = $_FILES['proof_image'];
    
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions)) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid file type. Allowed: " . implode(', ', $allowedExtensions)
        ]);
        exit;
    }
    
    $uploadApi = new UploadApi();
    $folder = 'capstone_app_images/remit_proofs';
    $publicId = 'remit_' . $rider_id . '_' . uniqid();
    
    $result = $uploadApi->upload($file['tmp_name'], [
        'folder' => $folder,
        'public_id' => $publicId,
        'overwrite' => true,
        'resource_type' => 'image',
        'quality' => 'auto',
        'fetch_format' => 'auto',
        'tags' => ['remit_proof', 'gcash', 'rider_' . $rider_id]
    ]);
    
    $proof_image_url = $result['secure_url'];
    
    // Begin transaction
    $conn->beginTransaction();
    
    try {
        // Insert remit proof
        $earning_ids_pg = '{' . implode(',', $earning_ids) . '}';
        
        $sql = "INSERT INTO remit_proofs (rider_id, earning_ids, gcash_number, amount, proof_image_url, status) 
                VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($sql);
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
            "proof_image_url" => $proof_image_url,
            "status" => "pending"
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
    
} catch (ApiError $e) {
    echo json_encode([
        "success" => false,
        "message" => "Cloudinary upload failed: " . $e->getMessage()
    ]);
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