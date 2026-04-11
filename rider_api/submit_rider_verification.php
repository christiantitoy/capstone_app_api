<?php
// submit_rider_verification.php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

require_once '/var/www/html/vendor/autoload.php';
require_once '/var/www/html/connection/db_connection.php';

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Api\Exception\ApiError;

try {
    // Configure Cloudinary
    Configuration::instance(getenv('CLOUDINARY_URL'));
    
    if (!getenv('CLOUDINARY_URL')) {
        throw new Exception('CLOUDINARY_URL environment variable is not set.');
    }

    // Get rider_id and text data from POST
    $rider_id = isset($_POST['rider_id']) ? intval($_POST['rider_id']) : null;
    $id_type = isset($_POST['id_type']) ? trim($_POST['id_type']) : null;
    $id_number = isset($_POST['id_number']) ? trim($_POST['id_number']) : null;

    // Validate required fields
    if (!$rider_id || !$id_type || !$id_number) {
        throw new Exception('Missing required fields: rider_id, id_type, or id_number');
    }

    // Check if files were uploaded
    if (!isset($_FILES['id_front']) || !isset($_FILES['id_back']) || !isset($_FILES['barangay_clearance'])) {
        throw new Exception('Missing required files: id_front, id_back, or barangay_clearance');
    }

    // Check if rider exists and get current verification_status
    $stmt = $conn->prepare("SELECT id, verification_status FROM riders WHERE id = :rider_id");
    $stmt->execute([':rider_id' => $rider_id]);
    $rider = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$rider) {
        throw new Exception('Rider not found');
    }

    // Check if already verified
    if ($rider['verification_status'] === 'complete') {
        throw new Exception('Your account is already verified');
    }

    // Check if verification already exists in rider_verifications table
    $stmt = $conn->prepare("SELECT id, status FROM rider_verifications WHERE rider_id = :rider_id");
    $stmt->execute([':rider_id' => $rider_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        if ($existing['status'] === 'pending') {
            throw new Exception('Verification already submitted and pending review');
        } elseif ($existing['status'] === 'approved') {
            throw new Exception('Your account is already verified');
        }
        // If rejected, allow resubmission (will update existing record)
    }

    // Validate file uploads
    $files = ['id_front', 'id_back', 'barangay_clearance'];
    foreach ($files as $fileKey) {
        $file = $_FILES[$fileKey];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Upload error for $fileKey: " . $file['error']);
        }
    }

    // Allowed file extensions (images and PDF)
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
    
    // Upload files to Cloudinary
    $uploadApi = new UploadApi();
    $folder = 'capstone_app_images/rider_verifications/rider_' . $rider_id;
    $uploadedUrls = [];

    foreach ($files as $fileKey) {
        $file = $_FILES[$fileKey];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception("Invalid file type for $fileKey. Allowed: " . implode(', ', $allowedExtensions));
        }

        // Determine resource type
        $resourceType = ($extension === 'pdf') ? 'raw' : 'image';
        
        $publicId = $fileKey . '_' . uniqid();
        
        try {
            $result = $uploadApi->upload($file['tmp_name'], [
                'folder' => $folder,
                'public_id' => $publicId,
                'overwrite' => true,
                'resource_type' => $resourceType,
                'quality' => 'auto',
                'fetch_format' => 'auto',
                'tags' => ['rider_verification', $fileKey, "rider_$rider_id"]
            ]);
            
            $uploadedUrls[$fileKey] = $result['secure_url'];
        } catch (ApiError $e) {
            throw new Exception("Cloudinary upload failed for $fileKey: " . $e->getMessage());
        }
    }

    // Begin transaction
    $conn->beginTransaction();

    try {
        // Save to rider_verifications table
        if ($existing) {
            // Update existing rejected verification
            $stmt = $conn->prepare("
                UPDATE rider_verifications 
                SET id_type = :id_type,
                    id_number = :id_number,
                    id_front_url = :id_front_url,
                    id_back_url = :id_back_url,
                    barangay_clearance_url = :barangay_clearance_url,
                    status = 'pending',
                    submitted_at = NOW(),
                    reviewed_at = NULL
                WHERE rider_id = :rider_id
            ");
        } else {
            // Insert new verification
            $stmt = $conn->prepare("
                INSERT INTO rider_verifications 
                (rider_id, id_type, id_number, id_front_url, id_back_url, barangay_clearance_url, status, submitted_at)
                VALUES 
                (:rider_id, :id_type, :id_number, :id_front_url, :id_back_url, :barangay_clearance_url, 'pending', NOW())
            ");
        }

        $result = $stmt->execute([
            ':rider_id' => $rider_id,
            ':id_type' => $id_type,
            ':id_number' => $id_number,
            ':id_front_url' => $uploadedUrls['id_front'],
            ':id_back_url' => $uploadedUrls['id_back'],
            ':barangay_clearance_url' => $uploadedUrls['barangay_clearance']
        ]);

        if (!$result) {
            throw new Exception('Failed to save verification data to database');
        }

        // ✅ UPDATE RIDER'S verification_status TO 'pending'
        $updateRiderStmt = $conn->prepare("
            UPDATE riders 
            SET verification_status = 'pending' 
            WHERE id = :rider_id
        ");
        $updateRiderStmt->execute([':rider_id' => $rider_id]);

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Verification documents submitted successfully',
            'verification_status' => 'pending',
            'urls' => $uploadedUrls
        ]);

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollBack();
        throw $e;
    }

} catch (ApiError $e) {
    http_response_code(502);
    echo json_encode([
        'status' => 'error',
        'message' => 'Cloudinary API Error: ' . $e->getMessage()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn = null;
?>