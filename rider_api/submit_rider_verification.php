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
ini_set('post_max_size', '50M'); // Increased for multiple files
ini_set('max_execution_time', '300');
ini_set('max_input_time', '300');
ini_set('max_file_uploads', '10'); // Allow multiple file uploads

// Cloudinary Free Plan Limits
define('CLOUDINARY_MAX_IMAGE_SIZE', 10 * 1024 * 1024); // 10MB for images
define('CLOUDINARY_MAX_PDF_SIZE', 10 * 1024 * 1024);   // 10MB for PDFs
define('MAX_FILE_SIZE', 10 * 1024 * 1024);             // Overall 10MB limit

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
    if (!isset($_FILES['id_front']) || !isset($_FILES['id_back'])) {
        throw new Exception('Missing required files: id_front or id_back');
    }

    // Check if barangay_clearance files exist (could be single or multiple)
    $hasClearanceFiles = false;
    if (isset($_FILES['barangay_clearance'])) {
        $hasClearanceFiles = true;
    }

    if (!$hasClearanceFiles) {
        throw new Exception('Missing required files: barangay_clearance');
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

    // Allowed file extensions and MIME types
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
    $allowedMimeTypes = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf'
    ];
    
    // Upload files to Cloudinary
    $uploadApi = new UploadApi();
    $folder = 'capstone_app_images/rider_verifications/rider_' . $rider_id;
    $uploadedUrls = [];

    // Handle single file uploads (id_front, id_back)
    $singleFiles = ['id_front', 'id_back'];
    
    foreach ($singleFiles as $fileKey) {
        $file = $_FILES[$fileKey];
        $sizeInMB = round($file['size'] / (1024 * 1024), 2);
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessage = getUploadErrorMessage($file['error']);
            throw new Exception("Upload error for $fileKey: " . $errorMessage);
        }
        
        // Check overall size limit
        if ($file['size'] > MAX_FILE_SIZE) {
            throw new Exception(
                "File '$fileKey' is {$sizeInMB}MB which exceeds Cloudinary's free plan limit of 10MB."
            );
        }
        
        // Verify it's a valid uploaded file
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new Exception("Security violation for $fileKey: Possible file upload attack");
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Validate file extension
        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception("Invalid file type for $fileKey. Allowed types: " . implode(', ', $allowedExtensions));
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedMimeTypes)) {
            throw new Exception("Invalid file format detected for $fileKey.");
        }

        // Determine resource type
        $resourceType = ($extension === 'pdf') ? 'raw' : 'image';
        $publicId = $fileKey . '_' . uniqid();
        
        try {
            $uploadOptions = [
                'folder' => $folder,
                'public_id' => $publicId,
                'overwrite' => true,
                'resource_type' => $resourceType,
                'tags' => ['rider_verification', $fileKey, "rider_$rider_id"],
                'timeout' => 120,
                'chunk_size' => 6000000
            ];
            
            if ($resourceType === 'image') {
                $uploadOptions['transformation'] = [
                    [
                        'quality' => 'auto:good',
                        'fetch_format' => 'auto',
                        'flags' => 'progressive'
                    ]
                ];
            }
            
            $result = $uploadApi->upload($file['tmp_name'], $uploadOptions);
            $uploadedUrls[$fileKey] = $result['secure_url'];
            
        } catch (ApiError $e) {
            $errorMsg = $e->getMessage();
            
            if (strpos($errorMsg, 'File size too large') !== false || 
                strpos($errorMsg, 'exceeds the limit') !== false) {
                throw new Exception(
                    "Cloudinary rejected $fileKey: File size ({$sizeInMB}MB) exceeds free plan limit of 10MB."
                );
            } else {
                throw new Exception("Upload failed for $fileKey: " . $errorMsg);
            }
        }
    }

    // Handle multiple barangay_clearance files
    $clearanceUrls = [];
    $clearanceFiles = $_FILES['barangay_clearance'];
    
    // Check if multiple files were uploaded
    $isMultiple = is_array($clearanceFiles['name']);
    
    if ($isMultiple) {
        // Multiple files uploaded
        $fileCount = count($clearanceFiles['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            $fileInfo = [
                'name' => $clearanceFiles['name'][$i],
                'type' => $clearanceFiles['type'][$i],
                'tmp_name' => $clearanceFiles['tmp_name'][$i],
                'error' => $clearanceFiles['error'][$i],
                'size' => $clearanceFiles['size'][$i]
            ];
            
            $clearanceUrl = uploadClearanceFile($fileInfo, $i, $uploadApi, $folder, $rider_id, $allowedExtensions, $allowedMimeTypes);
            if ($clearanceUrl) {
                $clearanceUrls[] = $clearanceUrl;
            }
        }
    } else {
        // Single file uploaded
        $clearanceUrl = uploadClearanceFile($clearanceFiles, 0, $uploadApi, $folder, $rider_id, $allowedExtensions, $allowedMimeTypes);
        if ($clearanceUrl) {
            $clearanceUrls[] = $clearanceUrl;
        }
    }
    
    if (empty($clearanceUrls)) {
        throw new Exception('No valid barangay clearance files were uploaded.');
    }

    // Convert clearance URLs to JSON string for storage
    $barangayClearanceJson = json_encode($clearanceUrls);

    // Begin transaction
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
            ':barangay_clearance_url' => $barangayClearanceJson
        ]);

        if (!$result) {
            throw new Exception('Failed to save verification data to database');
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
            'message' => 'Verification documents submitted successfully. Your documents are pending review.',
            'verification_status' => 'pending',
            'urls' => [
                'id_front' => $uploadedUrls['id_front'],
                'id_back' => $uploadedUrls['id_back'],
                'barangay_clearance' => $clearanceUrls
            ],
            'note' => 'Files have been optimized to meet Cloudinary free tier requirements.'
        ]);

    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }

} catch (ApiError $e) {
    http_response_code(502);
    echo json_encode([
        'status' => 'error',
        'message' => 'Cloudinary service error. Please try again later.'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred. Please try again.'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'help' => 'For files over 10MB, please compress them before uploading.'
    ]);
}

$conn = null;

/**
 * Upload a single clearance file to Cloudinary
 */
function uploadClearanceFile($file, $index, $uploadApi, $folder, $rider_id, $allowedExtensions, $allowedMimeTypes) {
    $sizeInMB = round($file['size'] / (1024 * 1024), 2);
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        error_log("Upload error for clearance file $index: " . getUploadErrorMessage($file['error']));
        return null;
    }
    
    // Check size limit
    if ($file['size'] > MAX_FILE_SIZE) {
        error_log("Clearance file $index exceeds 10MB limit");
        return null;
    }
    
    // Verify it's a valid uploaded file
    if (!is_uploaded_file($file['tmp_name'])) {
        error_log("Security violation for clearance file $index");
        return null;
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Validate file extension
    if (!in_array($extension, $allowedExtensions)) {
        error_log("Invalid file type for clearance file $index");
        return null;
    }

    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedMimeTypes)) {
        error_log("Invalid MIME type for clearance file $index");
        return null;
    }

    // Determine resource type
    $resourceType = ($extension === 'pdf') ? 'raw' : 'image';
    $publicId = 'barangay_clearance_' . $index . '_' . uniqid();
    
    try {
        $uploadOptions = [
            'folder' => $folder,
            'public_id' => $publicId,
            'overwrite' => true,
            'resource_type' => $resourceType,
            'tags' => ['rider_verification', 'barangay_clearance', "rider_$rider_id"],
            'timeout' => 120,
            'chunk_size' => 6000000
        ];
        
        if ($resourceType === 'image') {
            $uploadOptions['transformation'] = [
                [
                    'quality' => 'auto:good',
                    'fetch_format' => 'auto',
                    'flags' => 'progressive'
                ]
            ];
        }
        
        $result = $uploadApi->upload($file['tmp_name'], $uploadOptions);
        
        error_log("Successfully uploaded clearance file $index - Size: {$sizeInMB}MB");
        
        return $result['secure_url'];
        
    } catch (ApiError $e) {
        error_log("Cloudinary upload failed for clearance file $index: " . $e->getMessage());
        return null;
    }
}

/**
 * Get human-readable upload error message
 */
function getUploadErrorMessage($code) {
    switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
            return 'File exceeds maximum allowed size (10MB).';
        case UPLOAD_ERR_FORM_SIZE:
            return 'File exceeds form size limit (10MB).';
        case UPLOAD_ERR_PARTIAL:
            return 'File was only partially uploaded.';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was uploaded.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Missing temporary folder.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Failed to write file.';
        case UPLOAD_ERR_EXTENSION:
            return 'Upload stopped by extension.';
        default:
            return 'Unknown upload error.';
    }
}
?>