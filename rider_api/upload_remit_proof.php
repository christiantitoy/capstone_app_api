<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

require_once '/var/www/html/vendor/autoload.php';

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Api\Exception\ApiError;

try {
    Configuration::instance(getenv('CLOUDINARY_URL'));
    
    if (!getenv('CLOUDINARY_URL')) {
        throw new Exception('CLOUDINARY_URL environment variable is not set.');
    }
    
    if (!isset($_FILES['proof_image'])) {
        throw new Exception('No image file uploaded.');
    }
    
    $file = $_FILES['proof_image'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Upload error: ' . $file['error']);
    }
    
    // Validate file
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions)) {
        throw new Exception("Invalid file extension. Allowed: " . implode(', ', $allowedExtensions));
    }
    
    // Upload to Cloudinary
    $uploadApi = new UploadApi();
    $folder = 'capstone_app_images/remit_proofs';
    $publicId = 'remit_' . uniqid();
    
    $result = $uploadApi->upload($file['tmp_name'], [
        'folder' => $folder,
        'public_id' => $publicId,
        'overwrite' => true,
        'resource_type' => 'image',
        'quality' => 'auto',
        'fetch_format' => 'auto',
        'tags' => ['remit_proof', 'gcash', 'rider']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Remittance proof uploaded successfully',
        'url' => $result['secure_url'],
        'public_id' => $result['public_id']
    ]);
    
} catch (ApiError $e) {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'error' => 'Cloudinary API Error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>