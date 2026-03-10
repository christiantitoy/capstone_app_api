<?php
// /connection/upload_apis/upload-seller-media.php

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // adjust for production
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../vendor/autoload.php';
require_once '/var/www/html/connection/db_connection.php'; // if needed later

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['seller_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$seller_id = (int)$_SESSION['seller_id'];

try {
    Configuration::instance(getenv('CLOUDINARY_URL'));
    if (!getenv('CLOUDINARY_URL')) {
        throw new Exception('Cloudinary not configured');
    }

    $type = $_POST['type'] ?? $_GET['type'] ?? null;
    if (!$type || !in_array($type, ['logo', 'banner', 'valid_id', 'store_photos'])) {
        throw new Exception('Invalid or missing type parameter');
    }

    $uploadApi = new UploadApi();

    $baseFolder = "capstone_app_images/sellers/{$seller_id}";
    $tags = ["seller_{$seller_id}", "capstone", "type_{$type}"];

    $results = [];

    // ── Single file types ────────────────────────────────
    if ($type === 'logo' || $type === 'banner') {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('No valid file uploaded');
        }

        $file = $_FILES['file'];
        $publicId = "{$type}_" . uniqid();

        $result = $uploadApi->upload($file['tmp_name'], [
            'folder'       => "{$baseFolder}/{$type}",
            'public_id'    => $publicId,
            'overwrite'    => true,
            'resource_type'=> 'image',
            'tags'         => $tags,
            'quality'      => 'auto',
            'fetch_format' => 'auto'
        ]);

        $results[] = [
            'type'       => $type,
            'url'        => $result['secure_url'],
            'public_id'  => $result['public_id'],
            'bytes'      => $result['bytes']
        ];
    }

    // ── Multiple file types ──────────────────────────────
    else if ($type === 'valid_id' || $type === 'store_photos') {
        if (!isset($_FILES['files']) || !is_array($_FILES['files']['tmp_name'])) {
            throw new Exception('No files uploaded');
        }

        $uploaded = 0;
        foreach ($_FILES['files']['tmp_name'] as $idx => $tmpName) {
            if ($_FILES['files']['error'][$idx] !== UPLOAD_ERR_OK) continue;

            $publicId = "{$type}_{$idx}_" . uniqid();

            $result = $uploadApi->upload($tmpName, [
                'folder'       => "{$baseFolder}/{$type}",
                'public_id'    => $publicId,
                'resource_type'=> 'image',
                'tags'         => $tags,
            ]);

            $results[] = [
                'url'       => $result['secure_url'],
                'public_id' => $result['public_id']
            ];
            $uploaded++;
        }

        if ($uploaded === 0) {
            throw new Exception('No valid files were uploaded');
        }
    }

    // Optional: save URLs to database right here
    // or return them and let frontend send another request to save

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'type'    => $type,
        'files'   => $results
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}