<?php
// upload.php - Enhanced Cloudinary upload endpoint with detailed feedback

// Enable CORS for testing
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Return JSON responses
header('Content-Type: application/json');

// Load Composer's autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Api\Exception\ApiError;

try {
    // Configure Cloudinary using environment variable from Render
    Configuration::instance(getenv('CLOUDINARY_URL'));
    
    // Check if CLOUDINARY_URL is set
    if (!getenv('CLOUDINARY_URL')) {
        throw new Exception('CLOUDINARY_URL environment variable is not set. Please add it in Render dashboard.');
    }
    
    // Log request details for debugging
    $debugInfo = [
        'timestamp' => date('Y-m-d H:i:s'),
        'method' => $_SERVER['REQUEST_METHOD'],
        'files' => $_FILES ? array_keys($_FILES) : [],
        'post' => $_POST ? array_keys($_POST) : []
    ];
    
    // Check if file was uploaded
    if (!isset($_FILES['image'])) {
        throw new Exception('No image file uploaded. Make sure to use form-data with key "image"');
    }
    
    $file = $_FILES['image'];
    
    // Enhanced file validation with detailed error messages
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds the MAX_FILE_SIZE directive in the HTML form',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        $errorMessage = $uploadErrors[$file['error']] ?? 'Unknown upload error (code: ' . $file['error'] . ')';
        throw new Exception('Upload error: ' . $errorMessage);
    }
    
    // Get file details
    $fileName = $file['name'];
    $fileSize = $file['size'];
    $fileTmpName = $file['tmp_name'];
    
    // Validate file exists
    if (!file_exists($fileTmpName)) {
        throw new Exception('Temporary file does not exist. Check server permissions.');
    }
    
    // Validate file size (10MB max)
    $maxSize = 10 * 1024 * 1024; // 10MB
    if ($fileSize > $maxSize) {
        $sizeInMB = round($fileSize / 1024 / 1024, 2);
        $maxSizeInMB = round($maxSize / 1024 / 1024, 2);
        throw new Exception("File too large. Size: {$sizeInMB}MB, Maximum allowed: {$maxSizeInMB}MB");
    }
    
    // Validate file type using both extension and MIME
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions)) {
        throw new Exception("Invalid file extension: .{$extension}. Allowed: " . implode(', ', $allowedExtensions));
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $fileTmpName);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception("Invalid MIME type: {$mimeType}. Allowed: " . implode(', ', $allowedTypes));
    }
    
    // Check if file is actually an image
    $imageInfo = getimagesize($fileTmpName);
    if ($imageInfo === false) {
        throw new Exception('File is not a valid image.');
    }
    
    // Initialize upload API
    $uploadApi = new UploadApi();
    
    // Prepare upload options
    $folder = 'capstone_app_images/seller_documents';  // Clean, simple path
    $publicId = uniqid('documents_', true); // Unique ID for each image

    // Upload to Cloudinary with additional options
    $uploadOptions = [
        'folder' => $folder,
        'public_id' => $publicId,
        'overwrite' => true,
        'resource_type' => 'image',
        'quality' => 'auto',
        'fetch_format' => 'auto',
        'tags' => ['seller_documents', 'capstone_app_images'] // Removed date from tags
    ];
    
    // Attempt upload with timing
    $startTime = microtime(true);
    $result = $uploadApi->upload($fileTmpName, $uploadOptions);
    $uploadTime = round((microtime(true) - $startTime) * 1000); // in milliseconds
    
    // Generate different transformation URLs
    $baseUrl = $result['secure_url'];
    $thumbnailUrl = preg_replace('/upload\//', 'upload/w_200,h_200,c_fill/', $baseUrl);
    $optimizedUrl = preg_replace('/upload\//', 'upload/q_auto,f_auto/', $baseUrl);
    
    // Return enhanced success response
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Image uploaded successfully to Cloudinary',
        'debug' => $debugInfo,
        'upload_time_ms' => $uploadTime,
        'data' => [
            'url' => $result['secure_url'],
            'thumbnail_url' => $thumbnailUrl,
            'optimized_url' => $optimizedUrl,
            'public_id' => $result['public_id'],
            'width' => $result['width'],
            'height' => $result['height'],
            'format' => $result['format'],
            'bytes' => $result['bytes'],
            'size_kb' => round($result['bytes'] / 1024, 2),
            'original_filename' => $fileName,
            'folder' => $folder,
            'created_at' => $result['created_at'],
            'tags' => $uploadOptions['tags'] ?? []
        ],
        'cloudinary_response' => $result // Full response for debugging
    ]);
    
} catch (ApiError $e) {
    // Cloudinary API specific error
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'error' => 'Cloudinary API Error: ' . $e->getMessage(),
        'error_code' => $e->getCode(),
        'debug' => $debugInfo ?? null
    ]);
} catch (Exception $e) {
    // General error
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'error_code' => $e->getCode(),
        'debug' => $debugInfo ?? null
    ]);
}
?>