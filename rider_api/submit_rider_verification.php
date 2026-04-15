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

// Set PHP configuration for Cloudinary free tier (10MB limit)
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '35M'); // 3 files × 10MB
ini_set('max_execution_time', '300');
ini_set('max_input_time', '300');

define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB limit

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
    $requiredFiles = ['id_front', 'id_back', 'barangay_clearance'];
    foreach ($requiredFiles as $fileKey) {
        if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) {
            throw new Exception("Missing required file: $fileKey");
        }
    }

    // Check if rider exists
    $stmt = $conn->prepare("SELECT id, verification_status FROM riders WHERE id = :rider_id");
    $stmt->execute([':rider_id' => $rider_id]);
    $rider = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$rider) {
        throw new Exception('Rider not found');
    }

    if ($rider['verification_status'] === 'complete') {
        throw new Exception('Your account is already verified');
    }

    // Check if verification already exists
    $stmt = $conn->prepare("SELECT id, status FROM rider_verifications WHERE rider_id = :rider_id");
    $stmt->execute([':rider_id' => $rider_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        if ($existing['status'] === 'pending') {
            throw new Exception('Verification already submitted and pending review');
        } elseif ($existing['status'] === 'approved') {
            throw new Exception('Your account is already verified');
        }
    }

    // Allowed image extensions only
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $allowedMimeTypes = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp'
    ];
    
    $uploadApi = new UploadApi();
    $folder = 'capstone_app_images/rider_verifications/rider_' . $rider_id;
    $uploadedUrls = [];

    // Upload all files
    $files = ['id_front', 'id_back', 'barangay_clearance'];
    
    foreach ($files as $fileKey) {
        $file = $_FILES[$fileKey];
        $sizeInMB = round($file['size'] / (1024 * 1024), 2);
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Upload error for $fileKey: " . getUploadErrorMessage($file['error']));
        }
        
        if ($file['size'] > MAX_FILE_SIZE) {
            throw new Exception("File '$fileKey' is {$sizeInMB}MB. Maximum is 10MB.");
        }
        
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new Exception("Security violation for $fileKey");
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception("$fileKey must be an image (JPG, PNG, GIF, WEBP)");
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedMimeTypes)) {
            throw new Exception("$fileKey must be a valid image file");
        }

        $publicId = $fileKey . '_' . uniqid();
        
        try {
            $result = $uploadApi->upload($file['tmp_name'], [
                'folder' => $folder,
                'public_id' => $publicId,
                'overwrite' => true,
                'resource_type' => 'image',
                'tags' => ['rider_verification', $fileKey, "rider_$rider_id"],
                'timeout' => 120,
                'transformation' => [
                    ['quality' => 'auto:good', 'fetch_format' => 'auto']
                ]
            ]);
            
            $uploadedUrls[$fileKey] = $result['secure_url'];
            
        } catch (ApiError $e) {
            throw new Exception("Upload failed for $fileKey: " . $e->getMessage());
        }
    }

    // Save to database
    $conn->beginTransaction();

    try {
        if ($existing) {
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
            ':barangay_clearance_url' => $uploadedUrls['barangay_clearance']  // Single string
        ]);

        if (!$result) {
            throw new Exception('Failed to save to database');
        }

        $updateRiderStmt = $conn->prepare("
            UPDATE riders 
            SET verification_status = 'pending' 
            WHERE id = :rider_id
        ");
        $updateRiderStmt->execute([':rider_id' => $rider_id]);

        $conn->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Verification submitted successfully',
            'urls' => [
                'id_front' => $uploadedUrls['id_front'],
                'id_back' => $uploadedUrls['id_back'],
                'barangay_clearance' => $uploadedUrls['barangay_clearance']  // Single string
            ]
        ]);

    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }

} catch (ApiError $e) {
    http_response_code(502);
    echo json_encode(['status' => 'error', 'message' => 'Upload service error']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn = null;

function getUploadErrorMessage($code) {
    switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
            return 'File exceeds 10MB limit';
        case UPLOAD_ERR_FORM_SIZE:
            return 'File exceeds form size limit';
        case UPLOAD_ERR_PARTIAL:
            return 'File partially uploaded';
        case UPLOAD_ERR_NO_FILE:
            return 'No file uploaded';
        default:
            return 'Upload error';
    }
}
?>