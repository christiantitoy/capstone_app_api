<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once '/var/www/html/connection/db_connection.php';

$response = ["status" => "error", "message" => "No file uploaded"];

try {
    // Check if file was uploaded
    if (!isset($_FILES['product_image'])) {
        echo json_encode($response);
        exit;
    }

    $file = $_FILES['product_image'];

    // Validate file upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        $errorMessage = $uploadErrors[$file['error']] ?? 'Unknown upload error';
        throw new Exception('Upload error: ' . $errorMessage);
    }

    // Basic validation (your Cloudinary API will do more thorough validation)
    $fileSize = $file['size'];
    $maxSize = 10 * 1024 * 1024; // 10MB (matches your Cloudinary API)

    if ($fileSize > $maxSize) {
        $sizeInMB = round($fileSize / 1024 / 1024, 2);
        $maxSizeInMB = round($maxSize / 1024 / 1024, 2);
        throw new Exception("File too large. Size: {$sizeInMB}MB, Maximum allowed: {$maxSizeInMB}MB");
    }

    // Upload to Cloudinary via your API
    $curlFile = new CURLFile($file['tmp_name'], $file['type'], $file['name']);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://capstone-app-api-r1ux.onrender.com/connection/upload_apis/upload_seller_products.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['image' => $curlFile]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For testing only, remove in production

    $cloudinaryResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        throw new Exception('Connection to Cloudinary API failed: ' . $curlError);
    }

    // Decode Cloudinary response
    $cloudinaryData = json_decode($cloudinaryResponse, true);

    if (!$cloudinaryData) {
        throw new Exception('Invalid response from Cloudinary API');
    }

    // Check if upload was successful based on your API response structure
    if ($httpCode !== 201 || !isset($cloudinaryData['success']) || $cloudinaryData['success'] !== true) {
        $errorMsg = $cloudinaryData['error'] ?? $cloudinaryData['message'] ?? 'Unknown Cloudinary error';
        throw new Exception('Cloudinary upload failed: ' . $errorMsg);
    }

    // Get the image URL from Cloudinary response
    $imageUrl = $cloudinaryData['data']['url'] ?? null;

    if (!$imageUrl) {
        throw new Exception('No image URL returned from Cloudinary');
    }

    // Success response with all Cloudinary data
    $response = [
        "status" => "success",
        "message" => "File uploaded successfully to Cloudinary",
        "image_url" => $imageUrl,
        "cloudinary_data" => [
            "thumbnail_url" => $cloudinaryData['data']['thumbnail_url'] ?? null,
            "optimized_url" => $cloudinaryData['data']['optimized_url'] ?? null,
            "public_id" => $cloudinaryData['data']['public_id'] ?? null,
            "width" => $cloudinaryData['data']['width'] ?? null,
            "height" => $cloudinaryData['data']['height'] ?? null,
            "format" => $cloudinaryData['data']['format'] ?? null,
            "size_kb" => $cloudinaryData['data']['size_kb'] ?? null,
            "original_filename" => $cloudinaryData['data']['original_filename'] ?? $file['name']
        ]
    ];

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
    $response["error_details"] = [
        "file_name" => $_FILES['product_image']['name'] ?? null,
        "file_size" => $_FILES['product_image']['size'] ?? null
    ];
}

echo json_encode($response);
?>