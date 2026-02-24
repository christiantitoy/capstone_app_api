<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

error_reporting(0);
ini_set('display_errors', 0);

require_once '/var/www/html/connection/db_connection.php';

try {
    // Get POST data
    $buyer_id = $_POST['buyer_id'] ?? null;
    $business_id_type = $_POST['business_id_type'] ?? null;

    // Validate required fields
    if (!$buyer_id || !$business_id_type) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit;
    }

    // Check if seller exists
    $stmt = $conn->prepare("SELECT id FROM seller_profiles WHERE buyer_id = :buyer_id");
    $stmt->execute([':buyer_id' => $buyer_id]);
    $seller = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$seller) {
        echo json_encode(["status" => "error", "message" => "Seller profile not found"]);
        exit;
    }

    $seller_id = $seller['id'];
    $uploadedFiles = [];

    // Look for files with names: document_0, document_1, document_2, etc.
    $fileIndex = 0;
    while (isset($_FILES["document_$fileIndex"])) {
        $file = $_FILES["document_$fileIndex"];

        // Skip if file upload had an error
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $fileIndex++;
            continue;
        }

        // Validate it's an image
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $fileIndex++;
            continue; // Skip non-image files
        }

        // Prepare file for Cloudinary upload
        $curlFile = new CURLFile($file['tmp_name'], $file['type'], $file['name']);

        // Initialize cURL to upload to Cloudinary endpoint
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://10.216.143.249/capstone_app_api/uploading/upload_seller_documents.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['image' => $curlFile]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 second timeout

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            // Log error but continue with next file
            error_log("cURL Error for document_$fileIndex: " . $curlError);
            $fileIndex++;
            continue;
        }

        if ($httpCode === 201) {
            $cloudinaryResponse = json_decode($response, true);

            if ($cloudinaryResponse && $cloudinaryResponse['success']) {
                // Get the image URL from Cloudinary response
                $imageUrl = $cloudinaryResponse['data']['url'];

                // Save to database
                $stmt = $conn->prepare("INSERT INTO seller_documents (seller_id, business_id_type, document_url) VALUES (:seller_id, :business_id_type, :document_url)");
                $result = $stmt->execute([
                    ':seller_id' => $seller_id,
                    ':business_id_type' => $business_id_type,
                    ':document_url' => $imageUrl
                ]);

                if ($result) {
                    $uploadedFiles[] = [
                        "original_name" => $file['name'],
                        "cloudinary_url" => $imageUrl,
                        "thumbnail_url" => $cloudinaryResponse['data']['thumbnail_url'] ?? null,
                        "optimized_url" => $cloudinaryResponse['data']['optimized_url'] ?? null,
                        "document_id" => $conn->lastInsertId(),
                        "file_size_kb" => $cloudinaryResponse['data']['size_kb'] ?? null
                    ];
                }
            }
        } else {
            // Log failed upload but continue
            error_log("Cloudinary upload failed for document_$fileIndex. HTTP Code: $httpCode");
        }

        $fileIndex++;
    }

    // Send response
    if (empty($uploadedFiles)) {
        echo json_encode([
            "status" => "error",
            "message" => "No images were uploaded successfully. Please ensure you're uploading valid image files (JPG, PNG, GIF, WEBP)."
        ]);
    } else {
        echo json_encode([
            "status" => "success",
            "message" => count($uploadedFiles) . " document images uploaded successfully",
            "uploaded_files" => $uploadedFiles
        ]);
    }

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}

$conn = null; // Close PDO connection
?>