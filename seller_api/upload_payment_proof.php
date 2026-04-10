<?php
// upload_payment_proof.php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

require_once __DIR__ . '/../../vendor/autoload.php';

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Api\Exception\ApiError;

try {
    Configuration::instance(getenv('CLOUDINARY_URL'));
    
    if (!getenv('CLOUDINARY_URL')) {
        throw new Exception('CLOUDINARY_URL environment variable is not set.');
    }
    
    if (!isset($_FILES['image'])) {
        throw new Exception('No image file uploaded.');
    }
    
    $file = $_FILES['image'];
    
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
    $folder = 'capstone_app_images/payment_proofs';
    $publicId = 'payment_' . uniqid();
    
    $result = $uploadApi->upload($file['tmp_name'], [
        'folder' => $folder,
        'public_id' => $publicId,
        'overwrite' => true,
        'resource_type' => 'image',
        'quality' => 'auto',
        'fetch_format' => 'auto',
        'tags' => ['payment_proof', 'gcash']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment proof uploaded successfully',
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