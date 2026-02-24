<?php
// upload.php - Simple Cloudinary upload endpoint for Render

// Enable CORS for testing
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Return JSON responses
header('Content-Type: application/json');

// Load Composer's autoloader
require __DIR__ . '/vendor/autoload.php';

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

try {
    // Configure Cloudinary using environment variable from Render
    // This will use the CLOUDINARY_URL you set in Render's environment
    Configuration::instance(getenv('CLOUDINARY_URL'));
    
    // Check if CLOUDINARY_URL is set
    if (!getenv('CLOUDINARY_URL')) {
        throw new Exception('CLOUDINARY_URL environment variable is not set. Please add it in Render dashboard.');
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['image'])) {
        throw new Exception('No image file uploaded. Make sure to use form-data with key "image"');
    }
    
    $file = $_FILES['image'];
    
    // Validate upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        $errorMessage = $uploadErrors[$file['error']] ?? 'Unknown upload error';
        throw new Exception('Upload error: ' . $errorMessage);
    }
    
    // Validate file size (10MB max)
    $maxSize = 10 * 1024 * 1024; // 10MB
    if ($file['size'] > $maxSize) {
        throw new Exception('File too large. Maximum size is 10MB.');
    }
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.');
    }
    
    // Initialize upload API
    $uploadApi = new UploadApi();
    
    // Upload to Cloudinary
    $result = $uploadApi->upload($file['tmp_name'], [
        'folder' => 'uploads/' . date('Y-m-d'), // Organize by date
        'public_id' => uniqid(), // Generate unique ID
        'overwrite' => true,
        'resource_type' => 'image'
    ]);
    
    // Return success response
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Image uploaded successfully',
        'data' => [
            'url' => $result['secure_url'],
            'public_id' => $result['public_id'],
            'width' => $result['width'],
            'height' => $result['height'],
            'format' => $result['format'],
            'bytes' => $result['bytes']
        ]
    ]);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>