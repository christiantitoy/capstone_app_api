<?php
// /admin/backend/payouts/upload_payout_proof.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

require_once '../session/auth_admin.php';
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
    
    // Validate file size (5MB max)
    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        throw new Exception('File size exceeds 5MB limit.');
    }
    
    // Upload to Cloudinary
    $uploadApi = new UploadApi();
    $folder = 'capstone_app_images/payout_proofs';
    $publicId = 'payout_' . date('Ymd_His') . '_' . uniqid();
    
    $result = $uploadApi->upload($file['tmp_name'], [
        'folder' => $folder,
        'public_id' => $publicId,
        'overwrite' => true,
        'resource_type' => 'image',
        'quality' => 'auto',
        'fetch_format' => 'auto',
        'tags' => ['payout_proof', 'gcash', 'seller']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Payout proof uploaded successfully',
        'url' => $result['secure_url'],
        'public_id' => $result['public_id']
    ]);
    
} catch (ApiError $e) {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'message' => 'Cloudinary API Error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>